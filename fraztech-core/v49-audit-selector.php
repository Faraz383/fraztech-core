<?php
if (!defined('ABSPATH')) exit;

/*
 * Fraztech V4.9
 * - Audits every routable document in the canonical seed (19 pages + 12 services + 21 projects + 24 articles = 76).
 * - Repairs ONLY missing/non-Elementor documents; valid edited Elementor documents are never rebuilt.
 * - Adds a robust editor container selector (JS is enqueued from v48-boxes-routes.php).
 */

function ft_v49_routable_seed(){
 $out=[];
 foreach((array)ft_seed() as $item){
  if(!is_array($item)||empty($item['route'])||empty($item['layout'])) continue;
  if(!in_array($item['type']??'',['page','post','ft_service','ft_project'],true)) continue;
  $out[]=$item;
 }
 return $out;
}
function ft_v49_counts(){
 $c=['page'=>0,'ft_service'=>0,'ft_project'=>0,'post'=>0];
 foreach(ft_v49_routable_seed() as $i){$t=$i['type'];$c[$t]=($c[$t]??0)+1;}
 return $c;
}
function ft_v49_item_id($item){
 $id=ft_find((string)($item['key']??''));
 if($id) return (int)$id;
 $type=(string)($item['type']??'');$route=(string)($item['route']??'');
 if($type==='ft_project'&&function_exists('ft_v48_project_for_route')){ $id=ft_v48_project_for_route($route); if($id)return (int)$id; }
 $slug=basename(trim($route,'/')?:'home');
 $posts=get_posts(['post_type'=>$type,'post_status'=>'any','numberposts'=>1,'fields'=>'ids','name'=>$slug,'suppress_filters'=>true]);
 return $posts?(int)$posts[0]:0;
}
function ft_v49_walk_elementor($els,&$widgets,&$fields,&$boxes){
 foreach((array)$els as $el){
  if(!is_array($el))continue;
  if(in_array($el['widgetType']??'',['ft-v44-section','ft-v43-section'],true)){
   $widgets++;
   $s=$el['settings']??[];
   $fields+=is_array($s['fields']??null)?count($s['fields']):0;
   $boxes+=is_array($s['boxes']??null)?count($s['boxes']):0;
  }
  if(!empty($el['elements'])&&is_array($el['elements']))ft_v49_walk_elementor($el['elements'],$widgets,$fields,$boxes);
 }
}
function ft_v49_document_status($item){
 $id=ft_v49_item_id($item);$route=(string)$item['route'];$status=['id'=>$id,'route'=>$route,'type'=>$item['type'],'title'=>$item['title'],'exists'=>false,'elementor'=>false,'widgets'=>0,'fields'=>0,'boxes'=>0,'issue'=>''];
 if(!$id||!get_post($id)){$status['issue']='Missing WordPress document';return $status;}
 $status['exists']=true;
 $raw=(string)get_post_meta($id,'_elementor_data',true);$data=$raw!==''?json_decode($raw,true):null;
 if(!is_array($data)||!$data){$status['issue']='Missing Elementor data';return $status;}
 $w=0;$f=0;$b=0;ft_v49_walk_elementor($data,$w,$f,$b);$status['widgets']=$w;$status['fields']=$f;$status['boxes']=$b;
 if($w<1){$status['issue']='No Fraztech Elementor section widgets';return $status;}
 if((string)get_post_meta($id,'_elementor_edit_mode',true)!=='builder'){$status['issue']='Elementor builder mode not enabled';return $status;}
 $status['elementor']=true;return $status;
}
function ft_v49_audit(){
 $rows=[];$found=0;$editable=0;$widgets=0;$fields=0;$boxes=0;
 foreach(ft_v49_routable_seed() as $item){$r=ft_v49_document_status($item);$rows[]=$r;if($r['exists'])$found++;if($r['elementor'])$editable++;$widgets+=$r['widgets'];$fields+=$r['fields'];$boxes+=$r['boxes'];}
 $counts=ft_v49_counts();$header=(int)get_option('ft_v45_global_header_id');$footer=(int)get_option('ft_v45_global_footer_id');
 return ['expected'=>count($rows),'found'=>$found,'editable'=>$editable,'widgets'=>$widgets,'fields'=>$fields,'boxes'=>$boxes,'counts'=>$counts,'header'=>($header&&get_post($header)?1:0),'footer'=>($footer&&get_post($footer)?1:0),'rows'=>$rows,'ready'=>count($rows)===76&&$found===76&&$editable===76];
}
function ft_v49_make_elementor($id){
 $origin='';$source=function_exists('ft_v44_source_layout')?ft_v44_source_layout($id,$origin):(string)get_post_meta($id,'_ft_layout',true);
 if(trim((string)$source)==='')return 'No recoverable source layout';
 $why='';if(function_exists('ft_v44_roundtrip')&&!ft_v44_roundtrip($source,$id,$why))return 'DOM fidelity failed: '.$why;
 $data=function_exists('ft_v44_build_data')?ft_v44_build_data($source,$id):[];if(!$data)return 'Generated Elementor data is empty';
 if(!metadata_exists('post',$id,'_ft_v49_pre_repair_data'))update_post_meta($id,'_ft_v49_pre_repair_data',(string)get_post_meta($id,'_elementor_data',true));
 update_post_meta($id,'_ft_layout',wp_slash($source));
 update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));
 update_post_meta($id,'_elementor_edit_mode','builder');
 update_post_meta($id,'_elementor_template_type',get_post_type($id)==='post'?'wp-post':'wp-page');
 if(defined('ELEMENTOR_VERSION'))update_post_meta($id,'_elementor_version',ELEMENTOR_VERSION);
 return '';
}
function ft_v49_repair_missing_only(){
 $fixed=0;$created=0;$failed=[];
 foreach(ft_v49_routable_seed() as $item){
  $r=ft_v49_document_status($item);if($r['elementor'])continue;
  $id=$r['id'];
  if(!$id){
   try{$id=function_exists('ft_import_entry')?ft_import_entry($item):0;}catch(Throwable $e){$failed[$item['route']]=$e->getMessage();continue;}
   if(!$id){$failed[$item['route']]='Could not create missing document';continue;}$created++;
  }
  $e=ft_v49_make_elementor((int)$id);if($e!==''){$failed[$item['route']]=$e;continue;}$fixed++;
 }
 if(function_exists('ft_v48_sync_route_option'))ft_v48_sync_route_option();
 if(function_exists('ft_register'))ft_register();flush_rewrite_rules(false);
 if(class_exists('\\Elementor\\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}}
 return ['fixed'=>$fixed,'created'=>$created,'failed'=>$failed,'audit'=>ft_v49_audit()];
}

// Once per upgrade, repair only genuinely missing editor documents. Existing user edits are preserved.
add_action('admin_init',function(){
 if(!current_user_can('manage_options'))return;
 if(get_option('ft_v49_audit_version')==='4.9.0')return;
 $before=ft_v49_audit();$result=['fixed'=>0,'created'=>0,'failed'=>[]];
 if(!$before['ready'])$result=ft_v49_repair_missing_only();
 update_option('ft_v49_audit_version','4.9.0',false);update_option('ft_v49_last_audit',['time'=>time(),'before'=>$before,'result'=>$result,'after'=>ft_v49_audit()],false);
},55);

add_action('admin_menu',function(){add_submenu_page('fraztech','Full Site Elementor Audit','Full Site Audit','manage_options','ft-v49-audit','ft_v49_admin');},34);
function ft_v49_admin(){
 if(!current_user_can('manage_options'))return;$notice='';
 if(isset($_POST['ft_v49_repair'])&&check_admin_referer('ft_v49_repair')){$r=ft_v49_repair_missing_only();$notice='<div class="notice '.(empty($r['failed'])?'notice-success':'notice-warning').'"><p><strong>Audit repair finished.</strong> Repaired '.intval($r['fixed']).', created '.intval($r['created']).', failures '.intval(count($r['failed'])).'. Existing editable documents were not rebuilt.</p></div>';}
 $a=ft_v49_audit();echo '<div class="wrap"><h1>Fraztech → Full Site Elementor Audit</h1>'.$notice;
 echo '<p>This audit checks every canonical routable Fraztech document. It does not count collection-only records such as the 100 AI-tool cards, FAQs or packages as separate pages.</p>';
 echo '<table class="widefat striped" style="max-width:900px;margin:18px 0"><tbody>';
 $rows=['Total routable documents'=>$a['expected'],'WordPress documents found'=>$a['found'],'Elementor-editable documents'=>$a['editable'],'Main pages'=>$a['counts']['page'],'Service detail pages'=>$a['counts']['ft_service'],'Project / work detail pages'=>$a['counts']['ft_project'],'Article detail pages'=>$a['counts']['post'],'Editable section widgets'=>$a['widgets'],'Editable content fields'=>$a['fields'],'Editable cards / containers'=>$a['boxes'],'Global header editor'=>$a['header'],'Global footer editor'=>$a['footer']];
 foreach($rows as $k=>$v)echo '<tr><td><strong>'.esc_html($k).'</strong></td><td>'.intval($v).'</td></tr>';
 echo '<tr><td><strong>Status</strong></td><td>'.($a['ready']&&$a['header']&&$a['footer']?'<span style="color:#087a35;font-weight:700">PASS — all 76 pages + global header/footer are editable</span>':'<span style="color:#b32d2e;font-weight:700">NEEDS ATTENTION</span>').'</td></tr></tbody></table>';
 $bad=array_values(array_filter($a['rows'],fn($r)=>!$r['elementor']));if($bad){echo '<details open style="max-width:1050px"><summary><strong>Missing / non-editable documents</strong></summary><table class="widefat striped"><thead><tr><th>Route</th><th>Type</th><th>Title</th><th>Issue</th></tr></thead><tbody>';foreach($bad as $r)echo '<tr><td><code>'.esc_html($r['route']).'</code></td><td>'.esc_html($r['type']).'</td><td>'.esc_html($r['title']).'</td><td>'.esc_html($r['issue']).'</td></tr>';echo '</tbody></table></details>';}
 echo '<form method="post" style="margin-top:18px">';wp_nonce_field('ft_v49_repair');echo '<button class="button button-primary" name="ft_v49_repair">Audit & Repair Missing Only</button></form>';
 echo '<p style="margin-top:18px"><strong>Canvas selection:</strong> click text/image/button to edit that item. Click a card/container background to edit the box. If the border/outline is outside the hit-area, hover near it until the purple Container frame appears and click. Option/Alt-click on an item selects its parent card/container.</p></div>';
}
