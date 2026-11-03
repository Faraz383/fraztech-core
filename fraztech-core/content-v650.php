<?php
if (!defined('ABSPATH')) exit;
add_action('wp_enqueue_scripts', function(){if(is_front_page())wp_enqueue_style('ft-hero-spacing-v650',plugins_url('assets/hero-spacing-v650.css',__FILE__),[],'6.5.0');},99);
add_action('admin_menu',function(){add_submenu_page('fraztech','Content Update 6.5','Content Update 6.5','manage_options','ft-content-v650','ft_v650_screen');});
function ft_v650_manifest(){ $f=__DIR__.'/content-v650.json';return is_readable($f)?json_decode(file_get_contents($f),true):[]; }
function ft_v650_text_update($node,$fields,&$hits){
 if(!is_array($node))return $node;
 if(isset($node['settings']['fields'])&&is_array($node['settings']['fields']))foreach($node['settings']['fields'] as &$field){
  $key=$field['key']??'';
  if(isset($fields[$key])&&isset($field['text'])&&is_string($field['text'])&&$field['text']!==$fields[$key]){$field['text']=$fields[$key];$hits++;}
 }unset($field);
 foreach($node as $key=>$value){if($key==='settings')continue;if(is_array($value))$node[$key]=ft_v650_text_update($value,$fields,$hits);}
 return $node;
}
function ft_v650_prepare($id,$entry,$kind){
 $post=get_post($id);if(!$post||$post->post_title!==$entry['title'])return ['error'=>'Title mismatch or missing post'];
 $changes=[];
 if($kind==='pages'){
  if($post->post_type!=='page'||(int)$id===(int)get_option('page_on_front'))return ['error'=>'Not an eligible page'];
  $raw=get_post_meta($id,'_elementor_data',true);$tree=is_string($raw)?json_decode($raw,true):null;
  if(!is_array($tree))return ['error'=>'Elementor data not found'];
  $hits=0;$new=ft_v650_text_update($tree,$entry['fields'],$hits);
  if($hits)$changes['_elementor_data']=wp_json_encode($new,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
 }elseif($kind==='faqs'){
  if($post->post_type!=='ft_faq')return ['error'=>'Post type mismatch'];
  $q=$entry['question'];$a=$entry['answer'];
  if($post->post_title!==$q)$changes['post_title']=$q;
  if($post->post_content!==$a)$changes['post_content']=$a;
  if(get_post_meta($id,'_ft_excerpt',true)!==$a)$changes['_ft_excerpt']=$a;
  $card='<details><summary>'.esc_html($q).'<span>+</span></summary><p>'.esc_html($a).'</p></details>';
  if(get_post_meta($id,'_ft_card',true)!==$card)$changes['_ft_card']=$card;
  if(get_post_meta($id,'_ft_card_title',true)!==$q)$changes['_ft_card_title']=$q;
 }elseif($kind==='services'){
  if($post->post_type!=='ft_service')return ['error'=>'Post type mismatch'];
  $excerpt=$entry['description'];
  if($post->post_excerpt!==$excerpt)$changes['post_excerpt']=$excerpt;
  if(get_post_meta($id,'_ft_excerpt',true)!==$excerpt)$changes['_ft_excerpt']=$excerpt;
  $card=get_post_meta($id,'_ft_card',true);
  if(is_string($card)&&$card!==''){
   // Replace the first descriptive paragraph only; retain images, links, and icons.
   $new=preg_replace('~(<p\b[^>]*>).*?(</p>)~is','$1'.esc_html($excerpt).'$2',$card,1,$count);
   if($count&&$new!==$card)$changes['_ft_card']=$new;
  }
  // Service detail text is stored in Elementor fields; avoid overwriting layouts.
  $raw=get_post_meta($id,'_elementor_data',true);$tree=is_string($raw)?json_decode($raw,true):null;
  if(is_array($tree)){
   $fields=[];$seen=0;
   foreach($tree as $w){foreach(($w['settings']['fields']??[]) as $f){$txt=trim((string)($f['text']??''));if(strlen($txt)>65&&strlen($txt)<260&&$seen===0&&preg_match('/[a-z]/i',$txt)){$fields[$f['key']]=$excerpt;$seen++;break;}}if($seen)break;}
   if($fields){$hits=0;$new=ft_v650_text_update($tree,$fields,$hits);if($hits)$changes['_elementor_data']=wp_json_encode($new,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
  }
 }
 return ['changes'=>$changes];
}
function ft_v650_run($mode){
 if(!in_array($mode,['preview','apply','rollback'],true))return 'Invalid action';
 $manifest=ft_v650_manifest();$report=[];$total=0;$errors=0;
 foreach($manifest as $kind=>$entries)foreach($entries as $id=>$entry){
  $id=(int)$id;$backup='_ft_v650_backup';
  if($mode==='rollback'){
   $saved=get_post_meta($id,$backup,true);if(!is_array($saved))continue;
   $postdata=['ID'=>$id];foreach(['post_title','post_content','post_excerpt'] as $k)if(array_key_exists($k,$saved))$postdata[$k]=$saved[$k];
   if(count($postdata)>1)wp_update_post(wp_slash($postdata));
   foreach($saved['meta']??[] as $k=>$v){if($v['exists'])update_post_meta($id,$k,wp_slash($v['value']));else delete_post_meta($id,$k);}
   delete_post_meta($id,$backup);$report[]=get_the_title($id).' — restored';$total++;continue;
  }
  $p=ft_v650_prepare($id,$entry,$kind);if(isset($p['error'])){$errors++;$report[]=$entry['title'].' — skipped: '.$p['error'];continue;}
  $changes=$p['changes'];if(!$changes)continue;
  $report[]=$entry['title'].' — '.count($changes).' fields';$total++;
  if($mode==='preview')continue;
  if(metadata_exists('post',$id,$backup)){$report[]='  Existing backup retained; skipped reapply';continue;}
  $saved=['meta'=>[]];$post=get_post($id);
  foreach($changes as $key=>$value){if(str_starts_with($key,'post_'))$saved[$key]=$post->$key;else $saved['meta'][$key]=['exists'=>metadata_exists('post',$id,$key),'value'=>get_post_meta($id,$key,true)];}
  if(!add_post_meta($id,$backup,$saved,true)){$errors++;continue;}
  $postdata=['ID'=>$id];foreach($changes as $k=>$v){if(str_starts_with($k,'post_'))$postdata[$k]=$v;else update_post_meta($id,$k,wp_slash($v));}
  if(count($postdata)>1)wp_update_post(wp_slash($postdata));
 }
 return ucfirst($mode).': '.$total.' entries; '.$errors.' skipped. '.implode(' | ',$report);
}
function ft_v650_screen(){
 if(!current_user_can('manage_options'))return;
 echo '<div class="wrap"><h1>FrazTech Content Update 6.5</h1><p>Home hero is excluded. Backup your staging database before applying. Changes are mapped to known Elementor field keys, FAQ records and service entries. Existing styles, images and links are preserved.</p>';
 if(isset($_POST['ft_v650_action'])){check_admin_referer('ft_v650');$msg=ft_v650_run(sanitize_key(wp_unslash($_POST['ft_v650_action'])));echo '<div class="notice notice-info"><p>'.esc_html($msg).'</p></div>';}
 foreach(['preview'=>'Preview content changes','apply'=>'Apply content updates','rollback'=>'Restore previous content'] as $key=>$label){echo '<form method="post" style="display:inline-block;margin-right:10px">';wp_nonce_field('ft_v650');echo '<input type="hidden" name="ft_v650_action" value="'.esc_attr($key).'">';submit_button($label,'secondary','submit',false);echo '</form>';}
 echo '</div>';
}
