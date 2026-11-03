<?php
if (!defined('ABSPATH')) exit;

/*
 * Fraztech V4.8
 * 1) Makes nested visual boxes/containers editable without changing the approved DOM.
 * 2) Repairs legacy custom project routes such as /work/bloom/ on staging/cloned sites.
 */

/* ------------------------- Project route repair ------------------------- */
function ft_v48_normalize_route($route){
 $path=(string)wp_parse_url((string)$route,PHP_URL_PATH);
 if($path==='')$path=(string)$route;
 $home_path=(string)wp_parse_url(home_url('/'),PHP_URL_PATH);
 if($home_path && $home_path!=='/' && str_starts_with($path,$home_path))$path=substr($path,strlen($home_path));
 $path='/'.trim($path,'/').'/';
 return $path==='//' ? '/' : $path;
}
function ft_v48_project_for_route($route){
 $route=ft_v48_normalize_route($route);
 if($route==='/'||$route==='/wp-admin/')return 0;
 static $cache=[]; if(isset($cache[$route]))return $cache[$route];
 $routes=get_option('ft_routes',[]);
 if(is_array($routes)&&!empty($routes[$route])){
  $id=(int)$routes[$route];
  if($id&&get_post_type($id)==='ft_project'&&get_post_status($id))return $cache[$route]=$id;
 }
 foreach(['_ft_route','_ft_key'] as $meta){
  $ids=get_posts(['post_type'=>'ft_project','post_status'=>'publish','numberposts'=>1,'fields'=>'ids','meta_key'=>$meta,'meta_value'=>$route,'suppress_filters'=>true]);
  if($ids){
   $id=(int)$ids[0];
   if(!is_array($routes))$routes=[];$routes[$route]=$id;update_option('ft_routes',$routes,false);
   if(!get_post_meta($id,'_ft_route',true))update_post_meta($id,'_ft_route',$route);
   return $cache[$route]=$id;
  }
 }
 return $cache[$route]=0;
}
// Fallback works even when the persisted rewrite_rules option on a cloned staging site is stale.
add_filter('request',function($vars){
 if(is_admin())return $vars;
 $uri=$_SERVER['REQUEST_URI']??''; if($uri==='')return $vars;
 $path=ft_v48_normalize_route($uri);
 $id=ft_v48_project_for_route($path); if(!$id)return $vars;
 foreach(['pagename','page','name','error','attachment','attachment_id'] as $k)unset($vars[$k]);
 $vars['post_type']='ft_project';$vars['p']=$id;
 return $vars;
},1);

function ft_v48_sync_route_option(){
 $routes=get_option('ft_routes',[]);if(!is_array($routes))$routes=[];
 $ids=get_posts(['post_type'=>'ft_project','post_status'=>'publish','numberposts'=>-1,'fields'=>'ids','suppress_filters'=>true]);
 $changed=false;
 foreach($ids as $id){
  $route=(string)get_post_meta($id,'_ft_route',true);
  if($route==='')$route=(string)get_post_meta($id,'_ft_key',true);
  if($route==='')continue;$route=ft_v48_normalize_route($route);
  if(($routes[$route]??0)!=(int)$id){$routes[$route]=(int)$id;$changed=true;}
  if((string)get_post_meta($id,'_ft_route',true)!==$route){update_post_meta($id,'_ft_route',$route);$changed=true;}
 }
 if($changed)update_option('ft_routes',$routes,false);
 return $changed;
}
add_action('admin_init',function(){
 if(get_option('ft_v48_route_rules_version')==='4.9.0')return;
 ft_v48_sync_route_option();
 if(function_exists('ft_register'))ft_register();
 flush_rewrite_rules(false);
 update_option('ft_v48_route_rules_version','4.9.0',false);
},25);

/* ---------------------- Editable nested box controls -------------------- */
function ft_v48_box_is_candidate(DOMElement $el){
 $tag=strtolower($el->tagName);$class=' '.strtolower((string)$el->getAttribute('class')).' ';
 if(in_array($tag,['div','article','figure','form','aside','nav','main','header','footer','details','summary','ul','ol','li'],true))return true;
 if($tag==='a' && (str_contains($class,' card ')||str_contains($class,'-card ')||str_contains($class,' button ')||$el->getElementsByTagName('div')->length||$el->getElementsByTagName('img')->length))return true;
 if($tag==='span' && preg_match('/\b(tag|badge|pill|chip|round-arrow|service-number|logo|icon|mark|star)\b/i',$class))return true;
 return false;
}
function ft_v48_clean_box_classes($classes){
 $out=[];foreach(preg_split('/\s+/',trim((string)$classes),-1,PREG_SPLIT_NO_EMPTY) as $c){
  if(str_starts_with($c,'elementor-repeater-item-')||str_starts_with($c,'ft-v4'))continue;$out[]=$c;
 }
 return $out;
}
function ft_v48_box_label(DOMElement $el,$index){
 $tag=strtolower($el->tagName);$classes=ft_v48_clean_box_classes($el->getAttribute('class'));
 $name=$classes?implode(' · ',array_slice($classes,0,3)):$tag;
 $text=trim(preg_replace('/\s+/u',' ',wp_strip_all_tags($el->textContent)));
 if($text!=='' && function_exists('mb_strlen') && mb_strlen($text)>40)$text=mb_substr($text,0,37).'…';
 elseif(strlen($text)>40)$text=substr($text,0,37).'…';
 return 'Box: '.$name.($text!==''?' — '.$text:'');
}
function ft_v48_walk_box_nodes(DOMNode $node,&$out){
 foreach($node->childNodes as $child){
  if(!($child instanceof DOMElement))continue;
  if(ft_v48_box_is_candidate($child))$out[]=$child;
  ft_v48_walk_box_nodes($child,$out);
 }
}
function ft_v48_boxes_from_template($template_b64){
 $html=base64_decode((string)$template_b64,true);if($html===false||trim($html)===''||!class_exists('DOMDocument'))return [];
 $dom=ft_dom($html);$root=$dom->getElementById('ft-root');if(!$root)return [];
 $nodes=[];ft_v48_walk_box_nodes($root,$nodes);$rows=[];$hash=substr(md5((string)$template_b64),0,10);
 foreach($nodes as $i=>$el){$index=$i+1;$rows[]=['_id'=>substr(md5('v48|'.$hash.'|'.$index),0,8),'box_index'=>$index,'label'=>ft_v48_box_label($el,$index)];}
 return $rows;
}
function ft_v48_mark_box_targets($content,$boxes){
 if(trim((string)$content)===''||!is_array($boxes)||!$boxes||!class_exists('DOMDocument'))return $content;
 $dom=ft_dom($content);$root=$dom->getElementById('ft-root');if(!$root)return $content;
 // A widget renders one original top-level element. Boxes are descendants of that element.
 $top=null;foreach($root->childNodes as $n){if($n instanceof DOMElement){$top=$n;break;}}
 if(!$top)return $content;$nodes=[];ft_v48_walk_box_nodes($top,$nodes);
 $by=[];foreach($boxes as $row){if(!is_array($row)||empty($row['_id'])||empty($row['box_index']))continue;$by[(int)$row['box_index']]=$row;}
 foreach($nodes as $i=>$el){$index=$i+1;if(empty($by[$index]))continue;$row=$by[$index];$id=preg_replace('/[^A-Za-z0-9_-]/','',(string)$row['_id']);if($id==='')continue;
  $classes=preg_split('/\s+/',trim((string)$el->getAttribute('class')),-1,PREG_SPLIT_NO_EMPTY);
  foreach(['elementor-repeater-item-'.$id,'ft-v48-box-target'] as $c)if(!in_array($c,$classes,true))$classes[]=$c;
  $el->setAttribute('class',implode(' ',$classes));$el->setAttribute('data-ft-v48-row',$id);$el->setAttribute('data-ft-v48-box',(string)$index);
 }
 return ft_dom_html($dom);
}
add_filter('elementor/widget/render_content',function($content,$widget){
 if(!is_object($widget)||!method_exists($widget,'get_name'))return $content;
 if(!in_array($widget->get_name(),['ft-v44-section','ft-v43-section'],true))return $content;
 $s=$widget->get_settings_for_display();return ft_v48_mark_box_targets($content,$s['boxes']??[]);
},20,2);

function ft_v48_register_box_controls($element,$args=[]){
 if(!class_exists('\\Elementor\\Repeater'))return;
 $rep=new \Elementor\Repeater();
 $rep->add_control('box_index',['type'=>\Elementor\Controls_Manager::HIDDEN]);
 $rep->add_control('label',['type'=>\Elementor\Controls_Manager::HIDDEN]);
 $rep->add_control('box_note',['type'=>\Elementor\Controls_Manager::RAW_HTML,'raw'=>'Styles this card/container itself. Use Outline controls for the thin outer stroke, Border controls for the thick frame, and Radius for the shape.','content_classes'=>'elementor-panel-alert elementor-panel-alert-info']);
 $rep->add_control('background_color',['label'=>'Background Color','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'background-color: {{VALUE}} !important;']]);
 $rep->add_control('display',['label'=>'Display','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'','options'=>[''=>'Default','block'=>'Block','flex'=>'Flex','grid'=>'Grid','inline-flex'=>'Inline Flex','inline-grid'=>'Inline Grid','none'=>'Hide'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'display: {{VALUE}} !important;']]);
 $rep->add_control('width',['label'=>'Width','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','vw'],'range'=>['px'=>['min'=>0,'max'=>2000,'step'=>1],'%'=>['min'=>0,'max'=>100,'step'=>1],'vw'=>['min'=>0,'max'=>100,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'width: {{SIZE}}{{UNIT}} !important;']]);
 $rep->add_control('max_width',['label'=>'Max Width','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','vw'],'range'=>['px'=>['min'=>0,'max'=>2400,'step'=>1],'%'=>['min'=>0,'max'=>100,'step'=>1],'vw'=>['min'=>0,'max'=>100,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'max-width: {{SIZE}}{{UNIT}} !important;']]);
 $rep->add_control('height',['label'=>'Height','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','vh'],'range'=>['px'=>['min'=>0,'max'=>1800,'step'=>1],'%'=>['min'=>0,'max'=>100,'step'=>1],'vh'=>['min'=>0,'max'=>100,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'height: {{SIZE}}{{UNIT}} !important;']]);
 $rep->add_control('min_height',['label'=>'Min Height','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','vh'],'range'=>['px'=>['min'=>0,'max'=>1800,'step'=>1],'vh'=>['min'=>0,'max'=>100,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'min-height: {{SIZE}}{{UNIT}} !important;']]);
 $rep->add_control('padding',['label'=>'Padding','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']]);
 $rep->add_control('margin',['label'=>'Margin / Spacing','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']]);
 $rep->add_control('gap',['label'=>'Inner Gap','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','em','rem','%'],'range'=>['px'=>['min'=>0,'max'=>250,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'gap: {{SIZE}}{{UNIT}} !important;']]);
 $rep->add_control('border_heading',['label'=>'Border / Shape','type'=>\Elementor\Controls_Manager::HEADING,'separator'=>'before']);
 $rep->add_control('border_style',['label'=>'Border Style','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'','options'=>[''=>'Default','none'=>'None','solid'=>'Solid','dashed'=>'Dashed','dotted'=>'Dotted','double'=>'Double'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'border-style: {{VALUE}} !important;']]);
 $rep->add_control('border_width',['label'=>'Border Width','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']]);
 $rep->add_control('border_color',['label'=>'Border Color','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'border-color: {{VALUE}} !important;']]);
 $rep->add_control('border_radius',['label'=>'Border Radius','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;']]);
 $rep->add_control('outline_heading',['label'=>'Outer Outline','type'=>\Elementor\Controls_Manager::HEADING,'separator'=>'before']);
 $rep->add_control('outline_style',['label'=>'Outline Style','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'','options'=>[''=>'Default','none'=>'None','solid'=>'Solid','dashed'=>'Dashed','dotted'=>'Dotted','double'=>'Double'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'outline-style: {{VALUE}} !important;']]);
 $rep->add_control('outline_width',['label'=>'Outline Width','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px'],'range'=>['px'=>['min'=>0,'max'=>30,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'outline-width: {{SIZE}}{{UNIT}} !important;']]);
 $rep->add_control('outline_color',['label'=>'Outline Color','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'outline-color: {{VALUE}} !important;']]);
 $rep->add_control('outline_offset',['label'=>'Outline Offset','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px'],'range'=>['px'=>['min'=>-20,'max'=>40,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'outline-offset: {{SIZE}}{{UNIT}} !important;']]);
 $rep->add_control('layout_heading',['label'=>'Layout','type'=>\Elementor\Controls_Manager::HEADING,'separator'=>'before']);
 $rep->add_control('flex_direction',['label'=>'Flex Direction','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'','options'=>[''=>'Default','row'=>'Row','column'=>'Column','row-reverse'=>'Row Reverse','column-reverse'=>'Column Reverse'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'flex-direction: {{VALUE}} !important;']]);
 $rep->add_control('justify_content',['label'=>'Justify Content','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'','options'=>[''=>'Default','flex-start'=>'Start','center'=>'Center','flex-end'=>'End','space-between'=>'Space Between','space-around'=>'Space Around','space-evenly'=>'Space Evenly'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'justify-content: {{VALUE}} !important;']]);
 $rep->add_control('align_items',['label'=>'Align Items','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'','options'=>[''=>'Default','stretch'=>'Stretch','flex-start'=>'Start','center'=>'Center','flex-end'=>'End','baseline'=>'Baseline'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'align-items: {{VALUE}} !important;']]);
 $rep->add_control('grid_columns',['label'=>'Grid Columns','type'=>\Elementor\Controls_Manager::NUMBER,'min'=>1,'max'=>12,'step'=>1,'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr)) !important;']]);
 $rep->add_control('overflow',['label'=>'Overflow','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'','options'=>[''=>'Default','visible'=>'Visible','hidden'=>'Hidden','clip'=>'Clip','auto'=>'Auto','scroll'=>'Scroll'],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'overflow: {{VALUE}} !important;']]);
 $rep->add_control('position_heading',['label'=>'Position','type'=>\Elementor\Controls_Manager::HEADING,'separator'=>'before']);
 $rep->add_control('move_x',['label'=>'Move X','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%'],'range'=>['px'=>['min'=>-600,'max'=>600,'step'=>1],'%'=>['min'=>-100,'max'=>100,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'--ft-v48-box-x: {{SIZE}}{{UNIT}};']]);
 $rep->add_control('move_y',['label'=>'Move Y','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%'],'range'=>['px'=>['min'=>-600,'max'=>600,'step'=>1],'%'=>['min'=>-100,'max'=>100,'step'=>1]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'--ft-v48-box-y: {{SIZE}}{{UNIT}};']]);
 $rep->add_control('scale',['label'=>'Scale','type'=>\Elementor\Controls_Manager::SLIDER,'range'=>['px'=>['min'=>0.2,'max'=>3,'step'=>0.01]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'--ft-v48-box-scale: {{SIZE}};']]);
 $rep->add_control('opacity',['label'=>'Opacity','type'=>\Elementor\Controls_Manager::SLIDER,'range'=>['px'=>['min'=>0,'max'=>1,'step'=>0.01]],'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'opacity: {{SIZE}} !important;']]);
 $rep->add_control('order',['label'=>'Flex/Grid Order','type'=>\Elementor\Controls_Manager::NUMBER,'min'=>-50,'max'=>50,'step'=>1,'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'order: {{VALUE}} !important;']]);
 $rep->add_control('z_index',['label'=>'Z-index','type'=>\Elementor\Controls_Manager::NUMBER,'min'=>-100,'max'=>9999,'step'=>1,'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'z-index: {{VALUE}} !important;']]);
 $element->add_control('boxes',['label'=>'Cards / Containers','type'=>\Elementor\Controls_Manager::REPEATER,'fields'=>$rep->get_controls(),'title_field'=>'{{{ label }}}','prevent_empty'=>false]);
}
add_action('elementor/element/ft-v44-section/ft_content/before_section_end','ft_v48_register_box_controls',10,2);
add_action('elementor/element/ft-v43-section/ft_content/before_section_end','ft_v48_register_box_controls',10,2);

function ft_v48_patch_widget_data(&$elements,&$changed){
 foreach($elements as &$el){
  if(!is_array($el))continue;
  if(in_array($el['widgetType']??'',['ft-v44-section','ft-v43-section'],true)){
   $s=&$el['settings'];if(!is_array($s))$s=[];
   if(!isset($s['boxes'])||!is_array($s['boxes'])||count($s['boxes'])===0){$s['boxes']=ft_v48_boxes_from_template($s['template_b64']??'');$changed=true;}
  }
  if(!empty($el['elements'])&&is_array($el['elements']))ft_v48_patch_widget_data($el['elements'],$changed);
 }
 unset($el);
}
function ft_v48_backfill_boxes(){
 if(get_option('ft_v48_boxes_version')==='4.9.0')return;
 $types=array_merge(['page','post'],array_keys(function_exists('ft_types')?ft_types():[]));
 $ids=get_posts(['post_type'=>$types,'post_status'=>'any','numberposts'=>-1,'fields'=>'ids','meta_key'=>'_elementor_data','suppress_filters'=>true]);
 foreach($ids as $id){$raw=(string)get_post_meta($id,'_elementor_data',true);if(trim($raw)==='')continue;$data=json_decode($raw,true);if(!is_array($data))continue;$changed=false;ft_v48_patch_widget_data($data,$changed);if(!$changed)continue;
  if(!metadata_exists('post',$id,'_ft_v48_pre_box_data'))update_post_meta($id,'_ft_v48_pre_box_data',$raw);
  update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));
 }
 update_option('ft_v48_boxes_version','4.9.0',false);
}
add_action('admin_init','ft_v48_backfill_boxes',30);

add_action('elementor/preview/enqueue_scripts',function(){
 wp_enqueue_script('ft-v49-click-box',plugins_url('assets/elementor-v49-click-box.js',__FILE__),[],'4.9.0',true);
});
