<?php
if(!defined('ABSPATH'))exit;
function ft_complaint_table(){global $wpdb;require_once ABSPATH.'wp-admin/includes/upgrade.php';$charset=$wpdb->get_charset_collate();dbDelta("CREATE TABLE {$wpdb->prefix}ft_complaints (
 id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 reference varchar(50) NOT NULL,
 created_at datetime NOT NULL,
 details longtext NOT NULL,
 PRIMARY KEY  (id),
 UNIQUE KEY reference (reference)
) $charset;");dbDelta("CREATE TABLE {$wpdb->prefix}ft_evidence (
 id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 complaint_id bigint(20) unsigned NOT NULL,
 filename varchar(255) NOT NULL,
 mime varchar(60) NOT NULL,
 content longtext NOT NULL,
 PRIMARY KEY  (id),
 KEY complaint_id (complaint_id)
) $charset;");}
add_shortcode('ft_complaint_fields',fn()=>'<input type="hidden" name="action" value="ft_submit_complaint"><input type="hidden" name="ft_nonce" value="'.esc_attr(wp_create_nonce('ft_complaint')).'"><label class="ft-honeypot" aria-hidden="true">Leave empty<input name="company_website" tabindex="-1" autocomplete="off"></label>');
add_action('wp_ajax_ft_complaint_nonce','ft_complaint_nonce');add_action('wp_ajax_nopriv_ft_complaint_nonce','ft_complaint_nonce');
function ft_complaint_nonce(){nocache_headers();wp_send_json_success(['nonce'=>wp_create_nonce('ft_complaint')]);}
add_action('wp_ajax_ft_submit_complaint','ft_submit_complaint');add_action('wp_ajax_nopriv_ft_submit_complaint','ft_submit_complaint');
function ft_complaint_error($message,$status=400){wp_send_json(['error'=>$message],$status);}
function ft_submit_complaint(){
 if(!wp_verify_nonce($_POST['ft_nonce']??'','ft_complaint'))ft_complaint_error('Please refresh the page and submit again.',403);
 if(!empty($_POST['company_website']))ft_complaint_error('Submission could not be accepted.');
 $key='ft_rate_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR']??'unknown',wp_salt());$rate=(int)get_transient($key);if($rate>=3)ft_complaint_error('Please wait before submitting another complaint, or contact Faraz on WhatsApp.',429);
 $fields=['name'=>120,'email'=>254,'project'=>160,'reason'=>100,'message'=>5000,'resolution'=>2000];$data=[];
 foreach($fields as $key2=>$max){$val=$_POST[$key2]??'';if(!is_string($val))ft_complaint_error('Please check your details.');$val=trim(wp_unslash($val));if(!$val||mb_strlen($val)>$max)ft_complaint_error('Please check the '.$key2.' field.');$data[$key2]=sanitize_textarea_field($val);}
 if(!is_email($data['email'])||mb_strlen($data['message'])<20||($_POST['consent']??'')!=='on')ft_complaint_error('Check your email, complaint details and privacy acknowledgement.');
 $files=$_FILES['media']??[];$names=$files['name']??[];if(!is_array($names))ft_complaint_error('Invalid upload.');$uploads=[];$total=0;
 foreach($names as $i=>$name){$err=$files['error'][$i]??UPLOAD_ERR_NO_FILE;if($err===UPLOAD_ERR_NO_FILE)continue;if($err!==UPLOAD_ERR_OK)ft_complaint_error('One of the uploads failed. Try a smaller file.');$tmp=$files['tmp_name'][$i];if(!is_uploaded_file($tmp))ft_complaint_error('Invalid upload.');$size=filesize($tmp);$total+=$size;if($total>10*1024*1024||count($uploads)>=3)ft_complaint_error('Use up to 3 files totaling no more than 10 MB.');$name=sanitize_file_name($name);$ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));$head=file_get_contents($tmp,false,null,0,32);$mime='';
 if(in_array($ext,['jpg','jpeg','png','webp'],true)){$info=@getimagesize($tmp);$expected=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'];if($info&&($info['mime']??'')===$expected[$ext])$mime=$expected[$ext];}
 elseif($ext==='mp4'&&substr($head,4,4)==='ftyp')$mime='video/mp4';
 if(!$mime)ft_complaint_error('Only valid JPG, PNG, WebP and MP4 files are accepted.');$uploads[]=['filename'=>$name,'mime'=>$mime,'content'=>base64_encode(file_get_contents($tmp))];}
 global $wpdb;$ref='FT-'.strtoupper(wp_generate_password(12,false,false));$ok=$wpdb->insert($wpdb->prefix.'ft_complaints',['reference'=>$ref,'created_at'=>current_time('mysql',true),'details'=>wp_json_encode($data)]);if(!$ok)ft_complaint_error('Unable to save your complaint. Please try again.',500);$id=$wpdb->insert_id;
 foreach($uploads as $u){$u['complaint_id']=$id;if(!$wpdb->insert($wpdb->prefix.'ft_evidence',$u)){$wpdb->delete($wpdb->prefix.'ft_evidence',['complaint_id'=>$id]);$wpdb->delete($wpdb->prefix.'ft_complaints',['id'=>$id]);ft_complaint_error('Unable to save the attachments. Please try again.',500);}}
 set_transient($key,$rate+1,HOUR_IN_SECONDS);wp_send_json(['reference'=>$ref],201);
}
function ft_inbox_page(){if(!current_user_can('manage_options'))return;global $wpdb;$page=max(1,absint($_GET['paged']??1));$rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ft_complaints ORDER BY id DESC LIMIT 20 OFFSET %d",($page-1)*20));echo '<div class="wrap"><h1>Private complaint inbox</h1><p>Visible to WordPress administrators only. Evidence is stored in the database, not at public media URLs. No automated email notifications are configured.</p>';if(!$rows)echo '<p>No complaints on this page.</p>';foreach($rows as $r){$d=json_decode($r->details,true);echo '<article class="ft-complaint"><h2>'.esc_html($r->reference.' · '.$d['reason']).'</h2><p>'.esc_html($r->created_at.' UTC · '.$d['name'].' · '.$d['email']).'</p><p><strong>Project:</strong> '.esc_html($d['project']).'</p><pre>'.esc_html($d['message']).'</pre><p><strong>Requested resolution</strong></p><pre>'.esc_html($d['resolution']).'</pre>';foreach($wpdb->get_results($wpdb->prepare("SELECT id,filename FROM {$wpdb->prefix}ft_evidence WHERE complaint_id=%d",$r->id)) as $f){$url=wp_nonce_url(admin_url('admin-post.php?action=ft_evidence&id='.$f->id),'ft_evidence_'.$f->id);echo '<a href="'.esc_url($url).'">Download '.esc_html($f->filename).'</a>';}echo '<form action="'.esc_url(admin_url('admin-post.php')).'" method="post" onsubmit="return confirm(\'Delete this complaint and all its evidence permanently?\')"><input type="hidden" name="action" value="ft_delete_complaint"><input type="hidden" name="id" value="'.(int)$r->id.'">';wp_nonce_field('ft_delete_'.$r->id);echo '<p><button class="button">Delete complaint and evidence</button></p></form></article>';}
 echo '<p>';if($page>1)echo '<a class="button" href="'.esc_url(add_query_arg('paged',$page-1)).'">Previous</a> ';if(count($rows)===20)echo '<a class="button" href="'.esc_url(add_query_arg('paged',$page+1)).'">Next</a>';echo '</p></div>';}
add_action('admin_post_ft_evidence',function(){if(!current_user_can('manage_options'))wp_die('Administrator access required.','Access denied',['response'=>403]);$id=absint($_GET['id']??0);check_admin_referer('ft_evidence_'.$id);global $wpdb;$f=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ft_evidence WHERE id=%d",$id));if(!$f)wp_die('File not found.','Not found',['response'=>404]);nocache_headers();header('Content-Type: application/octet-stream');header('X-Content-Type-Options: nosniff');header("Content-Security-Policy: sandbox");header('Content-Disposition: attachment; filename="'.sanitize_file_name($f->filename).'"');echo base64_decode($f->content);exit;});
add_action('admin_post_ft_delete_complaint',function(){if(!current_user_can('manage_options'))wp_die('Administrator access required.','Access denied',['response'=>403]);$id=absint($_POST['id']??0);check_admin_referer('ft_delete_'.$id);global $wpdb;$wpdb->delete($wpdb->prefix.'ft_evidence',['complaint_id'=>$id]);$wpdb->delete($wpdb->prefix.'ft_complaints',['id'=>$id]);wp_safe_redirect(admin_url('admin.php?page=ft-complaints'));exit;});
