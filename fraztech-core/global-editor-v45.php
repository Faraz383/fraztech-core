<?php
if (!defined('ABSPATH')) exit;

/*
 * Fraztech V4.5 global editor + non-visual field ordering upgrade.
 * - Keeps page DOM unchanged.
 * - Reorders existing section fields so image rows appear first in Elementor.
 * - Creates two hidden draft pages used only as Elementor editing surfaces for
 *   the global Header and Footer. The theme reads their widget settings and
 *   outputs the exact original header/footer DOM, without Elementor wrappers.
 */

function ft_v45_url_value($v,$fallback=''){
 if(is_array($v)) return (string)($v['url']??$fallback);
 return is_string($v)&&$v!==''?$v:$fallback;
}
function ft_v45_url_setting($url){ return ['url'=>(string)$url,'is_external'=>false,'nofollow'=>false,'custom_attributes'=>'']; }
function ft_v45_media_setting($url=''){ return ['id'=>0,'url'=>(string)$url]; }
function ft_v45_theme_asset($path){ return get_template_directory_uri().'/static/assets/'.ltrim($path,'/'); }
function ft_v45_uid($s){ return substr(md5('ft45|'.$s),0,8); }

// V4.5.1: WordPress meta values returned by get_post_meta() are already
// unslashed. Calling wp_unslash() again strips the backslash from JSON
// unicode escapes (e.g. \\u2019 -> u2019), which caused literal u2019/u2197
// text to appear in the global header/footer. Decode the stored JSON first,
// and only use a legacy fallback when required.
function ft_v451_decode_elementor_json($raw){
 $raw=(string)$raw;
 if($raw==='')return [];
 $data=json_decode($raw,true);
 if(is_array($data))return $data;
 $legacy=json_decode(wp_unslash($raw),true);
 return is_array($legacy)?$legacy:[];
}
function ft_v451_repair_legacy_unicode($value){
 if(is_array($value)){foreach($value as $k=>$v)$value[$k]=ft_v451_repair_legacy_unicode($v);return $value;}
 if(!is_string($value)||$value==='')return $value;
 return strtr($value,[
  'u00b7'=>'·','u2019'=>'’','u2197'=>'↗','u2191'=>'↑','u00a9'=>'©',
  'u00d7'=>'×','u2013'=>'–','u2014'=>'—','u2026'=>'…','u201c'=>'“','u201d'=>'”'
 ]);
}

function ft_v45_default_header_settings(){
 return [
  'logo_image'=>ft_v45_media_setting(''),
  'logo_alt'=>'Fraztech',
  'logo_width'=>190,
  'brand_mark'=>'ft','brand_name'=>'fraztech','brand_tagline'=>'DESIGN · CODE · AI',
  'home_url'=>ft_v45_url_setting(home_url('/')),
  'nav_items'=>[
   ['_id'=>'navhome','label'=>'Home','url'=>ft_v45_url_setting(home_url('/')),'type'=>'normal','submenu'=>''],
   ['_id'=>'navabout','label'=>'About','url'=>ft_v45_url_setting(home_url('/about/')),'type'=>'normal','submenu'=>''],
   ['_id'=>'navservices','label'=>'Services','url'=>ft_v45_url_setting(home_url('/services/')),'type'=>'normal','submenu'=>''],
   ['_id'=>'navwork','label'=>'Work','url'=>ft_v45_url_setting(home_url('/work/')),'type'=>'dropdown','submenu'=>"All work|/work/\nMobile apps|/mobile-apps/\nWebsites|/websites/"],
   ['_id'=>'navsolutions','label'=>'Solutions','url'=>ft_v45_url_setting(home_url('/solutions/')),'type'=>'normal','submenu'=>''],
   ['_id'=>'navinsights','label'=>'Insights','url'=>ft_v45_url_setting(home_url('/insights/')),'type'=>'normal','submenu'=>''],
   ['_id'=>'navaitools','label'=>'AI Tools','url'=>ft_v45_url_setting(home_url('/ai-tools/')),'type'=>'normal','submenu'=>''],
   ['_id'=>'navstories','label'=>'How We Work','url'=>ft_v45_url_setting(home_url('/how-we-work/')),'type'=>'normal','submenu'=>''],
  ],
  'language_switcher_enabled'=>'yes',
  'cta_text'=>'Let’s talk ↗','cta_url'=>ft_v45_url_setting(home_url('/contact/')),
  'menu_label'=>'Open menu',
 ];
}
function ft_v45_link_rows($pairs){$out=[];$i=0;foreach($pairs as $label=>$url){$i++;$out[]=['_id'=>substr(md5($label.$i),0,8),'label'=>$label,'url'=>ft_v45_url_setting(home_url($url))];}return $out;}
function ft_v45_default_footer_settings(){
 $social=[
  ['Fiverr','https://www.fiverr.com/s/wbkdkmq','platforms/fiverr.svg','#e9f9f0','#007b45'],
  ['LinkedIn','https://www.linkedin.com/in/faraz-khan-4b8235256','platforms/linkedin.svg','#edf5ff','#0a66c2'],
  ['Upwork','https://www.upwork.com/freelancers/~0159ec3f835c4a7b67?mp_source=share','platforms/upwork.svg','#f0fbe9','#277e10'],
  ['Dribbble','https://dribbble.com/FarazDesign','platforms/dribbble.svg','#fff0f6','#b52d6d'],
  ['Behance','https://www.behance.net/farazdesign10','platforms/behance.svg','#edf3ff','#1760cf'],
  ['Facebook','https://www.facebook.com/profile.php?id=61575625778962','platforms/facebook.svg','#eef4ff','#0866ff'],
  ['Instagram','https://www.instagram.com/f_ara_zi?stkn=MWhnNDJ3ODlmemx0YQ==','platforms/instagram.svg','#fff0f7','#b72b78'],
  ['WhatsApp','https://wa.me/923487096356','platforms/whatsapp.svg','#eaf9ef','#128c4b'],
 ];
 $socials=[];foreach($social as $i=>$x)$socials[]=['_id'=>'soc'.$i,'name'=>$x[0],'url'=>ft_v45_url_setting($x[1]),'icon'=>ft_v45_media_setting(ft_v45_theme_asset($x[2])),'tint'=>$x[3],'color'=>$x[4]];
 return [
  'logo_image'=>ft_v45_media_setting(''),'logo_alt'=>'Fraztech','logo_width'=>190,
  'brand_mark'=>'ft','brand_name'=>'fraztech','brand_tagline'=>'DESIGN · CODE · AI',
  'home_url'=>ft_v45_url_setting(home_url('/')),
  'description'=>"Thoughtful design.\nComplete digital experiences.",
  'email'=>'info@fraztech.com',
  'explore_title'=>'Explore','explore_links'=>ft_v45_link_rows(['About'=>'/about/','Services'=>'/services/','AI development'=>'/services/ai-development/','Shopify'=>'/services/shopify/','Articles'=>'/insights/','Work'=>'/work/','Mobile apps'=>'/mobile-apps/','Websites'=>'/websites/']),
  'work_title'=>'Work together','work_links'=>ft_v45_link_rows(['Solutions'=>'/solutions/','Packages'=>'/packages/','Client reviews'=>'/reviews/','Contact'=>'/contact/','Payment methods'=>'/payments/','How we work'=>'/how-we-work/']),
  'social_title'=>'Connect with me','socials'=>$socials,
  'legal_links'=>ft_v45_link_rows(['Contact us'=>'/contact/','Raise a concern'=>'/complaints/','Privacy policy'=>'/privacy/','Cookie preferences'=>'/cookies/','Terms & conditions'=>'/terms/','Refund policy'=>'/refunds/']),
  'copyright'=>'© 2026 Fraztech. By Muhammad Faraz.','build_line'=>'Designed with intention. Built with care.','back_text'=>'Back to top ↑',
  'whatsapp_url'=>ft_v45_url_setting('https://wa.me/923487096356'),'whatsapp_icon'=>ft_v45_media_setting(ft_v45_theme_asset('whatsapp.svg')),
 ];
}

function ft_v45_register_global_widgets($widgets){
 if(!class_exists('\Elementor\Widget_Base')) return;
 if(!class_exists('FT_V45_Global_Header')){
  class FT_V45_Global_Header extends \Elementor\Widget_Base{
   public function get_name(){return 'ft-v45-global-header';}
   public function get_title(){return 'Fraztech Global Header';}
   public function get_icon(){return 'eicon-header';}
   public function get_categories(){return ['basic'];}
   public function has_widget_inner_wrapper(): bool {return false;}
   protected function register_controls(){
    $d=ft_v45_default_header_settings();
    $this->start_controls_section('brand',['label'=>'Logo & Brand']);
    $this->add_control('logo_image',['label'=>'Logo Image','type'=>\Elementor\Controls_Manager::MEDIA,'media_types'=>['image'],'default'=>$d['logo_image'],'description'=>'Upload a complete logo here. Leave empty to use the editable FT + Fraztech text logo below.']);
    $this->add_control('logo_alt',['label'=>'Logo Alt Text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['logo_alt']]);
    $this->add_control('logo_width',['label'=>'Logo Width (px)','type'=>\Elementor\Controls_Manager::NUMBER,'min'=>60,'max'=>400,'step'=>1,'default'=>$d['logo_width']]);
    $this->add_control('brand_mark',['label'=>'Logo Mark Text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['brand_mark']]);
    $this->add_control('brand_name',['label'=>'Brand Name','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['brand_name']]);
    $this->add_control('brand_tagline',['label'=>'Small Tagline','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['brand_tagline']]);
    $this->add_control('home_url',['label'=>'Logo Link','type'=>\Elementor\Controls_Manager::URL,'default'=>$d['home_url']]);
    $this->end_controls_section();
    $this->start_controls_section('navigation',['label'=>'Navigation']);
    $r=new \Elementor\Repeater();
    $r->add_control('label',['label'=>'Label','type'=>\Elementor\Controls_Manager::TEXT]);
    $r->add_control('url',['label'=>'URL','type'=>\Elementor\Controls_Manager::URL]);
    $r->add_control('type',['label'=>'Type','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'normal','options'=>['normal'=>'Normal link','dropdown'=>'Dropdown']]);
    $r->add_control('submenu',['label'=>'Dropdown items','type'=>\Elementor\Controls_Manager::TEXTAREA,'rows'=>5,'description'=>'One per line: Label|/url/','condition'=>['type'=>'dropdown']]);
    $this->add_control('nav_items',['label'=>'Menu Items','type'=>\Elementor\Controls_Manager::REPEATER,'fields'=>$r->get_controls(),'default'=>$d['nav_items'],'title_field'=>'{{{ label }}}']);
    $this->end_controls_section();
    $this->start_controls_section('language',['label'=>'Language Switcher']);
    $this->add_control('language_switcher_enabled',['label'=>'Show language dropdown','type'=>\Elementor\Controls_Manager::SWITCHER,'label_on'=>'Show','label_off'=>'Hide','return_value'=>'yes','default'=>$d['language_switcher_enabled'],'description'=>'Languages and translation API are managed under Fraztech → Languages.']);
    $this->end_controls_section();
    $this->start_controls_section('cta',['label'=>'Header CTA']);
    $this->add_control('cta_text',['label'=>'Button Text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['cta_text']]);
    $this->add_control('cta_url',['label'=>'Button Link','type'=>\Elementor\Controls_Manager::URL,'default'=>$d['cta_url']]);
    $this->add_control('menu_label',['label'=>'Mobile Menu Accessible Label','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['menu_label']]);
    $this->end_controls_section();
   }
   protected function render(){ echo ft_v45_header_markup($this->get_settings_for_display()); }
  }
 }
 if(!class_exists('FT_V45_Global_Footer')){
  class FT_V45_Global_Footer extends \Elementor\Widget_Base{
   public function get_name(){return 'ft-v45-global-footer';}
   public function get_title(){return 'Fraztech Global Footer';}
   public function get_icon(){return 'eicon-footer';}
   public function get_categories(){return ['basic'];}
   public function has_widget_inner_wrapper(): bool {return false;}
   protected function register_controls(){
    $d=ft_v45_default_footer_settings();
    $this->start_controls_section('brand',['label'=>'Logo & Contact']);
    $this->add_control('logo_image',['label'=>'Logo Image','type'=>\Elementor\Controls_Manager::MEDIA,'media_types'=>['image'],'default'=>$d['logo_image']]);
    $this->add_control('logo_alt',['label'=>'Logo Alt Text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['logo_alt']]);
    $this->add_control('logo_width',['label'=>'Logo Width (px)','type'=>\Elementor\Controls_Manager::NUMBER,'min'=>60,'max'=>400,'default'=>$d['logo_width']]);
    $this->add_control('brand_mark',['label'=>'Logo Mark Text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['brand_mark']]);
    $this->add_control('brand_name',['label'=>'Brand Name','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['brand_name']]);
    $this->add_control('brand_tagline',['label'=>'Small Tagline','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['brand_tagline']]);
    $this->add_control('home_url',['label'=>'Logo Link','type'=>\Elementor\Controls_Manager::URL,'default'=>$d['home_url']]);
    $this->add_control('description',['label'=>'Description','type'=>\Elementor\Controls_Manager::TEXTAREA,'rows'=>3,'default'=>$d['description']]);
    $this->add_control('email',['label'=>'Email','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['email']]);
    $this->end_controls_section();
    foreach([['explore','Explore links','explore_title','explore_links'],['work','Work together links','work_title','work_links']] as $spec){
     [$sid,$label,$tk,$lk]=$spec;$this->start_controls_section($sid,['label'=>$label]);$this->add_control($tk,['label'=>'Column Title','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d[$tk]]);
     $r=new \Elementor\Repeater();$r->add_control('label',['label'=>'Label','type'=>\Elementor\Controls_Manager::TEXT]);$r->add_control('url',['label'=>'URL','type'=>\Elementor\Controls_Manager::URL]);
     $this->add_control($lk,['label'=>'Links','type'=>\Elementor\Controls_Manager::REPEATER,'fields'=>$r->get_controls(),'default'=>$d[$lk],'title_field'=>'{{{ label }}}']);$this->end_controls_section();
    }
    $this->start_controls_section('social',['label'=>'Social Profiles']);$this->add_control('social_title',['label'=>'Column Title','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['social_title']]);
    $r=new \Elementor\Repeater();$r->add_control('name',['label'=>'Name','type'=>\Elementor\Controls_Manager::TEXT]);$r->add_control('url',['label'=>'URL','type'=>\Elementor\Controls_Manager::URL]);$r->add_control('icon',['label'=>'Icon','type'=>\Elementor\Controls_Manager::MEDIA,'media_types'=>['image']]);$r->add_control('tint',['label'=>'Background Tint','type'=>\Elementor\Controls_Manager::COLOR]);$r->add_control('color',['label'=>'Icon Color','type'=>\Elementor\Controls_Manager::COLOR]);
    $this->add_control('socials',['label'=>'Profiles','type'=>\Elementor\Controls_Manager::REPEATER,'fields'=>$r->get_controls(),'default'=>$d['socials'],'title_field'=>'{{{ name }}}']);$this->end_controls_section();
    $this->start_controls_section('legal',['label'=>'Legal & Bottom Bar']);$r=new \Elementor\Repeater();$r->add_control('label',['label'=>'Label','type'=>\Elementor\Controls_Manager::TEXT]);$r->add_control('url',['label'=>'URL','type'=>\Elementor\Controls_Manager::URL]);$this->add_control('legal_links',['label'=>'Legal Links','type'=>\Elementor\Controls_Manager::REPEATER,'fields'=>$r->get_controls(),'default'=>$d['legal_links'],'title_field'=>'{{{ label }}}']);$this->add_control('copyright',['label'=>'Copyright','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['copyright']]);$this->add_control('build_line',['label'=>'Middle Text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['build_line']]);$this->add_control('back_text',['label'=>'Back to Top Text','type'=>\Elementor\Controls_Manager::TEXT,'default'=>$d['back_text']]);$this->end_controls_section();
    $this->start_controls_section('whatsapp',['label'=>'Floating WhatsApp']);$this->add_control('whatsapp_url',['label'=>'WhatsApp Link','type'=>\Elementor\Controls_Manager::URL,'default'=>$d['whatsapp_url']]);$this->add_control('whatsapp_icon',['label'=>'WhatsApp Icon','type'=>\Elementor\Controls_Manager::MEDIA,'media_types'=>['image'],'default'=>$d['whatsapp_icon']]);$this->end_controls_section();
   }
   protected function render(){ echo ft_v45_footer_markup($this->get_settings_for_display()); }
  }
 }
 $widgets->register(new FT_V45_Global_Header());
 $widgets->register(new FT_V45_Global_Footer());
}
add_action('elementor/widgets/register','ft_v45_register_global_widgets',30);

function ft_v45_brand_markup($s){
 $home=esc_url(ft_v45_url_value($s['home_url']??'',home_url('/')));
 $img=is_array($s['logo_image']??null)?($s['logo_image']['url']??''):'';
 if($img){$w=max(60,min(400,(int)($s['logo_width']??190)));return '<a aria-label="'.esc_attr($s['logo_alt']??'Fraztech').' home" class="brand ft-brand-image notranslate" data-no-translate translate="no" href="'.$home.'"><img class="ft-global-logo-img" src="'.esc_url($img).'" alt="'.esc_attr($s['logo_alt']??'Fraztech').'" style="width:'.$w.'px"/></a>';}
 return '<a aria-label="Fraztech home" class="brand notranslate" data-no-translate translate="no" href="'.$home.'"><span class="brand-mark">'.esc_html($s['brand_mark']??'ft').'</span><span class="brand-word">'.esc_html($s['brand_name']??'fraztech').'<small>'.esc_html($s['brand_tagline']??'DESIGN · CODE · AI').'</small></span></a>';
}
function ft_v45_is_current($url){$path=wp_parse_url($url,PHP_URL_PATH)?:'/';$cur=wp_parse_url(home_url(add_query_arg([], $GLOBALS['wp']->request??'')),PHP_URL_PATH)?:'/';return untrailingslashit($path)===untrailingslashit($cur);}
function ft_v45_header_markup($s){
 if(function_exists('ft_v452_repair_recursive'))$s=ft_v452_repair_recursive($s);
 $o='<header>'.ft_v45_brand_markup($s).'<nav aria-label="Main navigation" id="main-nav">';
 foreach(($s['nav_items']??[]) as $i=>$it){$label=(string)($it['label']??'');$url=ft_v45_url_value($it['url']??'','#');$cur=ft_v45_is_current($url)?' aria-current="page"':'';
  if(($it['type']??'normal')==='dropdown'){$id='ft-nav-sub-'.($i+1);$o.='<div class="nav-dropdown"><a href="'.esc_url($url).'"'.$cur.'>'.esc_html($label).'</a><button aria-controls="'.esc_attr($id).'" aria-expanded="false" aria-label="Show '.esc_attr($label).' categories" class="nav-subtoggle">⌄</button><div class="nav-submenu" id="'.esc_attr($id).'">';foreach(preg_split('/\\r?\\n/',(string)($it['submenu']??'')) as $line){if(trim($line)==='')continue;[$sl,$su]=array_pad(array_map('trim',explode('|',$line,2)),2,'');if($su==='')continue;if(str_starts_with($su,'/'))$su=home_url($su);$o.='<a href="'.esc_url($su).'">'.esc_html($sl).'</a>';}$o.='</div></div>';}
  else{$cls=in_array($label,['Stories & Meetings','How We Work'],true)?' class="stories-nav"':'';$o.='<a'.$cls.' href="'.esc_url($url).'"'.$cur.'>'.esc_html($label).'</a>';}
 }
 $o.='</nav>';
 $lang_on=array_key_exists('language_switcher_enabled',$s)?(($s['language_switcher_enabled']??'')==='yes'):true;
 if($lang_on&&function_exists('ft_v54_language_switcher_markup'))$o.=ft_v54_language_switcher_markup();
 $o.='<a class="nav-cta" href="'.esc_url(ft_v45_url_value($s['cta_url']??'',home_url('/contact/'))).'">'.esc_html($s['cta_text']??'Let’s talk ↗').'</a><button aria-controls="main-nav" aria-expanded="false" aria-label="'.esc_attr($s['menu_label']??'Open menu').'" class="menu-toggle">☰</button></header>';
 return $o;
}
function ft_v45_footer_links($rows){$o='';foreach(($rows??[]) as $r){$o.='<a href="'.esc_url(ft_v45_url_value($r['url']??'','#')).'">'.esc_html($r['label']??'').'</a>';}return $o;}
function ft_v45_footer_markup($s){
 if(function_exists('ft_v452_repair_recursive'))$s=ft_v452_repair_recursive($s);
 if(($s['description']??'')==='Thoughtful design.nComplete digital experiences.')$s['description']="Thoughtful design.\nComplete digital experiences.";
 $o='<footer class="wrap"><div class="footer-top"><div>'.ft_v45_brand_markup($s).'<p>'.nl2br(esc_html($s['description']??''),false).'</p><a class="footer-email" href="mailto:'.esc_attr($s['email']??'info@fraztech.com').'">'.esc_html($s['email']??'info@fraztech.com').'</a></div>';
 $o.='<div><b>'.esc_html($s['explore_title']??'Explore').'</b>'.ft_v45_footer_links($s['explore_links']??[]).'</div><div><b>'.esc_html($s['work_title']??'Work together').'</b>'.ft_v45_footer_links($s['work_links']??[]).'</div><div class="footer-social-column"><b>'.esc_html($s['social_title']??'Connect with me').'</b><div aria-label="Social profiles" class="footer-socials" role="group">';
 foreach(($s['socials']??[]) as $r){$name=$r['name']??'Social';$url=ft_v45_url_value($r['url']??'','#');$icon=is_array($r['icon']??null)?($r['icon']['url']??''):'';$tint=$r['tint']??'#eef4ff';$color=$r['color']??'#1760cf';$o.='<a aria-label="'.esc_attr($name).' (opens in a new tab)" class="footer-social" href="'.esc_url($url).'" rel="noopener noreferrer" style="--social-tint:'.esc_attr($tint).';--social-color:'.esc_attr($color).'" target="_blank" title="'.esc_attr($name).'">'.($icon?'<img alt="" height="22" src="'.esc_url($icon).'" width="22"/>':'').'</a>';}
 $o.='</div></div></div><nav aria-label="Policies and support" class="legal-links">'.ft_v45_footer_links($s['legal_links']??[]).'</nav><div class="footer-bottom"><span>'.esc_html($s['copyright']??'').'</span><span>'.esc_html($s['build_line']??'').'</span><a href="#top">'.esc_html($s['back_text']??'Back to top ↑').'</a></div></footer>';
 $wu=ft_v45_url_value($s['whatsapp_url']??'','https://wa.me/923487096356');$wi=is_array($s['whatsapp_icon']??null)?($s['whatsapp_icon']['url']??''):'';$o.='<a aria-label="Chat with Faraz on WhatsApp (opens new tab)" class="whatsapp" href="'.esc_url($wu).'" rel="noopener noreferrer" target="_blank">'.($wi?'<img alt="" height="29" src="'.esc_url($wi).'" width="29"/>':'').'</a><div id="toast" role="status"></div>';
 return $o;
}

function ft_v45_find_global_widget_settings($type){
 $id=(int)get_option($type==='header'?'ft_v45_global_header_id':'ft_v45_global_footer_id'); if(!$id)return [];
 $raw=get_post_meta($id,'_elementor_data',true);$data=ft_v451_decode_elementor_json($raw);if(!is_array($data)||!$data)return [];
 $want=$type==='header'?'ft-v45-global-header':'ft-v45-global-footer';$stack=$data;while($stack){$el=array_shift($stack);if(!is_array($el))continue;if(($el['widgetType']??'')===$want)return is_array($el['settings']??null)?ft_v451_repair_legacy_unicode($el['settings']):[];foreach(($el['elements']??[]) as $c)$stack[]=$c;}return [];
}
function ft_v45_render_global_header(){ $s=ft_v45_find_global_widget_settings('header'); if(!$s)$s=ft_v45_default_header_settings(); return ft_v45_header_markup($s); }
function ft_v45_render_global_footer(){ $s=ft_v45_find_global_widget_settings('footer'); if(!$s)$s=ft_v45_default_footer_settings(); return ft_v45_footer_markup($s); }

function ft_v45_template_data($type){
 $is_header=$type==='header';$settings=$is_header?ft_v45_default_header_settings():ft_v45_default_footer_settings();
 return [[ 'id'=>ft_v45_uid($type),'elType'=>'widget','widgetType'=>$is_header?'ft-v45-global-header':'ft-v45-global-footer','isInner'=>false,'settings'=>$settings,'elements'=>[] ]];
}
function ft_v45_ensure_global_templates(){
 if(!is_admin()||!current_user_can('manage_options'))return;
 foreach(['header','footer'] as $type){$opt='ft_v45_global_'.$type.'_id';$id=(int)get_option($opt);if($id&&get_post($id))continue;
  $existing=get_posts(['post_type'=>'page','post_status'=>'any','meta_key'=>'_ft_global_layout','meta_value'=>$type,'numberposts'=>1,'fields'=>'ids']);$id=$existing?(int)$existing[0]:0;
  if(!$id)$id=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'Fraztech Global '.ucfirst($type),'post_name'=>'fraztech-global-'.$type,'post_content'=>'']);
  if(!$id||is_wp_error($id))continue;update_post_meta($id,'_ft_global_layout',$type);update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode(ft_v45_template_data($type))));update_post_meta($id,'_elementor_edit_mode','builder');update_post_meta($id,'_elementor_template_type','wp-page');update_post_meta($id,'_wp_page_template','elementor_canvas');if(defined('ELEMENTOR_VERSION'))update_post_meta($id,'_elementor_version',ELEMENTOR_VERSION);update_option($opt,$id);
  $ex=array_map('intval',(array)get_option('ft_excluded_posts',[]));if(!in_array($id,$ex,true)){$ex[]=$id;update_option('ft_excluded_posts',$ex);}
 }
}
add_action('admin_init','ft_v45_ensure_global_templates',20);

// Pure editor-UX upgrade: images first, then links, then text. Token keys and DOM are untouched.
function ft_v45_reorder_section_fields(){
 if(get_option('ft_v45_fields_ordered'))return;
 $ids=get_posts(['post_type'=>array_merge(['page','post'],array_keys(ft_types())),'post_status'=>['publish','draft','private'],'numberposts'=>-1,'fields'=>'ids']);$changed=0;
 foreach($ids as $id){$raw=get_post_meta($id,'_elementor_data',true);if(!$raw)continue;$data=ft_v451_decode_elementor_json($raw);if(!is_array($data)||!$data)continue;$dirty=false;
  $walk=function(&$els) use (&$walk,&$dirty){foreach($els as &$el){if(!is_array($el))continue;if(in_array($el['widgetType']??'',['ft-v43-section','ft-v44-section'],true)&&isset($el['settings']['fields'])&&is_array($el['settings']['fields'])){$priority=['image'=>0,'link'=>1,'text'=>2,'attr'=>3];$indexed=[];foreach($el['settings']['fields'] as $i=>$f)$indexed[]=['i'=>$i,'p'=>$priority[$f['kind']??'text']??9,'f'=>$f];usort($indexed,fn($a,$b)=>$a['p']<=>$b['p'] ?: $a['i']<=>$b['i']);$el['settings']['fields']=array_column($indexed,'f');$dirty=true;}if(!empty($el['elements'])&&is_array($el['elements']))$walk($el['elements']);}}
  ;$walk($data);if($dirty){update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));$changed++;}
 }
 update_option('ft_v45_fields_ordered',['time'=>time(),'documents'=>$changed]);if(class_exists('\Elementor\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}}
}
add_action('admin_init','ft_v45_reorder_section_fields',40);

add_action('admin_menu',function(){
 add_submenu_page('fraztech','Global Header & Footer','Global Header & Footer','manage_options','ft-global-editor-v45','ft_v45_global_admin');
},33);
function ft_v45_global_admin(){
 if(!current_user_can('manage_options'))return;ft_v45_ensure_global_templates();$h=(int)get_option('ft_v45_global_header_id');$f=(int)get_option('ft_v45_global_footer_id');$ord=get_option('ft_v45_fields_ordered');
 echo '<div class="wrap"><h1>Fraztech Global Header & Footer</h1><p>These are global. Edit once and the changes appear across the whole website. Page sections keep the same protected DOM-safe layout.</p>';
 echo '<p><a class="button button-primary button-hero" href="'.esc_url(admin_url('post.php?post='.$h.'&action=elementor')).'">Edit Global Header with Elementor</a> &nbsp; <a class="button button-primary button-hero" href="'.esc_url(admin_url('post.php?post='.$f.'&action=elementor')).'">Edit Global Footer with Elementor</a></p>';
 if(is_array($ord))echo '<p><strong>Page editor upgrade:</strong> image controls moved to the top in '.intval($ord['documents']??0).' Elementor documents. No page DOM/layout was changed.</p>';
 echo '<hr><p><strong>Page images:</strong> Open any page in Elementor → select a Fraztech Section → the first rows under “Section content” are now the Image rows. Open an Image row and use “Choose / Replace Image”.</p></div>';
}
add_action('admin_bar_menu',function($bar){if(!current_user_can('manage_options'))return;$h=(int)get_option('ft_v45_global_header_id');$f=(int)get_option('ft_v45_global_footer_id');if($h)$bar->add_node(['id'=>'ft-edit-header','title'=>'Edit Header','href'=>admin_url('post.php?post='.$h.'&action=elementor')]);if($f)$bar->add_node(['id'=>'ft-edit-footer','title'=>'Edit Footer','href'=>admin_url('post.php?post='.$f.'&action=elementor')]);},90);
