<?php
/**
 * Plugin Name: Fraztech Core
 * Description: Fraztech content, import, profile settings and private complaint inbox.
 * Version: 6.6.1
 * Author: Muhammad Faraz
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) exit;
define('FT_CORE',__DIR__);
function ft_types(){return ['ft_service'=>'Services','ft_project'=>'Projects','ft_package'=>'Packages','ft_story'=>'Client Stories','ft_faq'=>'FAQs','ft_tool'=>'AI Tools'];}
function ft_register(){
 foreach(ft_types() as $type=>$label){$public=in_array($type,['ft_service','ft_project'],true);register_post_type($type,['label'=>$label,'public'=>$public,'show_ui'=>true,'show_in_menu'=>'fraztech','show_in_rest'=>true,'supports'=>['title','editor','thumbnail','excerpt','page-attributes','revisions'],'rewrite'=>$public?['slug'=>$type==='ft_service'?'services':'projects','with_front'=>false]:false,'has_archive'=>false]);}
 foreach(get_option('ft_routes',[]) as $route=>$id)add_rewrite_rule('^'.preg_quote(trim($route,'/'),'#').'/?$','index.php?post_type=ft_project&p='.(int)$id,'top');
 add_rewrite_rule('^complaints/inbox/?$','index.php?ft_inbox=1','top');
}
add_action('init','ft_register');add_filter('query_vars',function($vars){$vars[]='ft_inbox';return $vars;});
add_action('template_redirect',function(){if(get_query_var('ft_inbox')){auth_redirect();if(!current_user_can('manage_options'))wp_die('Administrator access required.','Access denied',['response'=>403]);wp_safe_redirect(admin_url('admin.php?page=ft-complaints'));exit;}});
add_filter('post_type_link',function($link,$post){$route=get_post_meta($post->ID,'_ft_route',true);return $route?home_url($route):$link;},10,2);
register_activation_hook(__FILE__,function(){ft_register();ft_complaint_table();flush_rewrite_rules();});
register_deactivation_hook(__FILE__,function(){flush_rewrite_rules();});
function ft_setting($key,$default=''){return get_option('ft_settings',[])[$key]??$default;}
function ft_seed(){static $seed;return $seed??($seed=json_decode(file_get_contents(FT_CORE.'/seed.json'),true));}
function ft_find($key){
 // V5: resolve the document that actually owns the canonical route before falling back to meta lookup.
 // Earlier migrations could leave duplicate posts with the same _ft_key; returning an arbitrary duplicate
 // made audits report "editable" while the public route still opened the older non-Elementor post.
 $item=null;foreach((array)ft_seed() as $seed){if(isset($seed['key'])&&(string)$seed['key']===(string)$key){$item=$seed;break;}}
 if(is_array($item)){
  $type=(string)($item['type']??'');$route=(string)($item['route']??'');
  if($type==='ft_project'){$routes=get_option('ft_routes',[]);$id=(int)($routes[$route]??0);if($id&&get_post($id)&&get_post_type($id)==='ft_project')return $id;}
  if($type==='page'&&$route==='/'){$id=(int)get_option('page_on_front');if($id&&get_post($id)&&get_post_type($id)==='page')return $id;}
  if($route!==''){$slug=basename(trim($route,'/')?:'home');$ids=get_posts(['post_type'=>$type,'post_status'=>['publish','draft','private'],'name'=>$slug,'numberposts'=>1,'fields'=>'ids','suppress_filters'=>true]);if($ids)return (int)$ids[0];}
 }
 $ids=get_posts(['post_type'=>array_merge(['page','post'],array_keys(ft_types())),'post_status'=>['publish','draft','private'],'meta_key'=>'_ft_key','meta_value'=>$key,'numberposts'=>1,'fields'=>'ids','orderby'=>'modified','order'=>'DESC','suppress_filters'=>true]);return $ids?(int)$ids[0]:0;
}
function ft_asset_url($path){$assets=get_option('ft_assets',[]);return isset($assets[$path])?wp_get_attachment_url($assets[$path]):get_template_directory_uri().'/static'.$path;}
function ft_resolve($html){
 $html=preg_replace_callback('~(["\'])/assets/([^"\']+)(["\'])~',fn($m)=>$m[1].esc_url(ft_asset_url('/assets/'.$m[2])).$m[3],$html);
 $html=preg_replace_callback('~(href|action)=(["\'])(/(?!/)[^"\']*)\2~',function($m){return $m[1].'='.$m[2].esc_url(home_url($m[3])).$m[2];},$html);
 $defaults=['fiverr'=>'https://www.fiverr.com/s/wbkdkmq','linkedin'=>'https://www.linkedin.com/in/faraz-khan-4b8235256','upwork'=>'https://www.upwork.com/freelancers/~0159ec3f835c4a7b67?mp_source=share','dribbble'=>'https://dribbble.com/FarazDesign','behance'=>'https://www.behance.net/farazdesign10','facebook'=>'https://www.facebook.com/profile.php?id=61575625778962','instagram'=>'https://www.instagram.com/f_ara_zi?stkn=MWhnNDJ3ODlmemx0YQ==','whatsapp'=>'https://wa.me/923487096356'];
 foreach($defaults as $key=>$old){$new=ft_setting($key,$old);if($new!==$old)$html=str_replace([$old,esc_attr($old)],esc_url($new),$html);}
 $email=ft_setting('email','info@fraztech.com');$html=str_replace(['mailto:info@fraztech.com','>info@fraztech.com<'],['mailto:'.esc_attr($email),'>'.esc_html($email).'<'],$html);
 $github=ft_setting('github');if($github)$html=preg_replace('~<a([^>]*)href=(["\'])https://wa.me/[^"\']*\2([^>]*)>Request GitHub links(.*?)</a>~s','<a$1href="'.esc_url($github).'" target="_blank" rel="noopener noreferrer"$3>View GitHub projects$4</a>',$html);
 return $html;
}
function ft_resolve_url($url){return str_starts_with($url,'/assets/')?ft_asset_url($url):$url;}
function ft_dom($html){$dom=new DOMDocument('1.0','UTF-8');$old=libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="utf-8" ?><div id="ft-root">'.$html.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);libxml_clear_errors();libxml_use_internal_errors($old);return $dom;}
function ft_dom_html($dom){$out='';$root=$dom->getElementById('ft-root');if(!$root)return '';foreach($root->childNodes as $node)$out.=$dom->saveHTML($node);return $out;}
function ft_home_testimonials_markup(){
 $reviews = [
  ['name'=>'jd_designstudi0','country'=>'Canada','text'=>'We recently worked with Figmora on the UI/UX design for a SaaS web application, and the experience has been excellent from start to finish. Even with limited initial input and documentation, he quickly understood the product vision and took ownership of the design process.'],
  ['name'=>'shafee99','country'=>'Saudi Arabia','text'=>'Extremely responsible and polite, did the project very fast and with amazing quality and creativity.'],
  ['name'=>'trooox','country'=>'France','text'=>'Thank you for your hard work!'],
  ['name'=>'boxbrandsmarsel','country'=>'United States','text'=>'Was great and efficient, messaged me with updates constantly and was willing to accommodate us.'],
  ['name'=>'liam_reyes','country'=>'Repeat client','text'=>'Good experience with this designer, highly recommend!'],
 ];
 $cards='';
 foreach($reviews as $i=>$r){
  $initials=''; foreach(preg_split('/[^a-z0-9]+/i',$r['name']) as $part){if($part!=='')$initials.=strtoupper(substr($part,0,1));}
  $cards .= '<article class="ft-review-card" role="group" aria-label="Fiverr review by '.esc_attr($r['name']).'"><div class="ft-review-top"><span class="ft-review-avatar ft-avatar-'.($i%5).'" aria-hidden="true"><span>'.esc_html(substr($initials,0,2)).'</span></span><div class="ft-review-person"><strong>'.esc_html($r['name']).'</strong><small>'.esc_html($r['country']).' · Fiverr client</small></div><span class="ft-review-source" aria-label="Fiverr">F</span></div><div class="ft-review-stars" aria-label="5 out of 5 stars">★★★★★</div><p class="ft-review-copy">“'.esc_html($r['text']).'”</p><span class="ft-review-verified">Verified public review</span></article>';
 }
 // Duplicate the same verified set solely for a seamless marquee loop; duplicates are hidden from assistive technology.
 $track = '<div class="ft-review-track">'.$cards.'<div class="ft-review-duplicate" aria-hidden="true">'.$cards.'</div></div>';
 return '<section class="review-section ft-home-reviews" aria-labelledby="ft-review-heading"><div class="wrap review-inner"><div class="ft-review-heading-row"><div class="section-heading"><span class="eyebrow">05 / CLIENT EXPERIENCES</span><h2 id="ft-review-heading">Good work starts<br/>with trust.</h2><p>Feedback shared by clients on my Fiverr profile.</p></div><div class="ft-review-controls"><button type="button" class="ft-review-arrow" data-review-prev aria-label="Scroll reviews left">←</button><button type="button" class="ft-review-arrow" data-review-next aria-label="Scroll reviews right">→</button></div></div><div class="ft-review-viewport" tabindex="0" aria-label="Client reviews carousel">'.$track.'</div><div class="ft-review-foot"><span>5 verified public reviews · Fiverr</span><a href="https://www.fiverr.com/figmora" target="_blank" rel="noopener noreferrer">View Fiverr profile ↗</a></div></div></section>';
}
function ft_render_content($id){
 $layout=get_post_meta($id,'_ft_layout',true);
 if($layout){
  if(get_the_title($id)!==get_post_meta($id,'_ft_original_title',true))$layout=preg_replace('~<h1[^>]*>.*?</h1>~s','<h1>'.esc_html(get_the_title($id)).'</h1>',$layout,1);
  // V6.2.3: replace the homepage Section 05 placeholder BEFORE shortcodes/rendering.
  // No review-detail page link is included; reviews live directly in the homepage section.
  if((int)$id === (int)get_option('page_on_front') && !is_admin()){
   $section = ft_home_testimonials_markup();
   $pattern = '~<section\b(?=[^>]*class=(\"|\')[^\"\']*\breview-section\b[^\"\']*\1)[^>]*>.*?</section>~is';
   $replaced = preg_replace($pattern, $section, $layout, 1, $count);
   if(is_string($replaced) && !empty($count)) $layout = $replaced;
  }
  $body=do_shortcode($layout);
 }else{$body='<section class="wrap page-intro"><div class="section-heading"><span class="eyebrow">'.esc_html(get_post_type($id)==='post'?'INSIGHTS':'FRAZTECH').'</span><h1>'.esc_html(get_the_title($id)).'</h1><p>'.esc_html(get_the_excerpt($id)).'</p></div></section>';if(has_post_thumbnail($id))$body.='<figure class="wrap article-hero">'.get_the_post_thumbnail($id,'full').'</figure>';$body.='<article class="wrap reading ft-article-body">'.apply_filters('the_content',get_post_field('post_content',$id)).'</article>';}
 return ft_resolve($body);
}
function ft_render_card($id){
 $post=get_post($id);$card=get_post_meta($id,'_ft_card',true);$title=get_post_meta($id,'_ft_card_title',true)?:get_the_title($id);$category=get_post_meta($id,'_ft_category',true);$excerpt=get_post_meta($id,'_ft_excerpt',true)?:$post->post_excerpt;$image=get_the_post_thumbnail_url($id,'full')?:get_post_meta($id,'_ft_image',true);$link=get_permalink($id);
 if($post->post_type==='ft_story' && !get_post_meta($id,'_ft_demo',true) && !get_post_meta($id,'_ft_permission',true))return '';
 if($post->post_type==='ft_tool')return '<a class="ai-tool-card" href="'.esc_url(get_post_meta($id,'_ft_url',true)).'" target="_blank" rel="noopener noreferrer"><div class="ai-tool-card-top"><span class="ai-tool-logo"><img src="'.esc_url(ft_resolve_url($image)).'" alt="" width="40" height="40" loading="lazy"></span><span class="ai-tool-arrow" aria-hidden="true">↗</span></div><h3>'.esc_html($title).'</h3><p>'.esc_html($excerpt).'</p><span class="ai-tool-visit">Visit website<span class="sr-only">: '.esc_html($title).' (opens in a new tab)</span></span></a>';
 if(!$card){
  if($post->post_type==='ft_faq')return '<details><summary>'.esc_html($title).'<span>+</span></summary><div>'.wpautop(wp_kses_post($post->post_content)).'</div></details>';
  if($post->post_type==='ft_story'){$video=get_post_meta($id,'_ft_video',true);$demo=get_post_meta($id,'_ft_demo',true);return '<article class="video-card">'.($video?'<div class="video-player"><video controls playsinline preload="none" poster="'.esc_url($image).'"><source src="'.esc_url($video).'" type="video/mp4"></video>'.($demo?'<span class="demo-label">DEMO · NOT A CLIENT TESTIMONIAL</span>':'').'</div>':'').'<h3>'.esc_html($title).'</h3><p>'.esc_html($excerpt).'</p><div class="ft-story-text">'.wpautop(wp_kses_post($post->post_content)).'</div></article>';}
  if($post->post_type==='ft_package')return '<article class="price-card"><span class="eyebrow">'.esc_html($category).'</span><h3>'.esc_html($title).'</h3><p>'.esc_html($excerpt).'</p><div class="price">'.esc_html(get_post_meta($id,'_ft_price',true)).'</div>'.wpautop(wp_kses_post($post->post_content)).'<a class="button" href="'.esc_url(home_url('/contact/')).'">Discuss this package ↗</a></article>';
  if($post->post_type==='ft_service')return '<article class="service-card"><div class="card-top"><span class="service-tool"><img src="'.esc_url($image).'" alt="" width="30" height="30"></span><span class="service-number">'.sprintf('%02d',max(1,$post->menu_order)).'</span></div><h3>'.esc_html($title).'</h3><p>'.esc_html($excerpt).'</p><a class="text-link" href="'.esc_url($link).'">Explore service ↗</a></article>';
  $article=$post->post_type==='post';$card='<a class="'.($article?'article-card library-card':'category-card').'" href="'.esc_url($link).'" data-gallery-category="'.esc_attr($category).'" data-article-category="'.esc_attr($category).'" data-article-search="'.esc_attr($title.' '.$excerpt).'"><div class="'.($article?'article-cover':'category-art').'">'.($image?'<img class="reference-image" src="'.esc_url($image).'" alt="'.esc_attr($title).'" loading="lazy">':'').'</div><small>'.esc_html($category).'</small><h3>'.esc_html($title).'</h3><p>'.esc_html($excerpt).'</p><span class="text-link">'.($article?'Read article':'View project').' ↗</span></a>';
 }else{
  $dom=ft_dom($card);$xp=new DOMXPath($dom);
  $h=$xp->query('//h3')->item(0);if($h)$h->textContent=$title;
  if($post->post_type==='ft_faq'){$h=$xp->query('//summary')->item(0);if($h){$h->textContent=$title;$span=$dom->createElement('span','+');$h->appendChild($span);}}
  if($post->post_type==='ft_story'){$video=get_post_meta($id,'_ft_video',true);if($video){$source=$xp->query('//source')->item(0);if($source)$source->setAttribute('src',$video);}if($image){$v=$xp->query('//video')->item(0);if($v)$v->setAttribute('poster',$image);}$demo=(bool)get_post_meta($id,'_ft_demo',true);foreach($xp->query('//*[contains(@class,"demo-label")]') as $badge){if(!$demo)$badge->parentNode->removeChild($badge);}}
  if($post->post_type==='ft_package'&&get_post_meta($id,'_ft_price',true)){$price=$xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," price ")]')->item(0);if($price)$price->textContent=get_post_meta($id,'_ft_price',true);}
  $p=$xp->query('//p')->item(0);if($p&&$excerpt)$p->textContent=$excerpt;
  if($image){$im=$xp->query('//img')->item(0);if($im)$im->setAttribute('src',$image);}
  foreach(['data-gallery-category','data-article-category'] as $attr){foreach($xp->query('//*[@'.$attr.']') as $el)$el->setAttribute($attr,$category);}
  foreach($xp->query('//*[@data-article-search]') as $el)$el->setAttribute('data-article-search',$title.' '.$excerpt);
  if(in_array($post->post_type,['post','ft_project','ft_service'],true)){foreach($xp->query('//a') as $a){if(str_starts_with($a->getAttribute('href'),'/')){$a->setAttribute('href',$link);break;}}}
  $card=ft_dom_html($dom);
 }
 return ft_resolve($card);
}
add_shortcode('ft_collection',function($a){$a=shortcode_atts(['type'=>'post','group'=>'','limit'=>0],$a);if(!in_array($a['type'],array_merge(['post'],array_keys(ft_types())),true))return '';$args=['post_type'=>$a['type'],'post_status'=>'publish','numberposts'=>((int)$a['limit']?:-1),'orderby'=>'menu_order ID','order'=>'ASC','post__not_in'=>get_option('ft_excluded_posts',[])];if($a['group']){$args['meta_key']='_ft_group';$args['meta_value']=$a['group'];}$out='';foreach(get_posts($args) as $post)$out.=ft_render_card($post->ID);return $out;});
add_shortcode('ft_article_options',function(){$options=['All topics'];foreach(get_posts(['post_type'=>'post','numberposts'=>-1,'post__not_in'=>get_option('ft_excluded_posts',[])]) as $p){$c=get_post_meta($p->ID,'_ft_category',true);if($c)$options[]=$c;}return implode('',array_map(fn($s)=>'<option>'.esc_html($s).'</option>',array_unique($options)));});
add_shortcode('ft_gallery_filters',function($a){$cats=['All categories'];foreach(get_posts(['post_type'=>'ft_project','numberposts'=>-1,'meta_key'=>'_ft_group','meta_value'=>$a['group']??'']) as $p){$c=get_post_meta($p->ID,'_ft_category',true);if($c)$cats[]=$c;}$out='';foreach(array_unique($cats) as $i=>$c)$out.='<button class="'.($i===0?'active':'').'" data-gallery-filter="'.esc_attr($c).'" aria-pressed="'.($i===0?'true':'false').'">'.esc_html($c).'</button>';return $out;});
add_shortcode('ft_search_index',function(){return '<script id="site-search-data" type="application/json">[]</script>';});
add_action('wp_ajax_ft_search','ft_search');add_action('wp_ajax_nopriv_ft_search','ft_search');
function ft_search(){
 $q=sanitize_text_field(wp_unslash($_GET['q']??''));$words=preg_split('/\s+/u',mb_strtolower(mb_substr($q,0,120)),-1,PREG_SPLIT_NO_EMPTY);$matches=[];
 if($words){foreach(get_posts(['post_type'=>['page','post','ft_service','ft_project'],'post_status'=>'publish','numberposts'=>-1]) as $p){if(in_array($p->post_name,['complaints','home'],true)||in_array($p->ID,get_option('ft_excluded_posts',[])))continue;$title=get_the_title($p);$text=mb_strtolower($title.' '.wp_strip_all_tags($p->post_content.' '.get_post_meta($p->ID,'_ft_layout',true)));$ok=true;foreach($words as $w)if(!str_contains($text,$w)){$ok=false;break;}if($ok)$matches[]=['title'=>$title,'url'=>get_permalink($p),'kind'=>$p->post_type==='post'?'Article':($p->post_type==='ft_project'?'Portfolio':($p->post_type==='ft_service'?'Service':'Page')),'score'=>str_contains(mb_strtolower($title),mb_strtolower($q))?100:10];}}
 usort($matches,fn($a,$b)=>$b['score']<=>$a['score']);wp_send_json(['total'=>count($matches),'items'=>array_slice($matches,0,12)]);
}
require FT_CORE.'/admin.php';require FT_CORE.'/import.php';require FT_CORE.'/complaints.php';

add_shortcode('ft_tool_count',function($a){$a=shortcode_atts(['group'=>''],$a);$q=['post_type'=>'ft_tool','post_status'=>'publish','numberposts'=>-1,'fields'=>'ids'];if($a['group']){$q['meta_key']='_ft_group';$q['meta_value']=$a['group'];}return count(get_posts($q));});


add_action('wp_enqueue_scripts',function(){
 wp_register_style('ft-v44-widget',plugins_url('assets/elementor-v44.css',__FILE__),[], '6.2.3');
},1);

require_once FT_CORE . '/repair-v452.php';
require_once FT_CORE . '/elementor-v44.php';
require_once FT_CORE . '/global-editor-v45.php';
require_once FT_CORE . '/v48-boxes-routes.php';
require_once FT_CORE . '/v49-audit-selector.php';
require_once FT_CORE . '/v50-integrity.php';
require_once FT_CORE . '/how-we-work-v52.php';
require_once FT_CORE . '/language-switcher-v54.php';
require_once FT_CORE . '/motion-v550.php';
require_once FT_CORE . '/positioning-v612.php';
require_once FT_CORE . '/runtime-v610.php';

// V4.7 — canvas click-to-edit bridge. Loaded only inside Elementor's preview iframe.
add_action('elementor/preview/enqueue_scripts', function(){
 wp_enqueue_script('ft-v47-click-edit', plugins_url('assets/elementor-v47-click-edit.js', __FILE__), [], '6.2.3', true);
});


// V6.2.4: render testimonials against the FINAL frontend HTML. This works even when
// the active theme/Elementor renders the page without calling ft_render_content().
function ft_v624_filter_frontend_html($html){
 if(!is_string($html)||$html==='')return $html;
 // Remove every local link to the retired standalone reviews page (header/footer/old section).
 $html=preg_replace('~<a\b(?=[^>]*\bhref=("|\')[^"\']*/reviews/?(?:#[^"\']*)?\1)[^>]*>.*?</a>~is','',$html);
 if(is_front_page()){
  $section=ft_home_testimonials_markup();
  $pattern='~<section\b(?=[^>]*\bclass=("|\')[^"\']*\breview-section\b[^"\']*\1)[^>]*>.*?</section>~is';
  $replaced=preg_replace($pattern,$section,$html,1,$count);
  if(is_string($replaced)&&$count>0){$html=$replaced;}
  elseif(stripos($html,'ft-home-reviews')===false && stripos($html,'05 / CLIENT EXPERIENCES')===false){
   // Last-resort fallback if a theme strips the original section wrapper entirely.
   if(stripos($html,'</main>')!==false)$html=preg_replace('~</main>~i',$section.'</main>',$html,1);
   elseif(stripos($html,'</body>')!==false)$html=preg_replace('~</body>~i',$section.'</body>',$html,1);
  }
 }
 return $html;
}
add_action('template_redirect',function(){
 if(is_admin()||wp_doing_ajax())return;
 // Retire the old standalone review page without deleting the WordPress record/data.
 if(is_page('reviews')){wp_safe_redirect(home_url('/#ft-review-heading'),301);exit;}
 ob_start('ft_v624_filter_frontend_html');
},0);

// V6.2.4 homepage-only liquid-glass testimonial carousel.
add_action('wp_enqueue_scripts',function(){
 if(!is_front_page()) return;
 wp_enqueue_style('ft-home-reviews-v623',plugins_url('assets/home-reviews-v623.css',__FILE__),[],'6.2.4');
 wp_enqueue_script('ft-home-reviews-v623',plugins_url('assets/home-reviews-v623.js',__FILE__),[],'6.2.4',true);
},30);

// V6.3.1: lightweight homepage right-side hero motion; no glass overlay blur.
add_action('wp_enqueue_scripts', function(){
 if (!is_front_page()) return;
 wp_enqueue_style('ft-hero-liquid-v631', plugins_url('assets/hero-liquid-motion-v631.css', __FILE__), [], '6.3.1');
 wp_enqueue_script('ft-hero-liquid-v631', plugins_url('assets/hero-liquid-motion-v631.js', __FILE__), [], '6.3.1', true);
}, 30);

require_once __DIR__.'/content-refresh.php';

require_once __DIR__.'/content-v650.php';

require_once FT_CORE . "/additions-v660.php";
