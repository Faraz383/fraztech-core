<?php
if (!defined('ABSPATH')) exit;

/*
 * V4.5.2 data repair.
 *
 * V4.5 briefly decoded already-unslashed Elementor JSON with wp_unslash().
 * That removed the backslash from JSON escapes before json_decode(), turning
 * punctuation such as "\\u2197" into literal "u2197", "\\u00b7" into
 * "u00b7", and an escaped newline into the letter "n". This file repairs
 * the affected Elementor control values without rebuilding page markup or
 * touching image/link choices the user has already changed.
 */

function ft_v452_repair_text($value){
 if(!is_string($value) || $value==='') return $value;
 // Handle both the legacy no-backslash spelling (u2197) and a literal
 // backslash spelling (\\u2197) if one survived a partial migration.
 $map=[
  'u00a0'=>"\u{00A0}", 'u00b7'=>'·', 'u00d7'=>'×',
  'u2018'=>'‘', 'u2019'=>'’', 'u201c'=>'“', 'u201d'=>'”',
  'u2013'=>'–', 'u2014'=>'—', 'u2026'=>'…',
  'u2161'=>'Ⅱ', 'u2190'=>'←', 'u2191'=>'↑', 'u2192'=>'→', 'u2197'=>'↗',
  'u2713'=>'✓', 'u2733'=>'✳', 'u00a9'=>'©',
 ];
 foreach($map as $bad=>$good){
  $value=str_replace(['\\'.$bad,$bad],$good,$value);
 }
 return $value;
}
function ft_v452_repair_recursive($value){
 if(is_array($value)){foreach($value as $k=>$v)$value[$k]=ft_v452_repair_recursive($v);return $value;}
 return is_string($value)?ft_v452_repair_text($value):$value;
}
function ft_v452_decode_elementor_data($raw){
 $raw=(string)$raw;
 if($raw==='') return [];
 $data=json_decode($raw,true);
 if(is_array($data)) return $data;
 // Read-only fallback for metadata written by an older build.
 $legacy=json_decode(wp_unslash($raw),true);
 return is_array($legacy)?$legacy:[];
}
function ft_v452_repair_section_fields(&$settings,&$changes){
 if(empty($settings['fields']) || !is_array($settings['fields'])) return;
 foreach($settings['fields'] as &$field){
  if(!is_array($field)) continue;
  foreach(['label','text','alt'] as $key){
   if(!isset($field[$key]) || !is_string($field[$key])) continue;
   $new=ft_v452_repair_text($field[$key]);
   if($new!==$field[$key]){$field[$key]=$new;$changes++;}
  }
 }
 unset($field);
}
function ft_v452_repair_global_settings(&$settings,&$changes){
 if(!is_array($settings)) return;
 $walk=function(&$value) use (&$walk,&$changes){
  if(is_array($value)){foreach($value as &$v)$walk($v);unset($v);return;}
  if(!is_string($value)) return;
  $new=ft_v452_repair_text($value);
  if($new!==$value){$value=$new;$changes++;}
 };
 $walk($settings);
 // V4.5's accidental unslash also changed the one intentional footer newline
 // into a literal "n". Repair only the exact known legacy value so normal
 // words containing "n" are never touched.
 if(($settings['description']??'')==='Thoughtful design.nComplete digital experiences.'){
  $settings['description']="Thoughtful design.\nComplete digital experiences.";$changes++;
 }
}
function ft_v452_repair_elementor_tree(&$elements,&$changes){
 if(!is_array($elements)) return;
 foreach($elements as &$el){
  if(!is_array($el)) continue;
  $type=(string)($el['widgetType']??'');
  if(in_array($type,['ft-v43-section','ft-v44-section'],true)){
   if(isset($el['settings']) && is_array($el['settings'])) ft_v452_repair_section_fields($el['settings'],$changes);
  }elseif(in_array($type,['ft-v45-global-header','ft-v45-global-footer'],true)){
   if(isset($el['settings']) && is_array($el['settings'])) ft_v452_repair_global_settings($el['settings'],$changes);
  }
  if(!empty($el['elements']) && is_array($el['elements'])) ft_v452_repair_elementor_tree($el['elements'],$changes);
 }
 unset($el);
}
function ft_v452_run_storage_repair($force=false){
 if(!$force && get_option('ft_v452_storage_repair_done')) return get_option('ft_v452_storage_repair_done');
 $ids=[];
 if(function_exists('ft_v44_ids')) $ids=ft_v44_ids();
 foreach(['ft_v45_global_header_id','ft_v45_global_footer_id'] as $opt){$id=(int)get_option($opt);if($id)$ids[]=$id;}
 $ids=array_values(array_unique(array_filter(array_map('intval',$ids))));
 $documents=0;$changes=0;
 foreach($ids as $id){
  $raw=get_post_meta($id,'_elementor_data',true); if(!$raw) continue;
  $data=ft_v452_decode_elementor_data($raw); if(!$data || !is_array($data)) continue;
  $doc_changes=0; ft_v452_repair_elementor_tree($data,$doc_changes);
  if($doc_changes){
   update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));
   $documents++;$changes+=$doc_changes;
  }
 }
 $result=['time'=>time(),'documents'=>$documents,'values'=>$changes];
 update_option('ft_v452_storage_repair_done',$result,false);
 if($documents && class_exists('\\Elementor\\Plugin')){
  try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}
 }
 if($documents){
  // LiteSpeed listens for this action when the plugin is active; harmless otherwise.
  do_action('litespeed_purge_all');
 }
 return $result;
}
add_action('admin_init',function(){ft_v452_run_storage_repair(false);},55);

// Extra front-end safety: if any old field escaped the one-time storage repair,
// sanitize the value at render time too. This does not modify saved content.
add_filter('ft_v452_runtime_text',function($value){return ft_v452_repair_text($value);});

add_action('admin_notices',function(){
 if(!current_user_can('manage_options'))return;
 $r=get_option('ft_v452_storage_repair_done');
 if(!is_array($r)||empty($r['values'])||get_transient('ft_v452_notice_seen'))return;
 set_transient('ft_v452_notice_seen',1,DAY_IN_SECONDS);
 echo '<div class="notice notice-success is-dismissible"><p><strong>Fraztech V4.5.2:</strong> repaired '.intval($r['values']).' legacy escaped text values across '.intval($r['documents']).' Elementor documents. Existing image/link choices were preserved.</p></div>';
});
