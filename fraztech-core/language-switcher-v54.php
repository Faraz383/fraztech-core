<?php
if (!defined('ABSPATH')) exit;

/*
 * Fraztech V6.0.9 API-free, site-wide language packs.
 *
 * One administrator language selection prepares the complete published site for
 * that language in the browser, saves the resulting phrase pack in WordPress,
 * and future visitors reuse it on every page without downloading/translating
 * each page again. Existing Elementor documents are never rewritten.
 */

function ft_v54_language_catalog(){
 return [
   'en'=>['name'=>'English','native'=>'English','dir'=>'ltr','flag'=>'🇬🇧'],
   'de'=>['name'=>'German','native'=>'Deutsch','dir'=>'ltr','flag'=>'🇩🇪'],
   'ar'=>['name'=>'Arabic','native'=>'العربية','dir'=>'rtl','flag'=>'🇸🇦'],
   'es'=>['name'=>'Spanish','native'=>'Español','dir'=>'ltr','flag'=>'🇪🇸'],
   'nl'=>['name'=>'Dutch','native'=>'Nederlands','dir'=>'ltr','flag'=>'🇳🇱'],
   'fr'=>['name'=>'French','native'=>'Français','dir'=>'ltr','flag'=>'🇫🇷'],
   'ur'=>['name'=>'Urdu','native'=>'اردو','dir'=>'rtl','flag'=>'🇵🇰'],
 ];
}
function ft_v54_language_defaults(){ return ['enabled'=>1,'source'=>'en','languages'=>['en','de','ar','es','nl','fr','ur']]; }
function ft_v54_language_settings(){
 $saved=get_option('ft_v54_languages',[]); if(!is_array($saved))$saved=[];
 $s=array_merge(ft_v54_language_defaults(),$saved); $catalog=ft_v54_language_catalog(); $langs=[];
 foreach((array)($s['languages']??[]) as $code) if(isset($catalog[$code])) $langs[]=$code;
 if(!in_array('en',$langs,true)) array_unshift($langs,'en');
 $s['languages']=array_values(array_unique($langs)); unset($s['api_key']); return $s;
}

function ft_v54_translation_table(){ global $wpdb; return $wpdb->prefix.'ft_translations'; }
function ft_v54_install_translation_table(){
 $version=get_option('ft_v54_translation_table_version'); if($version==='1')return;
 global $wpdb; require_once ABSPATH.'wp-admin/includes/upgrade.php'; $table=ft_v54_translation_table(); $charset=$wpdb->get_charset_collate();
 $sql="CREATE TABLE $table (
  cache_key char(64) NOT NULL,
  target varchar(12) NOT NULL,
  source_text longtext NOT NULL,
  translation longtext NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (cache_key),
  KEY target (target)
 ) $charset;";
 dbDelta($sql); update_option('ft_v54_translation_table_version','1',false);
}
add_action('admin_init','ft_v54_install_translation_table',25);

/* ---------- Site-wide pack revision/status ---------- */
function ft_v543_site_revision(){ $r=(int)get_option('ft_v543_site_revision',1); return max(1,$r); }
function ft_v543_pack_meta(){ $m=get_option('ft_v543_pack_meta',[]); return is_array($m)?$m:[]; }
function ft_v543_pack_ready($lang){
 if($lang==='en')return true; $m=ft_v543_pack_meta(); $x=$m[$lang]??[];
 return !empty($x['complete']) && (int)($x['revision']??0)===ft_v543_site_revision();
}
function ft_v543_bump_revision(){
 update_option('ft_v543_site_revision',ft_v543_site_revision()+1,false);
}
add_action('save_post',function($post_id,$post){
 if(wp_is_post_revision($post_id)||wp_is_post_autosave($post_id)||!$post)return;
 if(in_array($post->post_status,['auto-draft','inherit','trash'],true))return;
 ft_v543_bump_revision();
},100,2);
add_action('deleted_post','ft_v543_bump_revision',100);
add_action('trashed_post','ft_v543_bump_revision',100);
add_action('untrashed_post','ft_v543_bump_revision',100);

/* ---------- Switcher ---------- */
function ft_v54_language_switcher_markup(){
 $s=ft_v54_language_settings(); if(empty($s['enabled']))return '';
 $catalog=ft_v54_language_catalog(); $codes=array_values(array_filter($s['languages'],fn($c)=>isset($catalog[$c]))); if(count($codes)<2)return '';
 $default=$catalog['en'];
 $o='<div class="ft-language-switcher notranslate" data-no-translate translate="no"><button type="button" class="ft-language-trigger" aria-haspopup="true" aria-expanded="false" aria-controls="ft-language-menu"><span class="ft-language-current-flag" aria-hidden="true">'.esc_html($default['flag']).'</span><span class="ft-language-code">EN</span><span class="ft-language-caret" aria-hidden="true">⌄</span></button><div class="ft-language-menu" id="ft-language-menu" role="menu" hidden>';
 foreach($codes as $code){
  $x=$catalog[$code]; $ready=true;
  $o.='<button type="button" class="ft-language-option'.($ready?' is-pack-ready':'').'" role="menuitem" data-ft-lang="'.esc_attr($code).'" data-ft-dir="'.esc_attr($x['dir']).'" data-ft-ready="'.($ready?'1':'0').'"><span class="ft-language-flag" aria-hidden="true">'.esc_html($x['flag']).'</span><span class="ft-language-short">'.esc_html(strtoupper($code)).'</span><b>'.esc_html($x['native']).'</b><i aria-hidden="true"></i></button>';
 }
 $o.='</div><span class="ft-language-status" aria-live="polite"></span><div class="ft-language-notice" role="status" hidden></div></div>'; return $o;
}

add_action('wp_enqueue_scripts',function(){
 if(is_admin())return; $s=ft_v54_language_settings(); if(empty($s['enabled']))return;
 wp_enqueue_style('ft-language-v54',plugins_url('assets/language-v54.css',__FILE__),[],'6.1.2');
 wp_enqueue_script('ft-language-v612',plugins_url('assets/language-v612.js',__FILE__),[],'6.1.2',true);
 $catalog=ft_v54_language_catalog(); $langs=[]; $ready=[];
 foreach($s['languages'] as $c) if(isset($catalog[$c])){ $langs[$c]=$catalog[$c]; $ready[$c]=true; }
 wp_localize_script('ft-language-v612','FT_LANG_V612',[
  'ajaxUrl'=>admin_url('admin-ajax.php'),
  'lookupNonce'=>wp_create_nonce('ft_v541_lookup'),
  'storeNonce'=>wp_create_nonce('ft_v541_store'),
  'sitePackNonce'=>wp_create_nonce('ft_v543_site_pack'),
  'source'=>$s['source'],'languages'=>$langs,'packReady'=>$ready,
  'siteRevision'=>ft_v543_site_revision(),
  'canSave'=>current_user_can('manage_options'),
  'messages'=>[
   'loading'=>'Translating website…','preparing'=>'Preparing language…','downloading'=>'Downloading language model…',
   'scanning'=>'Scanning the complete website…','pages'=>'Collecting website content…','saving'=>'Saving site-wide language pack…',
   'ready'=>'Language ready across the whole website.','error'=>'Translation is temporarily unavailable.',
   'unsupportedBrowser'=>'Local translation needs a supported desktop Chrome browser.',
   'unsupportedLanguage'=>'This language is not supported by the local translator on this browser.',
   'packMissing'=>'',
   'incomplete'=>'The complete website language pack could not be finished, so English was restored.',
   'original'=>'English'
  ],
 ]);
},40);

/* ---------- Shared translation cache ---------- */
function ft_v541_clean_text_batch($raw,$limit=120){
 if(!is_array($raw))return []; $raw=array_slice($raw,0,$limit); $texts=[];
 foreach($raw as $v){
  $v=html_entity_decode(wp_strip_all_tags(wp_unslash((string)$v)),ENT_QUOTES|ENT_HTML5,'UTF-8'); $v=trim($v);
  if($v===''||mb_strlen($v)>5000)continue; $texts[]=$v;
 }
 return array_values(array_unique($texts));
}
function ft_v541_translation_lookup(){ $s=ft_v54_language_settings(); $cat=ft_v54_language_catalog(); $target=sanitize_key(wp_unslash($_POST['target']??''));
 if(!isset($cat[$target])||!in_array($target,$s['languages'],true)||$target==='en') wp_send_json_error(['message'=>'Invalid target language.'],400);
 $texts=ft_v541_clean_text_batch($_POST['texts']??[]); if(!$texts)wp_send_json_success(['translations'=>[],'missing'=>[]]); ft_v54_install_translation_table();
 global $wpdb; $table=ft_v54_translation_table(); $keys=[];$srcByKey=[];
 foreach($texts as $t){$k=hash('sha256','en|'.$target.'|'.$t);$keys[]=$k;$srcByKey[$k]=$t;}
 $found=[];$ph=implode(',',array_fill(0,count($keys),'%s'));$sql=$wpdb->prepare("SELECT cache_key,translation FROM $table WHERE cache_key IN ($ph)",...$keys);
 foreach((array)$wpdb->get_results($sql,ARRAY_A) as $r)$found[$r['cache_key']]=$r['translation'];
 $out=[];$missing=[]; foreach($keys as $k){$src=$srcByKey[$k];if(array_key_exists($k,$found))$out[$src]=(string)$found[$k];else $missing[]=$src;}
 wp_send_json_success(['translations'=>$out,'missing'=>$missing]);
}
add_action('wp_ajax_ft_v541_lookup','ft_v541_translation_lookup');
add_action('wp_ajax_nopriv_ft_v541_lookup','ft_v541_translation_lookup');

function ft_v541_translation_store(){
 if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'Administrator login required to save the shared translation cache.'],403);
 check_ajax_referer('ft_v541_store','nonce'); $s=ft_v54_language_settings(); $cat=ft_v54_language_catalog(); $target=sanitize_key(wp_unslash($_POST['target']??''));
 if(!isset($cat[$target])||!in_array($target,$s['languages'],true)||$target==='en') wp_send_json_error(['message'=>'Invalid target language.'],400);
 $pairsRaw=(string)wp_unslash($_POST['pairs']??''); $pairs=json_decode($pairsRaw,true);
 if(!is_array($pairs))wp_send_json_error(['message'=>'Invalid translation payload.'],400);
 $pairs=array_slice($pairs,0,120); ft_v54_install_translation_table(); global $wpdb; $table=ft_v54_translation_table(); $saved=0;
 foreach($pairs as $row){
  if(!is_array($row))continue;
  $src=trim(html_entity_decode(wp_strip_all_tags((string)($row['source']??'')),ENT_QUOTES|ENT_HTML5,'UTF-8'));
  $tr=trim(html_entity_decode(wp_strip_all_tags((string)($row['translation']??'')),ENT_QUOTES|ENT_HTML5,'UTF-8'));
  if($src===''||$tr===''||mb_strlen($src)>5000||mb_strlen($tr)>10000)continue;
  $k=hash('sha256','en|'.$target.'|'.$src);
  $wpdb->replace($table,['cache_key'=>$k,'target'=>$target,'source_text'=>$src,'translation'=>$tr,'updated_at'=>current_time('mysql')],['%s','%s','%s','%s','%s']); $saved++;
 }
 wp_send_json_success(['saved'=>$saved]);
}
add_action('wp_ajax_ft_v541_store','ft_v541_translation_store');

/* ---------- Site URL inventory and pack completion ---------- */
function ft_v543_public_urls(){
 $urls=[home_url('/')]; $types=get_post_types(['public'=>true],'names'); unset($types['attachment']);
 if($types){
  $ids=get_posts(['post_type'=>array_values($types),'post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','orderby'=>'ID','order'=>'ASC','no_found_rows'=>true]);
  foreach($ids as $id){$u=get_permalink($id);if($u)$urls[]=$u;}
 }
 $urls=array_values(array_unique(array_filter(array_map('esc_url_raw',$urls))));
 return apply_filters('ft_v543_site_pack_urls',$urls);
}
function ft_v543_site_pack_urls_ajax(){
 if(!current_user_can('manage_options'))wp_send_json_error(['message'=>'Administrator login required to prepare a complete website language pack.'],403);
 check_ajax_referer('ft_v543_site_pack','nonce');
 wp_send_json_success(['urls'=>ft_v543_public_urls(),'revision'=>ft_v543_site_revision()]);
}
add_action('wp_ajax_ft_v543_site_urls','ft_v543_site_pack_urls_ajax');

function ft_v543_mark_pack_complete(){
 if(!current_user_can('manage_options'))wp_send_json_error(['message'=>'Administrator login required.'],403);
 check_ajax_referer('ft_v543_site_pack','nonce'); $target=sanitize_key(wp_unslash($_POST['target']??'')); $cat=ft_v54_language_catalog(); $s=ft_v54_language_settings();
 if($target==='en'||!isset($cat[$target])||!in_array($target,$s['languages'],true))wp_send_json_error(['message'=>'Invalid target language.'],400);
 $revision=(int)($_POST['revision']??0); if($revision!==ft_v543_site_revision())wp_send_json_error(['message'=>'Website changed while the pack was being prepared. Please run it again.'],409);
 $count=max(0,(int)($_POST['count']??0)); $pages=max(0,(int)($_POST['pages']??0)); $m=ft_v543_pack_meta();
 $m[$target]=['complete'=>1,'revision'=>$revision,'count'=>$count,'pages'=>$pages,'updated_at'=>current_time('mysql')];
 update_option('ft_v543_pack_meta',$m,false);
 wp_send_json_success(['ready'=>true,'target'=>$target,'count'=>$count,'pages'=>$pages,'revision'=>$revision]);
}
add_action('wp_ajax_ft_v543_mark_complete','ft_v543_mark_pack_complete');


/* ---------- V5.4.6: instant visitor prefetch across all READY languages ---------- */
function ft_v544_translation_lookup_multi(){
 $s=ft_v54_language_settings(); $cat=ft_v54_language_catalog();
 $texts=ft_v541_clean_text_batch($_POST['texts']??[],120);
 if(!$texts) wp_send_json_success(['languages'=>[]]);
 ft_v54_install_translation_table(); global $wpdb; $table=ft_v54_translation_table();
 $targets=[];
 foreach((array)$s['languages'] as $target){
  if($target==='en'||!isset($cat[$target])) continue;
  $targets[]=$target;
 }
 $result=[];
 foreach($targets as $target){
  $keys=[];$srcByKey=[];
  foreach($texts as $t){$k=hash('sha256','en|'.$target.'|'.$t);$keys[]=$k;$srcByKey[$k]=$t;}
  if(!$keys){$result[$target]=[];continue;}
  $ph=implode(',',array_fill(0,count($keys),'%s'));
  $sql=$wpdb->prepare("SELECT cache_key,translation FROM $table WHERE cache_key IN ($ph)",...$keys);
  $found=[];
  foreach((array)$wpdb->get_results($sql,ARRAY_A) as $r)$found[$r['cache_key']]=$r['translation'];
  $map=[];
  foreach($keys as $k) if(isset($found[$k])) $map[$srcByKey[$k]]=(string)$found[$k];
  $result[$target]=$map;
 }
 wp_send_json_success(['languages'=>$result]);
}
add_action('wp_ajax_ft_v544_lookup_multi','ft_v544_translation_lookup_multi');
add_action('wp_ajax_nopriv_ft_v544_lookup_multi','ft_v544_translation_lookup_multi');


/* ---------- V6.1.0: public read-only translation cache endpoints ---------- */
function ft_v610_lookup_rows($texts,$targets){
 ft_v54_install_translation_table(); global $wpdb; $table=ft_v54_translation_table(); $result=[];
 foreach($targets as $target){
  $keys=[];$src=[];foreach($texts as $t){$k=hash('sha256','en|'.$target.'|'.$t);$keys[]=$k;$src[$k]=$t;}
  if(!$keys){$result[$target]=[];continue;}
  $ph=implode(',',array_fill(0,count($keys),'%s'));$sql=$wpdb->prepare("SELECT cache_key,translation FROM $table WHERE cache_key IN ($ph)",...$keys);$found=[];
  foreach((array)$wpdb->get_results($sql,ARRAY_A) as $r)$found[$r['cache_key']]=$r['translation'];$map=[];
  foreach($keys as $k)if(isset($found[$k]))$map[$src[$k]]=(string)$found[$k];$result[$target]=$map;
 }
 return $result;
}
function ft_v610_public_lookup(){
 $s=ft_v54_language_settings();$cat=ft_v54_language_catalog();$target=sanitize_key(wp_unslash($_POST['target']??''));
 if($target==='en'||!isset($cat[$target])||!in_array($target,$s['languages'],true))wp_send_json_error(['message'=>'Invalid language.'],400);
 $texts=ft_v541_clean_text_batch($_POST['texts']??[],100);if(!$texts)wp_send_json_success(['translations'=>[]]);
 $r=ft_v610_lookup_rows($texts,[$target]);wp_send_json_success(['translations'=>$r[$target]??[]]);
}
add_action('wp_ajax_ft_v610_lookup','ft_v610_public_lookup');add_action('wp_ajax_nopriv_ft_v610_lookup','ft_v610_public_lookup');
function ft_v610_public_lookup_multi(){
 $s=ft_v54_language_settings();$cat=ft_v54_language_catalog();$texts=ft_v541_clean_text_batch($_POST['texts']??[],100);if(!$texts)wp_send_json_success(['languages'=>[]]);
 $targets=[];foreach((array)$s['languages'] as $c)if($c!=='en'&&isset($cat[$c]))$targets[]=$c;
 wp_send_json_success(['languages'=>ft_v610_lookup_rows($texts,$targets)]);
}
add_action('wp_ajax_ft_v610_lookup_multi','ft_v610_public_lookup_multi');add_action('wp_ajax_nopriv_ft_v610_lookup_multi','ft_v610_public_lookup_multi');

/* ---------- V5.4.6: admin-only prebuild controller assets ---------- */
add_action('admin_enqueue_scripts',function($hook){
 if($hook!=='fraztech_page_ft-languages-v54') return;
 wp_enqueue_script('ft-language-admin-v544',plugins_url('assets/language-admin-v544.js',__FILE__),[],'6.1.2',true);
 wp_localize_script('ft-language-admin-v544','FT_LANG_ADMIN_V544',[
  'ajaxUrl'=>admin_url('admin-ajax.php'),
  'lookupNonce'=>wp_create_nonce('ft_v541_lookup'),
  'storeNonce'=>wp_create_nonce('ft_v541_store'),
  'sitePackNonce'=>wp_create_nonce('ft_v543_site_pack'),
  'languages'=>ft_v54_language_catalog(),
  'enabled'=>ft_v54_language_settings()['languages'],
  'packReady'=>array_reduce(ft_v54_language_settings()['languages'],function($carry,$code){$carry[$code]=ft_v543_pack_ready($code);return $carry;},[]),
  'messages'=>[
   'start'=>'Preparing all website languages…',
   'scan'=>'Scanning website pages…',
   'model'=>'Preparing translation model…',
   'translate'=>'Translating website content…',
   'save'=>'Saving language pack…',
   'done'=>'All supported language packs are ready.',
   'unsupported'=>'This language is not supported by Chrome local translation.',
   'browser'=>'Use desktop Chrome to prepare the language packs.',
  ],
 ]);
},40);

/* ---------- Settings / clear ---------- */
function ft_v54_save_language_settings(){
 if(!current_user_can('manage_options'))wp_die('Not allowed.'); check_admin_referer('ft_v54_languages_save'); $catalog=ft_v54_language_catalog(); $langs=[];
 foreach((array)($_POST['languages']??[]) as $c){$c=sanitize_key($c);if(isset($catalog[$c]))$langs[]=$c;}
 if(!in_array('en',$langs,true))array_unshift($langs,'en');
 update_option('ft_v54_languages',['enabled'=>isset($_POST['enabled'])?1:0,'source'=>'en','languages'=>array_values(array_unique($langs))],false);
 wp_safe_redirect(admin_url('admin.php?page=ft-languages-v54&saved=1'));exit;
}
add_action('admin_post_ft_v54_save_languages','ft_v54_save_language_settings');

function ft_v54_clear_translation_cache(){
 if(!current_user_can('manage_options'))wp_die('Not allowed.');check_admin_referer('ft_v54_clear_cache');ft_v54_install_translation_table();global $wpdb;
 $wpdb->query('TRUNCATE TABLE '.ft_v54_translation_table()); delete_option('ft_v543_pack_meta');
 wp_safe_redirect(admin_url('admin.php?page=ft-languages-v54&cleared=1'));exit;
}
add_action('admin_post_ft_v54_clear_cache','ft_v54_clear_translation_cache');


/* ---------- V5.4.6: article sidebar scrollspy ---------- */
add_action('wp_enqueue_scripts',function(){
 if(is_admin() || !is_singular('post')) return;
 wp_enqueue_style('ft-article-scrollspy-v546',plugins_url('assets/article-scrollspy-v546.css',__FILE__),[],'5.4.6');
 wp_enqueue_script('ft-article-scrollspy-v546',plugins_url('assets/article-scrollspy-v546.js',__FILE__),[],'5.4.6',true);
},80);

add_action('admin_menu',function(){add_submenu_page('fraztech','Languages','Languages','manage_options','ft-languages-v54','ft_v54_language_admin');},35);
function ft_v54_language_admin(){
 if(!current_user_can('manage_options'))return;
 $s=ft_v54_language_settings();$cat=ft_v54_language_catalog();global $wpdb;ft_v54_install_translation_table();$table=ft_v54_translation_table();$counts=[];$meta=ft_v543_pack_meta();$rev=ft_v543_site_revision();
 foreach(array_keys($cat) as $c){if($c==='en')continue;$counts[$c]=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE target=%s",$c));}
 echo '<div class="wrap"><h1>Fraztech → Languages V5.4.6</h1>';
 if(isset($_GET['saved']))echo '<div class="notice notice-success"><p>Language settings saved.</p></div>';
 if(isset($_GET['cleared']))echo '<div class="notice notice-success"><p>Translation cache and site-wide pack status cleared.</p></div>';
 echo '<p><strong>API-free site-wide packs:</strong> visitors never download translation models and never translate pages themselves. Prepare the packs once here in WordPress admin; after that language switching on the frontend is instant.</p>';
 echo '<div class="notice notice-info inline"><p><strong>One-time admin preparation:</strong> press <em>Prepare all supported languages</em> below. Fraztech scans all published pages once, creates the language packs in the admin area, and stores them in WordPress. Frontend visitors only read those prepared packs. Runtime-only text can no longer block a language switch.</p></div>';
 echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';wp_nonce_field('ft_v54_languages_save');echo '<input type="hidden" name="action" value="ft_v54_save_languages"><table class="form-table"><tbody>';
 echo '<tr><th>Language switcher</th><td><label><input type="checkbox" name="enabled" value="1" '.checked(!empty($s['enabled']),true,false).'> Show in global header</label></td></tr>';
 echo '<tr><th>Languages</th><td>';
 foreach($cat as $code=>$x){$checked=in_array($code,$s['languages'],true);echo '<label style="display:inline-block;min-width:180px;margin:0 14px 10px 0"><input type="checkbox" name="languages[]" value="'.esc_attr($code).'" '.checked($checked,true,false).' '.($code==='en'?'onclick="return false"':'').'> '.esc_html($x['native']).' <code>'.esc_html(strtoupper($code)).'</code></label>';}
 echo '<p class="description">English is the original language. Arabic and Urdu use RTL direction automatically.</p></td></tr></tbody></table><p><button class="button button-primary">Save language settings</button></p></form>';

 echo '<hr><h2>Site-wide language packs</h2>';
 echo '<p><button type="button" class="button button-primary button-hero" id="ft-v544-prepare-all">Prepare all supported languages</button> <span id="ft-v544-prepare-note" style="margin-left:8px;color:#52635d">Run this once before going live.</span></p>';
 echo '<div id="ft-v544-progress" style="display:none;max-width:860px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px 16px;margin:12px 0 18px"><strong id="ft-v544-progress-title">Preparing…</strong><div style="height:10px;background:#eef2ef;border-radius:99px;margin:10px 0 8px;overflow:hidden"><div id="ft-v544-progress-bar" style="height:100%;width:0;background:#1d7a55;transition:width .25s ease"></div></div><div id="ft-v544-progress-text" style="color:#52635d"></div></div>';
 echo '<table class="widefat striped" style="max-width:860px"><thead><tr><th>Language</th><th>Status</th><th>Cached phrases</th><th>Pages scanned</th><th>Updated</th></tr></thead><tbody>';
 foreach($s['languages'] as $code){
  if($code==='en'||!isset($cat[$code]))continue; $m=$meta[$code]??[]; $ready=!empty($m['complete'])&&(int)($m['revision']??0)===$rev;
  echo '<tr><td>'.esc_html($cat[$code]['native']).' ('.esc_html(strtoupper($code)).')</td><td><strong style="color:'.($ready?'#16803d':'#9a6700').'">'.($ready?'READY':'NOT PREPARED / STALE').'</strong></td><td>'.number_format_i18n($counts[$code]??0).'</td><td>'.number_format_i18n((int)($m['pages']??0)).'</td><td>'.esc_html((string)($m['updated_at']??'—')).'</td></tr>';
 }
 echo '</tbody></table>';
 echo '<p class="description" style="max-width:860px">If you edit website text after a pack is prepared, its status becomes stale automatically. Select that language once again while logged in as admin; only new/missing phrases need translation because the existing shared cache is reused.</p>';
 echo '<p><strong>Important:</strong> only the administrator preparation step uses Chrome local translation. Frontend visitors do not see downloads, percentages or translation progress. Once a pack is READY, the stored WordPress translations are applied immediately. Any small runtime-only text is ignored instead of blocking the whole page.</p>';

 echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" style="margin-top:14px">';wp_nonce_field('ft_v54_clear_cache');echo '<input type="hidden" name="action" value="ft_v54_clear_cache"><button class="button">Clear translation cache & packs</button></form>';
 echo '<hr><h2>Safe behavior</h2><p>Home, Work, Services, Projects, Articles, How We Work, images, URLs, Elementor documents and your current styling are not rebuilt or overwritten by this feature.</p></div>';
}
