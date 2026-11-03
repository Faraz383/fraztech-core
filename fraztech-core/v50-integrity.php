<?php
if (!defined('ABSPATH')) exit;

/*
 * Fraztech V5.0 — canonical route + Elementor integrity repair.
 *
 * Fixes two failure modes visible after the V4.x migration chain:
 * 1) Custom project/service post types can be omitted from Elementor's enabled post types,
 *    so the public project opens correctly but WordPress only offers the classic "Edit Post" screen.
 * 2) Some frozen collection cards retained an old __FTV43_LINK_*__ placeholder because a link row
 *    was missing from that widget's repeater data. Clicking the card then navigates to a fake token URL.
 *
 * The repair is intentionally non-destructive: existing Elementor documents and user edits are kept.
 * Missing canonical route documents borrow Elementor data from an editable duplicate when available;
 * otherwise only that missing document is rebuilt from its canonical seed layout.
 */

function ft_v50_enabled_types(){return ['page','post','ft_service','ft_project'];}

function ft_v50_enable_elementor_types(){
 $support=get_option('elementor_cpt_support',[]);if(!is_array($support))$support=[];
 $changed=false;foreach(ft_v50_enabled_types() as $type){if(!in_array($type,$support,true)){$support[]=$type;$changed=true;}}
 if($changed)update_option('elementor_cpt_support',array_values(array_unique($support)),false);
 return $changed;
}
add_action('admin_init','ft_v50_enable_elementor_types',1);

function ft_v50_routable_seed(){
 $out=[];foreach((array)ft_seed() as $item){if(!is_array($item)||empty($item['route'])||empty($item['layout']))continue;if(!in_array($item['type']??'',ft_v50_enabled_types(),true))continue;if(function_exists('ft_v51_hww_seed_item') && (string)($item['route']??'')==='/videos/')continue;$out[]=$item;}
 if(function_exists('ft_v51_hww_seed_item'))$out[]=ft_v51_hww_seed_item();
 return $out;
}
function ft_v50_norm_route($route){return function_exists('ft_v48_normalize_route')?ft_v48_normalize_route($route):('/'.trim((string)$route,'/').'/');}
function ft_v50_statuses(){return ['publish','draft','private','pending'];}

function ft_v50_candidate_ids($item){
 $type=(string)($item['type']??'');$key=(string)($item['key']??'');$route=(string)($item['route']??'');$ids=[];
 if($key!==''){$q=get_posts(['post_type'=>$type,'post_status'=>ft_v50_statuses(),'numberposts'=>-1,'fields'=>'ids','meta_key'=>'_ft_key','meta_value'=>$key,'orderby'=>'modified','order'=>'DESC','suppress_filters'=>true]);foreach($q as $id)$ids[]=(int)$id;}
 if($type==='ft_project'&&$route!==''){$q=get_posts(['post_type'=>'ft_project','post_status'=>ft_v50_statuses(),'numberposts'=>-1,'fields'=>'ids','meta_key'=>'_ft_route','meta_value'=>ft_v50_norm_route($route),'orderby'=>'modified','order'=>'DESC','suppress_filters'=>true]);foreach($q as $id)$ids[]=(int)$id;}
 if($route!==''){$slug=basename(trim($route,'/')?:'home');$q=get_posts(['post_type'=>$type,'post_status'=>ft_v50_statuses(),'numberposts'=>-1,'fields'=>'ids','name'=>$slug,'orderby'=>'modified','order'=>'DESC','suppress_filters'=>true]);foreach($q as $id)$ids[]=(int)$id;}
 return array_values(array_unique(array_filter($ids)));
}
function ft_v50_route_id($item){
 $type=(string)($item['type']??'');$route=(string)($item['route']??'');
 if($type==='page'&&$route==='/'){$id=(int)get_option('page_on_front');if($id&&get_post($id)&&get_post_type($id)==='page')return $id;}
 if($type==='ft_project'){
  $routes=get_option('ft_routes',[]);$nr=ft_v50_norm_route($route);$id=(int)($routes[$route]??($routes[$nr]??0));if($id&&get_post($id)&&get_post_type($id)==='ft_project')return $id;
  $q=get_posts(['post_type'=>'ft_project','post_status'=>ft_v50_statuses(),'numberposts'=>1,'fields'=>'ids','meta_key'=>'_ft_route','meta_value'=>$nr,'suppress_filters'=>true]);if($q)return (int)$q[0];
 }
 $slug=basename(trim($route,'/')?:'home');$q=get_posts(['post_type'=>$type,'post_status'=>ft_v50_statuses(),'numberposts'=>1,'fields'=>'ids','name'=>$slug,'suppress_filters'=>true]);return $q?(int)$q[0]:0;
}
function ft_v50_elementor_data($id){$raw=(string)get_post_meta($id,'_elementor_data',true);if(trim($raw)==='')return null;$data=json_decode($raw,true);return is_array($data)&&$data?$data:null;}
function ft_v50_is_elementor($id){return $id&&get_post($id)&&(string)get_post_meta($id,'_elementor_edit_mode',true)==='builder'&&is_array(ft_v50_elementor_data($id));}

function ft_v50_copy_elementor($from,$to){
 if(!$from||!$to||$from===$to)return false;$raw=(string)get_post_meta($from,'_elementor_data',true);if(trim($raw)==='')return false;
 if(!metadata_exists('post',$to,'_ft_v50_pre_repair_data'))update_post_meta($to,'_ft_v50_pre_repair_data',(string)get_post_meta($to,'_elementor_data',true));
 update_post_meta($to,'_elementor_data',$raw);update_post_meta($to,'_elementor_edit_mode','builder');
 update_post_meta($to,'_elementor_template_type',get_post_type($to)==='page'?'wp-page':'wp-post');
 $v=(string)get_post_meta($from,'_elementor_version',true);if($v!=='')update_post_meta($to,'_elementor_version',$v);elseif(defined('ELEMENTOR_VERSION'))update_post_meta($to,'_elementor_version',ELEMENTOR_VERSION);
 $settings=get_post_meta($from,'_elementor_page_settings',true);if($settings!==''&&$settings!==null)update_post_meta($to,'_elementor_page_settings',$settings);
 return true;
}
function ft_v50_build_elementor($id,$item){
 if(!$id||!function_exists('ft_v44_build_data'))return 'Elementor builder function is unavailable';$src=(string)($item['layout']??'');if(trim($src)==='')return 'Canonical source layout is empty';
 $why='';if(function_exists('ft_v44_roundtrip')&&!ft_v44_roundtrip($src,$id,$why))return 'DOM fidelity failed: '.$why;$data=ft_v44_build_data($src,$id);if(!$data)return 'Generated Elementor data is empty';
 if(!metadata_exists('post',$id,'_ft_v50_pre_repair_data'))update_post_meta($id,'_ft_v50_pre_repair_data',(string)get_post_meta($id,'_elementor_data',true));
 update_post_meta($id,'_ft_layout',wp_slash($src));update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));update_post_meta($id,'_elementor_edit_mode','builder');update_post_meta($id,'_elementor_template_type',get_post_type($id)==='page'?'wp-page':'wp-post');if(defined('ELEMENTOR_VERSION'))update_post_meta($id,'_elementor_version',ELEMENTOR_VERSION);return '';
}
function ft_v50_create_or_reuse($item){
 $type=(string)$item['type'];$route=(string)$item['route'];$candidates=ft_v50_candidate_ids($item);$slug=basename(trim($route,'/')?:'home');
 if($candidates){$id=(int)$candidates[0];wp_update_post(['ID'=>$id,'post_name'=>$slug,'post_status'=>'publish']);return $id;}
 // Direct insert rather than ft_import_entry: the older importer stops at any duplicate _ft_key.
 $id=wp_insert_post(wp_slash(['post_type'=>$type,'post_status'=>'publish','post_title'=>(string)$item['title'],'post_name'=>$slug,'post_content'=>'','post_excerpt'=>(string)($item['excerpt']??''),'menu_order'=>(int)($item['order']??0)]),true);if(is_wp_error($id))return 0;
 update_post_meta($id,'_ft_key',(string)$item['key']);update_post_meta($id,'_ft_original_title',(string)$item['title']);foreach(['layout','card','card_title','excerpt','image','category','group','demo','url'] as $k)if(isset($item[$k]))update_post_meta($id,'_ft_'.$k,wp_slash($item[$k]));return (int)$id;
}
function ft_v50_sync_seed_meta($id,$item){
 if(!$id)return;update_post_meta($id,'_ft_key',(string)$item['key']);if(!metadata_exists('post',$id,'_ft_original_title'))update_post_meta($id,'_ft_original_title',(string)$item['title']);
 if(trim((string)get_post_meta($id,'_ft_layout',true))===''&&!empty($item['layout']))update_post_meta($id,'_ft_layout',wp_slash($item['layout']));
 foreach(['card','card_title','excerpt','image','category','group','demo','url'] as $k)if(isset($item[$k])&&!metadata_exists('post',$id,'_ft_'.$k))update_post_meta($id,'_ft_'.$k,wp_slash($item[$k]));
}

function ft_v50_card_route_map(){
 static $map=null;if($map!==null)return $map;$map=[];
 foreach(ft_v50_routable_seed() as $item){$route=(string)$item['route'];$labels=[];foreach(['card_title','title'] as $k)if(!empty($item[$k]))$labels[]=(string)$item[$k];
  if(!empty($item['card'])&&class_exists('DOMDocument')){$d=ft_dom((string)$item['card']);$xp=new DOMXPath($d);$h=$xp->query('//h3')->item(0);if($h)$labels[]=(string)$h->textContent;}
  foreach($labels as $label){$key=ft_v50_label_key($label);if($key!=='')$map[$key]=home_url($route);}
 }
 return $map;
}
function ft_v50_label_key($s){$s=wp_strip_all_tags((string)$s);$s=html_entity_decode($s,ENT_QUOTES|ENT_HTML5,'UTF-8');$s=preg_replace('/\s+/u',' ',trim($s));return function_exists('mb_strtolower')?mb_strtolower($s):strtolower($s);}
function ft_v50_anchor_context_labels(DOMElement $a){
 $labels=[];
 for($n=$a,$depth=0;$n instanceof DOMElement&&$depth<4;$n=$n->parentNode,$depth++){
  $h=$n->getElementsByTagName('h3');if($h->length){$t=trim((string)$h->item(0)->textContent);if($t!==''&&!in_array($t,$labels,true))$labels[]=$t;}
  $imgs=$n->getElementsByTagName('img');foreach($imgs as $img){$alt=trim((string)$img->getAttribute('alt'));if($alt!==''&&!in_array($alt,$labels,true))$labels[]=$alt;}
 }
 $own=trim((string)$a->textContent);if($own!==''&&!in_array($own,$labels,true))$labels[]=$own;return $labels;
}
function ft_v50_anchor_context_label(DOMElement $a){$labels=ft_v50_anchor_context_labels($a);return $labels?$labels[0]:'';}
function ft_v50_url_for_anchor(DOMElement $a){$map=ft_v50_card_route_map();foreach(ft_v50_anchor_context_labels($a) as $label){$k=ft_v50_label_key($label);if(isset($map[$k]))return $map[$k];}return '';}
function ft_v50_fix_orphan_links_html($html){
 if(!is_string($html)||!str_contains($html,'__FTV'))return $html;if(!class_exists('DOMDocument'))return $html;$dom=ft_dom($html);$root=$dom->getElementById('ft-root');if(!$root)return $html;$xp=new DOMXPath($dom);$changed=false;
 foreach($xp->query('//a[contains(@href,"__FTV")]') as $a){if(!$a instanceof DOMElement)continue;$url=ft_v50_url_for_anchor($a);if($url!==''){$a->setAttribute('href',$url);$changed=true;}}
 return $changed?ft_dom_html($dom):$html;
}
add_filter('elementor/widget/render_content',function($content,$widget){if(!is_object($widget)||!method_exists($widget,'get_name'))return $content;if(!in_array($widget->get_name(),['ft-v44-section','ft-v43-section'],true))return $content;return ft_v50_fix_orphan_links_html($content);},999,2);

function ft_v50_patch_widget_links(&$elements,&$changed){
 foreach($elements as &$el){if(!is_array($el))continue;if(in_array($el['widgetType']??'',['ft-v44-section','ft-v43-section'],true)){
   if(!isset($el['settings'])||!is_array($el['settings']))$el['settings']=[];$s=&$el['settings'];$fields=is_array($s['fields']??null)?$s['fields']:[];
   $rendered=function_exists('ft_v44_render_inner_from_settings')?ft_v44_render_inner_from_settings($s):'';
   if(str_contains($rendered,'__FTV')&&class_exists('DOMDocument')){$dom=ft_dom($rendered);$xp=new DOMXPath($dom);$bykey=[];foreach($fields as $idx=>$f)if(is_array($f)&&!empty($f['key']))$bykey[(string)$f['key']]=$idx;
    foreach($xp->query('//a[contains(@href,"__FTV")]') as $a){if(!$a instanceof DOMElement)continue;$href=(string)$a->getAttribute('href');if(!preg_match('/__FTV(?:42|43|44)_LINK_(.+?)__/', $href,$m))continue;$key=(string)$m[1];$url=ft_v50_url_for_anchor($a);if($url==='')continue;$label=ft_v50_anchor_context_label($a);
     if(isset($bykey[$key])){$idx=$bykey[$key];$old=$fields[$idx]['url']['url']??'';if($old===''||str_contains((string)$old,'__FTV')){$fields[$idx]['kind']='link';$fields[$idx]['url']=function_exists('ft_v44_url_setting')?ft_v44_url_setting($url):['url'=>$url];$changed=true;}}
     else{$fields[]=['_id'=>substr(md5('v50|'.$key),0,8),'key'=>$key,'kind'=>'link','label'=>'Link: '.($label?:'Card'),'url'=>function_exists('ft_v44_url_setting')?ft_v44_url_setting($url):['url'=>$url]];$bykey[$key]=count($fields)-1;$changed=true;}
    }
    if($changed)$s['fields']=$fields;
   }
  }
  if(!empty($el['elements'])&&is_array($el['elements']))ft_v50_patch_widget_links($el['elements'],$changed);
 }
 unset($el);
}
function ft_v50_backfill_orphan_link_fields(){
 $changed_docs=0;$ids=get_posts(['post_type'=>ft_v50_enabled_types(),'post_status'=>ft_v50_statuses(),'numberposts'=>-1,'fields'=>'ids','meta_key'=>'_elementor_data','suppress_filters'=>true]);
 foreach($ids as $id){$raw=(string)get_post_meta($id,'_elementor_data',true);$data=json_decode($raw,true);if(!is_array($data))continue;$changed=false;ft_v50_patch_widget_links($data,$changed);if(!$changed)continue;if(!metadata_exists('post',$id,'_ft_v50_pre_link_data'))update_post_meta($id,'_ft_v50_pre_link_data',$raw);update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));$changed_docs++;}
 return $changed_docs;
}

function ft_v50_repair_site(){
 ft_v50_enable_elementor_types();$result=['reused'=>0,'copied'=>0,'rebuilt'=>0,'created'=>0,'failed'=>[],'link_docs'=>0];$routes=get_option('ft_routes',[]);if(!is_array($routes))$routes=[];
 foreach(ft_v50_routable_seed() as $item){$id=ft_v50_route_id($item);$candidates=ft_v50_candidate_ids($item);if(!$id){$id=ft_v50_create_or_reuse($item);if(!$id){$result['failed'][$item['route']]='Could not create or resolve route document';continue;}$result['created']++;}
  ft_v50_sync_seed_meta($id,$item);if(get_post_status($id)!=='publish')wp_update_post(['ID'=>$id,'post_status'=>'publish']);
  if($item['type']==='ft_project'){$nr=ft_v50_norm_route($item['route']);update_post_meta($id,'_ft_route',$nr);$routes[$nr]=$id;$routes[(string)$item['route']]=$id;}
  if(!ft_v50_is_elementor($id)){$donor=0;foreach($candidates as $cid){if($cid!==$id&&ft_v50_is_elementor($cid)){$donor=$cid;break;}}
   if($donor&&ft_v50_copy_elementor($donor,$id))$result['copied']++;else{$err=ft_v50_build_elementor($id,$item);if($err!==''){$result['failed'][$item['route']]=$err;continue;}$result['rebuilt']++;}
  }else{$result['reused']++;}
 }
 update_option('ft_routes',$routes,false);$result['link_docs']=ft_v50_backfill_orphan_link_fields();if(function_exists('ft_register'))ft_register();flush_rewrite_rules(false);
 if(class_exists('\\Elementor\\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}}
 return $result;
}

function ft_v50_document_render_has_orphan_link($id){$data=ft_v50_elementor_data($id);if(!$data)return false;$html=function_exists('ft_v44_reconstruct_data')?ft_v44_reconstruct_data($data):'';$html=ft_v50_fix_orphan_links_html($html);return (bool)preg_match('/__FTV(?:42|43|44)_(?:LINK|URL)_[^_]+__/',(string)$html);}
function ft_v50_audit(){
 $rows=[];$found=0;$editable=0;$orphan=0;$routes=get_option('ft_routes',[]);$support=get_option('elementor_cpt_support',[]);$support_ok=is_array($support)&&!array_diff(ft_v50_enabled_types(),$support);
 foreach(ft_v50_routable_seed() as $item){$id=ft_v50_route_id($item);$exists=$id&&get_post($id);$is_el=$exists&&ft_v50_is_elementor($id);$has_orphan=$is_el&&ft_v50_document_render_has_orphan_link($id);if($exists)$found++;if($is_el)$editable++;if($has_orphan)$orphan++;$rows[]=['route'=>$item['route'],'title'=>$item['title'],'type'=>$item['type'],'id'=>$id,'exists'=>$exists,'elementor'=>$is_el,'orphan'=>$has_orphan];}
 return ['expected'=>count($rows),'found'=>$found,'editable'=>$editable,'orphan'=>$orphan,'support_ok'=>$support_ok,'rows'=>$rows,'ready'=>count($rows)===76&&$found===76&&$editable===76&&$orphan===0&&$support_ok];
}

add_action('admin_init',function(){
 if(!current_user_can('manage_options'))return;if(get_option('ft_v50_integrity_version')==='5.0.0')return;$before=ft_v50_audit();$repair=ft_v50_repair_site();$after=ft_v50_audit();update_option('ft_v50_integrity_version','5.0.0',false);update_option('ft_v50_last_integrity',['time'=>time(),'before'=>$before,'repair'=>$repair,'after'=>$after],false);
},80);

add_action('admin_menu',function(){add_submenu_page('fraztech','V5 Site Integrity','V5 Site Integrity','manage_options','ft-v50-integrity','ft_v50_admin');},35);
function ft_v50_admin(){
 if(!current_user_can('manage_options'))return;$notice='';if(isset($_POST['ft_v50_repair'])&&check_admin_referer('ft_v50_repair')){$r=ft_v50_repair_site();$notice='<div class="notice '.(empty($r['failed'])?'notice-success':'notice-warning').'"><p><strong>V5 repair complete.</strong> Existing editable: '.intval($r['reused']).', copied to canonical routes: '.intval($r['copied']).', rebuilt missing only: '.intval($r['rebuilt']).', created: '.intval($r['created']).', pages with orphan link fields repaired: '.intval($r['link_docs']).', failures: '.intval(count($r['failed'])).'.</p></div>';}
 $a=ft_v50_audit();echo '<div class="wrap"><h1>Fraztech → V5 Route & Elementor Integrity</h1>'.$notice.'<p>This checks the <strong>actual route-owning WordPress document</strong> for every canonical Fraztech URL, not an arbitrary duplicate with the same key.</p><table class="widefat striped" style="max-width:920px;margin:18px 0"><tbody>';
 $stats=['Canonical routable documents'=>$a['expected'],'Route documents found'=>$a['found'],'Route documents editable in Elementor'=>$a['editable'],'Pages with unresolved card links'=>$a['orphan'],'Elementor enabled for Page/Post/Service/Project'=>$a['support_ok']?1:0];foreach($stats as $k=>$v)echo '<tr><td><strong>'.esc_html($k).'</strong></td><td>'.intval($v).'</td></tr>';echo '<tr><td><strong>Status</strong></td><td>'.($a['ready']?'<span style="color:#087a35;font-weight:700">PASS — all 76 canonical routes are Elementor-editable and card links are resolved</span>':'<span style="color:#b32d2e;font-weight:700">NEEDS ATTENTION</span>').'</td></tr></tbody></table>';
 $bad=array_values(array_filter($a['rows'],fn($r)=>!$r['exists']||!$r['elementor']||$r['orphan']));if($bad){echo '<details open><summary><strong>Problems</strong></summary><table class="widefat striped" style="max-width:1100px"><thead><tr><th>Route</th><th>Type</th><th>Post ID</th><th>Issue</th></tr></thead><tbody>';foreach($bad as $r){$issues=[];if(!$r['exists'])$issues[]='Route document missing';elseif(!$r['elementor'])$issues[]='Route document is not Elementor-enabled';if($r['orphan'])$issues[]='Unresolved card link token';echo '<tr><td><code>'.esc_html($r['route']).'</code></td><td>'.esc_html($r['type']).'</td><td>'.intval($r['id']).'</td><td>'.esc_html(implode('; ',$issues)).'</td></tr>';}echo '</tbody></table></details>';}
 echo '<form method="post" style="margin-top:18px">';wp_nonce_field('ft_v50_repair');echo '<button class="button button-primary" name="ft_v50_repair">Repair Canonical Routes & Elementor Access</button></form><p style="margin-top:15px">The repair preserves existing Elementor documents. It only copies an editable duplicate onto the public route when necessary, or rebuilds a route only when no editable donor exists.</p></div>';
}
