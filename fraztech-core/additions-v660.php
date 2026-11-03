<?php
if (!defined('ABSPATH')) exit;
// V6.6: additive case-study/about widgets, one replacement contact widget.
function ft66_contact_markup($settings=[]){
 $heading=$settings['heading']??'Tell us what you have in mind';$intro=$settings['intro']??'You do not need a complete plan. Tell us where you are starting and we can work out the next step.';
 ob_start(); ?>
 <div class="ft66-contact" id="ft66-contact"><h3><?php echo esc_html($heading); ?></h3><p><?php echo esc_html($intro); ?></p>
 <form class="ft66-form" novalidate><label>Your name *<input name="name" maxlength="100" required placeholder="How should we address you?"></label><label>Email address *<input name="email" type="email" maxlength="180" required placeholder="you@company.com"></label><label>Tell us about your project *<textarea name="brief" maxlength="4000" required rows="5" placeholder="Your idea, your challenge, or what you need help with"></textarea></label>
 <details><summary>Share a few more details <small>(optional)</small></summary><label>What do you need help with?<select name="service"><option value="">Not sure yet / Skip</option><option>UI/UX design</option><option>Website</option><option>Mobile app</option><option>Development</option><option>AI and automation</option><option>Something else</option></select></label><label>Where are you in the process?<select name="stage"><option value="">Not sure / Skip</option><option>Just an idea</option><option>Planning</option><option>Already building</option><option>Improving an existing product</option></select></label><label>Estimated budget<select name="budget"><option value="">Not sure / Skip</option><option>Under $1,000</option><option>$1,000–$5,000</option><option>Over $5,000</option></select></label><label>Timeline<select name="timeline"><option value="">Not sure / Skip</option><option>As soon as possible</option><option>Within 1–3 months</option><option>Flexible</option></select></label></details>
 <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="ft66-honey"><input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('ft66_inquiry')); ?>"><button class="button ft66-submit" type="submit">Send Project Inquiry</button><p class="ft66-status" role="status" aria-live="polite"></p></form>
 <div class="ft66-overlay" hidden><div class="ft66-modal" role="dialog" aria-modal="true" aria-labelledby="ft66-modal-title" tabindex="-1"><button type="button" class="ft66-x" aria-label="Close">×</button><span class="ft66-check">✓</span><h3 id="ft66-modal-title">Your Project Details Have Been Sent</h3><p>Thanks for reaching out! Your inquiry has been sent to</p><p><strong>info@fraztech.com</strong></p><p>Want to discuss your idea directly? You can also share your details on WhatsApp.</p><a class="button ft66-whatsapp" href="https://wa.me/923487096356" target="_blank" rel="noopener noreferrer">Continue on WhatsApp</a><button type="button" class="ft66-close">Stay on this page</button></div></div></div>
 <?php return ob_get_clean();
}
add_action('wp_ajax_nopriv_ft66_inquiry','ft66_inquiry');add_action('wp_ajax_ft66_inquiry','ft66_inquiry');
function ft66_inquiry(){
 if(!check_ajax_referer('ft66_inquiry','nonce',false))wp_send_json_error(['message'=>'This form has expired. Refresh the page and try again.'],403);
 if(!empty($_POST['website']))wp_send_json_error(['message'=>'Unable to submit.'],400);
 $name=sanitize_text_field(wp_unslash($_POST['name']??''));$email=sanitize_email(wp_unslash($_POST['email']??''));$brief=sanitize_textarea_field(wp_unslash($_POST['brief']??''));
 if(!$name||strlen($name)>100||!is_email($email)||!$brief||strlen($brief)>4000)wp_send_json_error(['message'=>'Please add your name, a valid email and a short project description.'],422);
 $ip=hash('sha256',sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']??'unknown')));$key='ft66_rate_'.substr($ip,0,28);if(get_transient($key)>=5)wp_send_json_error(['message'=>'Too many attempts. Please try again later.'],429);
 set_transient($key,(int)get_transient($key)+1,HOUR_IN_SECONDS);
 $parts=['New FrazTech project inquiry','Name: '.$name,'Email: '.$email,'Project brief: '.$brief];
 foreach(['service'=>'Help needed','stage'=>'Project stage','budget'=>'Budget','timeline'=>'Timeline'] as $k=>$label){$v=sanitize_text_field(wp_unslash($_POST[$k]??''));if($v)$parts[]=$label.': '.mb_substr($v,0,150);}
 $sent=wp_mail('info@fraztech.com','New project inquiry — FrazTech',implode("\n\n",$parts),['Content-Type: text/plain; charset=UTF-8','Reply-To: '.$name.' <'.$email.'>']);
 if(!$sent)wp_send_json_error(['message'=>'We could not send your email right now. Please try again or contact us on WhatsApp.'],500);
 $wa="Hi Faraz, I just submitted my project inquiry.\n".implode("\n",array_slice($parts,1));wp_send_json_success(['whatsapp'=>'https://wa.me/923487096356?text='.rawurlencode($wa)]);
}
add_action('wp_enqueue_scripts',function(){if(!is_page('contact')&&!is_page('contact-us'))return;wp_enqueue_style('ft66-additions',plugins_url('assets/additions-v660.css',__FILE__),[],'6.6.0');wp_enqueue_script('ft66-additions',plugins_url('assets/additions-v660.js',__FILE__),[],'6.6.0',true);wp_add_inline_script('ft66-additions','window.ft66Ajax='.wp_json_encode(admin_url('admin-ajax.php')).';','before');});
function ft66_people($s=[]){$s=wp_parse_args($s,['eyebrow'=>'WHO YOU WILL WORK WITH','heading'=>'A Clear Point of Contact From Start to Finish','intro'=>'You will know who is guiding the work, what comes next and where to share feedback.','lead'=>'Muhammad Faraz','lead_description'=>'I work with you to understand the problem, shape the direction and keep the project moving.','team_heading'=>'The Right People for the Work','team_description'=>'Depending on what your project needs, I coordinate design and development support while keeping communication clear.']);ob_start();?><section class="wrap section ft66-people"><div class="section-heading"><span class="eyebrow"><?php echo esc_html($s['eyebrow']);?></span><h2><?php echo esc_html($s['heading']);?></h2><p><?php echo esc_html($s['intro']);?></p></div><div class="ft66-people-grid"><article><h3><?php echo esc_html($s['lead']);?></h3><p><?php echo esc_html($s['lead_description']);?></p></article><article><h3><?php echo esc_html($s['team_heading']);?></h3><p><?php echo esc_html($s['team_description']);?></p></article></div></section><?php return ob_get_clean();}
function ft66_case($post_id,$s=[]){$s=wp_parse_args($s,['heading'=>'A Closer Look at This Project','challenge'=>'','approach'=>'','solution'=>'','outcome'=>'']);$third=str_contains(strtolower(get_post_meta($post_id,'_ft_layout',true)),'third-party design')||str_contains(strtolower(get_post_meta($post_id,'_ft_layout',true)),'dribbble / temporary');ob_start();?><section class="wrap section ft66-case"><div class="section-heading"><span class="eyebrow"><?php echo $third?'DESIGN REFERENCE':'PROJECT DETAILS';?></span><h2><?php echo esc_html($s['heading']);?></h2></div><?php if($third):?><p>This is a credited third-party design reference, not a FrazTech client case study. Explore the original work above.</p><?php else: ?><div class="ft66-case-grid"><?php foreach(['challenge'=>'The Challenge','approach'=>'Our Approach','solution'=>'The Solution','outcome'=>'The Outcome'] as $k=>$label){if(!trim($s[$k]))continue;?><article><h3><?php echo esc_html($label);?></h3><p><?php echo nl2br(esc_html($s[$k]));?></p></article><?php } ?></div><?php if(!array_filter(array_intersect_key($s,array_flip(['challenge','approach','solution','outcome'])))):?><p>Project details are being prepared. The original portfolio images and information above remain unchanged.</p><?php endif;endif;?></section><?php return ob_get_clean();}
add_action('elementor/widgets/register',function($manager){if(!class_exists('\\Elementor\\Widget_Base'))return;
 class FT66_Contact_Widget extends \Elementor\Widget_Base{public function get_name(){return 'ft66-contact';}public function get_title(){return 'FrazTech Project Inquiry';}public function get_icon(){return 'eicon-form-horizontal';}public function get_categories(){return ['basic'];}protected function register_controls(){$this->start_controls_section('content',['label'=>'Contact Form']);$this->add_control('heading',['label'=>'Heading','type'=>\Elementor\Controls_Manager::TEXT,'default'=>'Tell us what you have in mind']);$this->add_control('intro',['label'=>'Description','type'=>\Elementor\Controls_Manager::TEXTAREA,'default'=>'You do not need a complete plan. Tell us where you are starting and we can work out the next step.']);$this->end_controls_section();}protected function render(){$left=get_post_meta(get_the_ID(),'_ft66_contact_left',true);echo '<div class="wrap section compact contact-grid">'.($left?wp_kses_post($left):'').ft66_contact_markup($this->get_settings_for_display()).'</div>';}}
 class FT66_People_Widget extends \Elementor\Widget_Base{public function get_name(){return 'ft66-people';}public function get_title(){return 'FrazTech Who You Work With';}public function get_icon(){return 'eicon-person';}public function get_categories(){return ['basic'];}protected function register_controls(){$this->start_controls_section('content',['label'=>'People & Collaboration']);foreach(['eyebrow'=>'Eyebrow','heading'=>'Heading','intro'=>'Introduction','lead'=>'Lead name','lead_description'=>'Lead description','team_heading'=>'Team heading','team_description'=>'Team description'] as $key=>$label)$this->add_control($key,['label'=>$label,'type'=>in_array($key,['intro','lead_description','team_description'])?\Elementor\Controls_Manager::TEXTAREA:\Elementor\Controls_Manager::TEXT,'default'=>'']);$this->end_controls_section();}protected function render(){echo ft66_people(array_filter($this->get_settings_for_display(),fn($v)=>$v!==''));}}
 class FT66_Case_Widget extends \Elementor\Widget_Base{public function get_name(){return 'ft66-case';}public function get_title(){return 'FrazTech Project Case Study';}public function get_icon(){return 'eicon-document-file';}public function get_categories(){return ['basic'];}protected function register_controls(){$this->start_controls_section('content',['label'=>'Case Study']);foreach(['heading'=>'Heading','challenge'=>'The Challenge','approach'=>'Our Approach','solution'=>'The Solution','outcome'=>'The Outcome'] as $key=>$label)$this->add_control($key,['label'=>$label,'type'=>$key==='heading'?\Elementor\Controls_Manager::TEXT:\Elementor\Controls_Manager::TEXTAREA,'default'=>'']);$this->end_controls_section();}protected function render(){echo ft66_case(get_the_ID(),array_filter($this->get_settings_for_display(),fn($v)=>$v!==''));}}
 $manager->register(new FT66_Contact_Widget());$manager->register(new FT66_People_Widget());$manager->register(new FT66_Case_Widget());
});
// V6.6.1: preserve the original editable contact widget and replace only its rendered form.
// The original contact layout and all its existing Elementor controls remain untouched.
add_filter('elementor/widget/render_content', function($content,$widget){
 if(!is_page('contact')&&!is_page('contact-us'))return $content;
 if(!str_contains($content,'id="brief-form"') && !str_contains($content,"id='brief-form'"))return $content;
 $replacement=ft66_contact_markup();
 $result=preg_replace('~<form\b[^>]*\bid=["\\\']brief-form["\\\'][^>]*>.*?</form>~is',$replacement,$content,1,$count);
 return $count?$result:$content;
},30,2);

// For sites whose contact page uses the legacy shortcode instead of Elementor.
add_filter('the_content',function($content){
 if(!is_page('contact')&&!is_page('contact-us')||!str_contains($content,'brief-form'))return $content;
 $result=preg_replace('~<form\b[^>]*\bid=["\\\']brief-form["\\\'][^>]*>.*?</form>~is',ft66_contact_markup(),$content,1,$count);
 return $count?$result:$content;
},30);

function ft661_walk_types($elements,&$types){
 foreach($elements as $el){if(!empty($el['widgetType']))$types[]=$el['widgetType'];if(!empty($el['elements'])&&is_array($el['elements']))ft661_walk_types($el['elements'],$types);}
}
function ft661_repair(){
 $about=(int)ft_find('about');if(!$about){$p=get_page_by_path('about');$about=$p?$p->ID:0;}
 $projects=get_posts(['post_type'=>'ft_project','numberposts'=>-1,'post_status'=>['publish','draft','private'],'fields'=>'ids']);
 $ids=array_unique(array_filter(array_merge([$about],$projects)));$report=['updated'=>[],'already_present'=>[],'skipped'=>[],'about'=>$about,'projects'=>count($projects)];
 foreach($ids as $id){
  $raw=get_post_meta($id,'_elementor_data',true);
  if(!$raw){$report['skipped'][$id]='No Elementor document';continue;}
  $elements=is_array($raw)?$raw:json_decode($raw,true);
  if(!is_array($elements)){$report['skipped'][$id]='Invalid Elementor JSON';continue;}
  $widget=$id===$about?'ft66-people':'ft66-case';$types=[];ft661_walk_types($elements,$types);
  if(in_array($widget,$types,true)){$report['already_present'][]=$id;continue;}
  $elements[]=['id'=>substr(md5('ft661-'.$widget.'-'.$id),0,8),'elType'=>'widget','widgetType'=>$widget,'isInner'=>false,'settings'=>[],'elements'=>[]];
  // Keep an immutable first-copy backup; do not overwrite the previous v6.6.0 backup.
  if(!metadata_exists('post',$id,'_ft661_original_elementor_data'))add_post_meta($id,'_ft661_original_elementor_data',is_string($raw)?$raw:wp_json_encode($raw),true);
  $encoded=wp_json_encode($elements);if(!$encoded){$report['skipped'][$id]='JSON encode failed';continue;}
  update_post_meta($id,'_elementor_data',wp_slash($encoded));
  $verify=get_post_meta($id,'_elementor_data',true);$decoded=json_decode($verify,true);$verify_types=[];if(is_array($decoded))ft661_walk_types($decoded,$verify_types);
  if(!in_array($widget,$verify_types,true)){$report['skipped'][$id]='Write verification failed';continue;}
  $report['updated'][]=$id;
 }
 if(class_exists('\Elementor\Plugin')){try{\Elementor\Plugin::instance()->files_manager->clear_cache();}catch(\Throwable $e){$report['cache_warning']=$e->getMessage();}}
 update_option('ft661_last_report',$report,false);return $report;
}
add_action('admin_menu',function(){add_submenu_page('options-general.php','FrazTech v6.6.1 Repair','FrazTech Repair','manage_options','ft661-repair','ft661_repair_page');});
function ft661_repair_page(){
 if(!current_user_can('manage_options'))return;
 if(isset($_POST['ft661_apply'])){check_admin_referer('ft661_repair');$report=ft661_repair();echo '<div class="notice notice-success"><p>Repair finished. Review the results below and test your staging pages.</p></div>';}
 else $report=get_option('ft661_last_report',[]);
 echo '<div class="wrap"><h1>FrazTech Core v6.6.1 — Repair</h1><p>This adds missing Elementor-editable About and project sections without resetting existing widgets, images, or page text. The existing Contact form is updated at render time, preserving the original contact widget.</p>';
 echo '<form method="post">';wp_nonce_field('ft661_repair');submit_button('Apply / Recheck missing sections','primary','ft661_apply');echo '</form>';
 if($report){echo '<h2>Last repair report</h2><p>Updated: '.count($report['updated']??[]).' | Already present: '.count($report['already_present']??[]).' | Skipped: '.count($report['skipped']??[]).'</p>';if(!empty($report['skipped'])){echo '<ul>';foreach($report['skipped'] as $id=>$reason)echo '<li>'.esc_html($id.': '.$reason).'</li>';echo '</ul>';}}
 echo '<p><strong>Contact email:</strong> info@fraztech.com | <strong>WhatsApp:</strong> +923487096356</p></div>';
}
