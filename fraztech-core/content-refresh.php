<?php
if (!defined('ABSPATH')) exit;
add_action('admin_menu', function(){add_submenu_page('fraztech','Content Refresh','Content Refresh','manage_options','ft-content-refresh','ft_content_refresh_screen');});
function ft_content_refresh_pairs(){
 $file=plugin_dir_path(__FILE__).'content-refresh-map.json';
 if(!is_readable($file))return [];
 $pairs=json_decode(file_get_contents($file),true);
 return is_array($pairs)?$pairs:[];
}
function ft_content_refresh_screen(){
 if(!current_user_can('manage_options'))return;
 echo '<div class="wrap"><h1>FrazTech Content Refresh</h1><p>Back up the staging database first. Preview and Apply use the same matching logic. Changes are limited to text fields and can be restored using the backup button.</p>';
 if(isset($_POST['ft_refresh_action'])){
  check_admin_referer('ft_refresh_content');
  $action=sanitize_key(wp_unslash($_POST['ft_refresh_action']));
  $result=ft_content_refresh_apply($action);
  echo '<div class="notice notice-info"><p>'.esc_html($result).'</p></div>';
 }
 foreach(['preview'=>'Preview matches','apply'=>'Apply copy updates','rollback'=>'Restore previous copy'] as $key=>$label){
  echo '<form method="post" style="display:inline-block;margin-right:12px">';wp_nonce_field('ft_refresh_content');echo '<input type="hidden" name="ft_refresh_action" value="'.esc_attr($key).'">';submit_button($label,'secondary','submit',false);echo '</form>';
 }
 echo '</div>';
}
function ft_cr_replace_string($s,$pairs,&$hits){
 foreach($pairs as $old=>$new){
  if(!is_string($old)||$old===''||!is_string($new)||$old===$new)continue;
  $count=0;$s=str_replace($old,$new,$s,$count);$hits+=$count;
 }
 return $s;
}
function ft_cr_replace_tree($value,$pairs,&$hits){
 if(is_string($value))return ft_cr_replace_string($value,$pairs,$hits);
 if(is_array($value)){foreach($value as $k=>$v)$value[$k]=ft_cr_replace_tree($v,$pairs,$hits);}
 return $value;
}
function ft_content_refresh_apply($action){
 if(!in_array($action,['preview','apply','rollback'],true))return 'Invalid action';
 $pairs=ft_content_refresh_pairs();if(!$pairs)return 'No replacement map found';
 $types=array_values(array_diff(get_post_types(['show_ui'=>true],'names'),['attachment','elementor_library']));
 if(!in_array('elementor_library',$types,true))$types[]='elementor_library';
 $ids=get_posts(['post_type'=>$types,'post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','suppress_filters'=>true]);
 $matches=0;$affected=0;$updated=0;$errors=0;
 foreach($ids as $id){
  if(wp_is_post_revision($id))continue;
  $backupKey='_ft_copy_refresh_backup_641';
  if($action==='rollback'){
   $backup=get_post_meta($id,$backupKey,true);
   if(!is_array($backup))continue;
   if(isset($backup['post_content'])){$res=wp_update_post(['ID'=>$id,'post_content'=>wp_slash($backup['post_content'])],true);if(is_wp_error($res)){$errors++;continue;}}
   if(array_key_exists('elementor',$backup)){
    if($backup['elementor_exists'])update_post_meta($id,'_elementor_data',wp_slash($backup['elementor']));
    else delete_post_meta($id,'_elementor_data');
   }
   delete_post_meta($id,$backupKey);$updated++;continue;
  }
  $post=get_post($id);if(!$post)continue;
  $original=$post->post_content;
  $contentHits=0;$new=ft_cr_replace_string($original,$pairs,$contentHits);
  $raw=get_post_meta($id,'_elementor_data',true);
  $hasEl=metadata_exists('post',$id,'_elementor_data');
  $elHits=0;$newEl=$raw;
  if(is_string($raw)&&$raw!==''){
   $tree=json_decode($raw,true);
   if(json_last_error()===JSON_ERROR_NONE&&is_array($tree)){
    $tree=ft_cr_replace_tree($tree,$pairs,$elHits);
    if($elHits)$newEl=wp_json_encode($tree,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
   }else{
    // Invalid Elementor JSON must not be rewritten blindly.
    $errors++;
   }
  }
  $hits=$contentHits+$elHits;
  if(!$hits)continue;
  $matches+=$hits;$affected++;
  if($action==='preview')continue;
  if(get_post_meta($id,$backupKey,true))continue;
  $backup=['post_content'=>$original,'elementor'=>$raw,'elementor_exists'=>$hasEl];
  if(!add_post_meta($id,$backupKey,$backup,true)){$errors++;continue;}
  $ok=true;
  if($contentHits){$res=wp_update_post(['ID'=>$id,'post_content'=>wp_slash($new)],true);if(is_wp_error($res))$ok=false;}
  if($ok&&$elHits){$ok=update_post_meta($id,'_elementor_data',wp_slash($newEl))!==false;}
  if(!$ok){
   wp_update_post(['ID'=>$id,'post_content'=>wp_slash($original)]);
   if($hasEl)update_post_meta($id,'_elementor_data',wp_slash($raw));
   delete_post_meta($id,$backupKey);$errors++;continue;
  }
  $updated++;
 }
 if($action==='rollback')return "Restored {$updated} posts. Errors: {$errors}. Clear Elementor and page caches.";
 if($action==='preview')return "Found {$matches} replaceable text occurrences in {$affected} posts/pages. Nothing changed. JSON errors: {$errors}.";
 return "Updated {$updated} posts/pages out of {$affected} matching. Text occurrences: {$matches}. Errors: {$errors}. Clear Elementor and page caches.";
}

/** v6.4.2: Target the actual Fraztech V4.3 Elementor hero fields, not HTML strings. */
function ft_cr_hero_changes(){
 return [
  'tb3db7c52'=>'Your Business',
  't403c955b'=>'Has Challenges',
  't51d6cd53'=>"Let's Build Better Ways Forward",
  't665c73cc'=>"I'm Muhammad Faraz. I help businesses understand what's holding them back, improve customer experiences, and build practical digital and AI solutions that fit how they work.",
 ];
}
function ft_cr_hero_process($action){
 if(!in_array($action,['preview','apply','restore'],true))return 'Invalid action';
 $id=135;$post=get_post($id);
 if(!$post||$post->post_type!=='page')return 'Home page ID 135 not found. No changes made.';
 $key='_ft_hero_copy_backup_642';
 $raw=get_post_meta($id,'_elementor_data',true);
 if($action==='restore'){
  if(!metadata_exists('post',$id,$key))return 'No hero backup exists. Nothing changed.';
  $backup=get_post_meta($id,$key,true);
  if(!is_string($backup)||!json_decode($backup,true))return 'Backup invalid. Nothing changed.';
  $result=update_post_meta($id,'_elementor_data',wp_slash($backup));
  if($result===false)return 'Restore failed. Backup retained.';
  delete_post_meta($id,$key);
  delete_post_meta($id,'_elementor_element_cache');
  if(class_exists('\\Elementor\\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(\Throwable $e){}}
  return 'Original hero copy restored. Clear LiteSpeed cache and check the homepage.';
 }
 if(!is_string($raw)||$raw==='')return 'Elementor data missing. No changes made.';
 $tree=json_decode($raw,true);
 if(json_last_error()!==JSON_ERROR_NONE||!is_array($tree))return 'Elementor JSON invalid. No changes made.';
 $changes=ft_cr_hero_changes();$found=[];
 $walk=function(&$nodes)use(&$walk,$changes,&$found){
  foreach($nodes as &$node){
   if(!is_array($node))continue;
   if(($node['widgetType']??'')==='ft-v43-section' && ($node['settings']['outer_classes']??'')==='hero wrap' && isset($node['settings']['fields']) && is_array($node['settings']['fields'])){
    foreach($node['settings']['fields'] as &$field){
     $k=$field['key']??'';
     if(($field['kind']??'')==='text'&&isset($changes[$k])){
      $found[$k]=true;
      $field['text']=$changes[$k];
      // Elementor labels are editor hints only; preserve labels and visual structure.
     }
    }unset($field);
   }
   if(isset($node['elements'])&&is_array($node['elements']))$walk($node['elements']);
  }unset($node);
 };
 $walk($tree);
 if(count($found)!==count($changes))return 'Expected four hero text fields; found '.count($found).'. No changes made.';
 if($action==='preview')return 'Hero ready: 3 heading lines and 1 description field on Home page 135. Nothing changed.';
 if(metadata_exists('post',$id,$key))return 'Hero backup already exists. Restore previous hero before applying again.';
 $new=wp_json_encode($tree,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
 if(!is_string($new)||!is_array(json_decode($new,true)))return 'Encoding failed. Nothing changed.';
 if(!add_post_meta($id,$key,$raw,true))return 'Could not create backup. Nothing changed.';
 if(update_post_meta($id,'_elementor_data',wp_slash($new))===false){delete_post_meta($id,$key);return 'Update failed. No changes saved.';}
 delete_post_meta($id,'_elementor_element_cache');
 if(class_exists('\\Elementor\\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(\Throwable $e){}}
 return 'Hero updated: 3 heading lines and 1 description field. Original saved for rollback. Purge LiteSpeed cache.';
}
add_action('admin_menu',function(){add_submenu_page('fraztech','Hero Copy Fix','Hero Copy Fix','manage_options','ft-hero-copy-fix',function(){
 if(!current_user_can('manage_options'))return;
 echo '<div class="wrap"><h1>FrazTech Hero Copy Fix v6.4.2</h1><p>Updates only four text fields in the Home page custom Elementor hero. Preserves design, images, animations and other sections. Back up staging first.</p>';
 if(isset($_POST['ft_hero_action'])){check_admin_referer('ft_hero_copy_642');echo '<div class="notice notice-info"><p>'.esc_html(ft_cr_hero_process(sanitize_key(wp_unslash($_POST['ft_hero_action'])))).'</p></div>';}
 foreach(['preview'=>'Preview hero fields','apply'=>'Apply approved hero copy','restore'=>'Restore original hero'] as $k=>$label){echo '<form method="post" style="display:inline-block;margin-right:12px">';wp_nonce_field('ft_hero_copy_642');echo '<input type="hidden" name="ft_hero_action" value="'.esc_attr($k).'">';submit_button($label,'secondary','submit',false);echo '</form>';}
 echo '<p><strong>Approved heading:</strong> Your Business / Has Challenges / Let\'s Build Better Ways Forward</p></div>';
 });});
