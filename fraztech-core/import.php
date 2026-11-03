<?php
if(!defined('ABSPATH'))exit;
function ft_import_assets(){
 $files=[];$root=get_template_directory().'/static/assets';if(!is_dir($root))return [];
 foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS)) as $f){if(in_array(strtolower($f->getExtension()),['png','jpg','jpeg','webp','mp4'],true))$files[]=$f->getPathname();}sort($files);return $files;
}
function ft_import_asset($file){$relative='/assets/'.substr($file,strlen(get_template_directory().'/static/assets/'));$map=get_option('ft_assets',[]);if(isset($map[$relative])&&get_post($map[$relative]))return;require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';$tmp=wp_tempnam(basename($file));if(!$tmp||!copy($file,$tmp))throw new Exception('Could not copy media file. Check available disk space.');$id=media_handle_sideload(['name'=>basename($file),'tmp_name'=>$tmp],0,null,['post_name'=>'ft-media-'.substr(md5($relative),0,12),'post_title'=>ucwords(str_replace(['-','_'],' ',pathinfo($file,PATHINFO_FILENAME)))]);if(is_wp_error($id)){@unlink($tmp);throw new Exception($id->get_error_message());}$map[$relative]=$id;update_option('ft_assets',$map,false);}
function ft_import_entry($item){
 $existing=ft_find($item['key']);if($existing)return $existing;
 $slug=trim($item['route'],'/');$parent=0;
 if($item['type']==='page'&&str_contains($slug,'/')){$parent=ft_find('/'.dirname($slug).'/');$slug=basename($slug);}
 else $slug=basename($slug?:'home');
 $content='';$title=$item['title'];
 $id=wp_insert_post(wp_slash(['post_type'=>$item['type'],'post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_parent'=>$parent,'post_content'=>$content,'post_excerpt'=>$item['excerpt']??'','menu_order'=>$item['order']]),true);
 if(is_wp_error($id))throw new Exception($id->get_error_message());
 update_post_meta($id,'_ft_key',$item['key']);update_post_meta($id,'_ft_original_title',$title);
 foreach(['layout','card','card_title','excerpt','image','category','group','demo','url'] as $key)if(isset($item[$key]))update_post_meta($id,'_ft_'.$key,wp_slash($item[$key]));
 if($item['type']==='ft_project'){$routes=get_option('ft_routes',[]);$routes[$item['route']]=$id;update_option('ft_routes',$routes,false);update_post_meta($id,'_ft_route',$item['route']);}
 return $id;
}
function ft_finish_import(){
 $sample=get_page_by_path('hello-world',OBJECT,'post');if($sample&&$sample->post_title==='Hello world!'&&!get_post_meta($sample->ID,'_ft_key',true))update_option('ft_excluded_posts',[$sample->ID]);
 $home=ft_find('/');if($home){update_option('show_on_front','page');update_option('page_on_front',$home);update_option('page_for_posts',0);}update_option('blogname','Fraztech');update_option('permalink_structure','/insights/%postname%/');$privacy=ft_find('/privacy/');if($privacy)update_option('wp_page_for_privacy_policy',$privacy);ft_register();flush_rewrite_rules();update_option('ft_import_complete',gmdate('c'));
}
add_action('wp_ajax_ft_import',function(){if(!current_user_can('manage_options'))wp_send_json_error(['message'=>'Administrator access required.'],403);check_ajax_referer('ft_import','nonce');if(get_stylesheet()!=='fraztech')wp_send_json_error(['message'=>'Activate the Fraztech theme first.'],400);$step=absint($_POST['step']??0);$assets=ft_import_assets();$items=ft_seed();$total=count($assets)+count($items);try{if($step<count($assets))ft_import_asset($assets[$step]);elseif($step<$total)ft_import_entry($items[$step-count($assets)]);else {ft_finish_import();wp_send_json_success(['done'=>true,'message'=>'Content imported. Open your website, then edit content from Pages, Posts and Fraztech.']);}wp_send_json_success(['done'=>false,'next'=>$step+1,'message'=>'Importing '.($step+1).' of '.$total.'. Keep this tab open.']);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()],500);}});
