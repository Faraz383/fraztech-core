<?php
if (!defined('ABSPATH')) exit;

/*
 * Fraztech V5.3.1 — How We Work icon-enhanced roadmap page.
 * - Replaces the old Stories & Meetings destination without touching existing Elementor pages.
 * - Creates one interactive Elementor-editable process page at /how-we-work/.
 * - Updates only the matching global header/footer link rows.
 * - Keeps /videos/ as a permanent redirect so old bookmarks never 404.
 */

function ft_v52_hww_tech($name,$icon='',$abbr=''){
 $name=(string)$name;$abbr=$abbr?:strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$name),0,2));
 $visual=$icon?'<img src="'.esc_attr($icon).'" alt="" width="24" height="24" loading="lazy">':'<span class="ft-hww-tech-fallback" aria-hidden="true">'.esc_html($abbr).'</span>';
 return '<span class="ft-hww-tech">'.$visual.'<span>'.esc_html($name).'</span></span>';
}
function ft_v52_hww_step($n,$title,$text,$deliverable=''){
 return '<article class="ft-hww-step"><span class="ft-hww-step-no">'.esc_html(str_pad((string)$n,2,'0',STR_PAD_LEFT)).'</span><div><h3>'.esc_html($title).'</h3><p>'.esc_html($text).'</p>'.($deliverable?'<span class="ft-hww-deliverable">Result · '.esc_html($deliverable).'</span>':'').'</div></article>';
}
function ft_v52_hww_panel($key,$title,$summary,$steps,$tech_groups,$is_active=false){
 $o='<div class="ft-hww-panel'.($is_active?' is-active':'').'" data-ft-hww-panel="'.esc_attr($key).'"'.($is_active?'':' hidden').'><div class="ft-hww-panel-head"><div><span class="eyebrow">SELECTED PROCESS</span><h2>'.esc_html($title).'</h2><p>'.esc_html($summary).'</p></div><div class="ft-hww-panel-chip">Idea <span>→</span> Launch</div></div><div class="ft-hww-roadmap-grid"><div class="ft-hww-timeline">';
 foreach($steps as $i=>$s)$o.=ft_v52_hww_step($i+1,$s[0],$s[1],$s[2]??'');
 $o.='</div><aside class="ft-hww-stack"><span class="eyebrow">TECHNOLOGY & TOOLS</span><h3>Selected around the product.</h3><p>The final stack depends on platform, budget, scale, integrations and long-term maintenance — not a fixed technology list.</p>';
 foreach($tech_groups as $group=>$items){$o.='<div class="ft-hww-tech-group"><b>'.esc_html($group).'</b><div class="ft-hww-tech-list">';foreach($items as $it)$o.=ft_v52_hww_tech($it[0],$it[1]??'',$it[2]??'');$o.='</div></div>';}
 $o.='</aside></div></div>';
 return $o;
}

function ft_v52_hww_layout(){
 $services=[
  'mobile-app'=>[
   'Mobile App Development','From product idea to tested App Store and Google Play builds, with the right native or cross-platform approach.',
   [
    ['Discovery & requirements','We define users, business goals, core problems, must-have features and success criteria.','Approved requirement brief'],
    ['Scope & milestones','We separate MVP requirements from future ideas, confirm dependencies, milestones and delivery priorities.','Final scope & delivery plan'],
    ['UX architecture','User journeys, information architecture, navigation and edge cases are mapped before visual design.','Approved user flows'],
    ['UI design & prototype','High-fidelity screens, reusable components, responsive states and an interactive prototype are created in Figma.','Approved design system & prototype'],
    ['Technology selection','We choose React Native, Flutter or native Swift/Kotlin based on product needs, performance and maintenance.','Technical architecture'],
    ['Frontend development','The approved interface is implemented with reusable components, navigation, state handling and device-safe layouts.','Working app frontend'],
    ['Backend & database','Authentication, business logic, database, admin requirements and server-side workflows are implemented where needed.','Connected backend'],
    ['APIs & integrations','Payments, maps, notifications, analytics, storage and third-party services are connected and error states are handled.','Integrated product'],
    ['QA & device testing','We test functionality, design accuracy, API failures, permissions, responsive states and supported devices.','QA-approved build'],
    ['Client review & refinement','A review build is shared, agreed feedback is applied and launch blockers are closed.','Release candidate'],
    ['Store deployment','Signing, store assets, privacy details, build configuration and submission are prepared for Google Play and App Store.','Submitted / published app'],
    ['Handover & support','Source code, Figma, assets and agreed documentation are handed over with post-launch support planning.','Complete project handover'],
   ],
   [
    'Design'=>[['Figma','/assets/platforms/figma.svg','F']],
    'Cross-platform'=>[['React Native','/assets/platforms/react.svg','RN'],['Flutter','','FL'],['Dart','','D']],
    'Native'=>[['Swift','','SW'],['Kotlin','','KT']],
    'Backend'=>[['Node.js','','NJ'],['Laravel / PHP','','LP'],['Python','','PY'],['Firebase','','FB'],['Supabase','','SB']],
    'Data'=>[['PostgreSQL','','PG'],['MySQL','','MY'],['MongoDB','','MO']],
    'Payments & APIs'=>[['Stripe','','ST'],['PayPal','','PP'],['Apple Pay','','AP'],['Google Pay','','GP'],['REST APIs','','API']],
   ]
  ],
  'web-app'=>[
   'Website & Web App','A structured process for marketing websites, portals and custom web applications — from sitemap to production deployment.',
   [
    ['Discovery & goals','We clarify audiences, business goals, required pages, workflows, content and measurable outcomes.','Project brief'],
    ['Sitemap & requirements','Pages, user roles, forms, data needs, integrations and functional requirements are organized.','Approved sitemap & scope'],
    ['Wireframes & UX','Key screens and user flows are structured before visual polish so content hierarchy is clear.','Approved wireframes'],
    ['UI system','Desktop and responsive interfaces, components, states and interaction patterns are designed in Figma.','Responsive design system'],
    ['Architecture & stack','Frontend, backend, database, hosting and integration choices are made around the actual requirements.','Technical plan'],
    ['Frontend build','The approved UI is implemented responsively with reusable components, accessibility and interaction states.','Responsive frontend'],
    ['Backend & database','Business logic, APIs, authentication, dashboards, CMS needs and data models are developed.','Functional backend'],
    ['Integrations','Payments, email, CRM, analytics, maps, storage or other external services are connected.','Connected workflows'],
    ['QA & performance','Browsers, devices, responsive breakpoints, forms, APIs, accessibility basics and speed are checked.','QA-approved release'],
    ['Deployment','Production environment, domain, SSL, analytics and release configuration are completed.','Live website / web app'],
    ['Handover','Source, Figma, access details and agreed documentation are organized for ownership and future work.','Complete handover'],
   ],
   [
    'Design'=>[['Figma','/assets/platforms/figma.svg','F']],
    'Frontend'=>[['React','/assets/platforms/react.svg','R'],['Next.js','/assets/platforms/nextdotjs.svg','N'],['TypeScript','','TS'],['HTML / CSS / JS','','WEB']],
    'Backend'=>[['Node.js','','NJ'],['Laravel / PHP','','LP'],['Python','','PY']],
    'Database'=>[['PostgreSQL','','PG'],['MySQL','','MY'],['MongoDB','','MO']],
    'Delivery'=>[['GitHub','','GH'],['REST APIs','','API'],['Cloud / hosting','','CL']],
   ]
  ],
  'wordpress'=>[
   'WordPress Website','A managed WordPress workflow for editable business sites, content platforms and WooCommerce experiences.',
   [
    ['Requirements & sitemap','We confirm pages, content types, editing needs, forms, commerce requirements and integrations.','Approved scope'],
    ['UX & page structure','Page hierarchy, conversion paths and responsive content blocks are planned.','Page blueprint'],
    ['UI design','Key pages and reusable sections are designed before development where custom design is required.','Approved UI direction'],
    ['WordPress setup','Staging, WordPress configuration, users, permalink structure and required foundations are prepared.','Working staging site'],
    ['Theme / Elementor build','The approved design is implemented with editable components, templates and responsive controls.','Editable website'],
    ['Dynamic content','Custom post types, blog, portfolios, CMS fields and repeatable content are configured where needed.','Manageable content system'],
    ['Plugins & integrations','Forms, email, CRM, analytics, booking or other approved services are connected.','Integrated site'],
    ['Commerce if required','WooCommerce products, cart, checkout, payments, shipping and store settings are configured.','Functional store'],
    ['Speed & security','Caching, image handling, plugin hygiene, backups and security basics are configured.','Optimized build'],
    ['Responsive QA','Desktop, tablet and mobile layouts, forms, links and editing workflows are tested.','QA-approved site'],
    ['Launch & handover','Domain/hosting launch, admin access and editing guidance are completed.','Live editable WordPress site'],
   ],
   [
    'Design'=>[['Figma','/assets/platforms/figma.svg','F']],
    'CMS'=>[['WordPress','/assets/platforms/wordpress.svg','WP'],['Elementor','','E']],
    'Commerce'=>[['WooCommerce','/assets/platforms/woocommerce.svg','WC']],
    'Development'=>[['PHP','','PHP'],['HTML / CSS / JS','','WEB']],
    'Operations'=>[['Caching','','CA'],['Security','','SE'],['SEO tools','','SEO']],
   ]
  ],
  'shopify'=>[
   'Shopify Store','A conversion-focused Shopify process covering store structure, theme implementation, products, checkout and launch.',
   [
    ['Store strategy','We define audience, catalog structure, markets, key collections, conversion goals and required apps.','Store plan'],
    ['Information architecture','Navigation, collections, filters, product taxonomy and primary customer journeys are organized.','Approved store structure'],
    ['UI direction','Homepage, collection, product, cart and supporting pages are designed or adapted to the selected theme direction.','Approved storefront UI'],
    ['Theme setup','The selected Shopify theme is installed and configured on a development store.','Working theme foundation'],
    ['Custom development','Liquid, sections, templates, metafields and custom frontend behavior are implemented where required.','Customized storefront'],
    ['Products & collections','Product data, variants, collections, tags, filters and merchandising structure are configured.','Organized catalog'],
    ['Cart & checkout','Cart behavior, checkout settings, discounts and customer account requirements are configured.','Purchase flow'],
    ['Payments, tax & shipping','Supported gateways, shipping rules, taxes and market settings are connected based on the merchant setup.','Commerce configuration'],
    ['Apps & integrations','Approved apps for reviews, email, subscriptions, analytics or operations are configured.','Integrated store'],
    ['QA & launch','Responsive storefront, product flows, cart, checkout, links and critical store settings are tested before launch.','Launch-ready store'],
    ['Handover & growth','Admin guidance and recommendations for future merchandising, CRO and iteration are provided.','Live Shopify store'],
   ],
   [
    'Design'=>[['Figma','/assets/platforms/figma.svg','F']],
    'Platform'=>[['Shopify','/assets/platforms/shopify.svg','SH'],['Liquid','','LQ']],
    'Frontend'=>[['HTML / CSS / JS','','WEB']],
    'Commerce'=>[['Shopify Payments','','SP'],['PayPal','','PP'],['Metafields','','MF']],
    'Growth'=>[['Analytics','','AN'],['Email / CRM','','CRM'],['Shopify Apps','','APP']],
   ]
  ],
  'saas'=>[
   'SaaS, CMS & Admin Platforms','A product-development roadmap for multi-role systems, dashboards, internal tools and subscription platforms.',
   [
    ['Product discovery','Users, roles, workflows, business rules and success criteria are clarified.','Product requirements'],
    ['Roles & permissions','Access levels, admin capabilities, approval rules and sensitive actions are mapped.','Permission matrix'],
    ['UX flows','Primary workflows, navigation, states, tables, forms and dashboard priorities are designed.','Approved flows'],
    ['Design system','Reusable components, data-display patterns, forms and responsive behavior are created.','Scalable UI system'],
    ['System architecture','Frontend, backend, database, auth, storage, billing and infrastructure are planned.','Technical architecture'],
    ['Frontend development','Dashboards, workflows, tables, forms and responsive views are implemented.','Working application UI'],
    ['Backend & APIs','Business rules, data models, APIs, jobs and server-side validation are developed.','Application backend'],
    ['Auth & billing','Authentication, role-based access, subscriptions, invoices and webhooks are integrated where needed.','Secure account flows'],
    ['QA & security checks','Permissions, edge cases, validation, data handling and core performance are tested.','QA-approved product'],
    ['Deployment & monitoring','Production environments, logs, analytics and release processes are configured.','Production SaaS'],
    ['Handover & iteration','Documentation, source access and the next product roadmap are organized.','Maintainable product'],
   ],
   [
    'Design'=>[['Figma','/assets/platforms/figma.svg','F']],
    'Frontend'=>[['React','/assets/platforms/react.svg','R'],['Next.js','/assets/platforms/nextdotjs.svg','N'],['TypeScript','','TS']],
    'Backend'=>[['Node.js','','NJ'],['Laravel','','LA'],['Python','','PY']],
    'Data'=>[['PostgreSQL','','PG'],['MySQL','','MY']],
    'Product systems'=>[['RBAC / Auth','','AU'],['Stripe billing','','ST'],['Webhooks','','WH'],['REST APIs','','API']],
   ]
  ],
  'ai-automation'=>[
   'AI & Automation','AI is added where it improves a real workflow, with evaluation, guardrails and maintainability built into the process.',
   [
    ['Use-case discovery','We identify the actual task, user pain point, expected outcome and whether AI is the right solution.','Validated AI use case'],
    ['Data & privacy review','Required data, permissions, retention and sensitive information are mapped before implementation.','Data plan'],
    ['UX & fallback design','AI interactions, user controls, loading states, confidence, errors and non-AI fallback paths are designed.','AI experience flow'],
    ['Model & architecture choice','Model/API, retrieval approach, automation platform and cost/latency tradeoffs are selected.','Technical AI plan'],
    ['Prototype','A small working flow is created to test usefulness before building the full feature.','Validated prototype'],
    ['Integration','Model APIs, knowledge sources, tools, business systems and application logic are connected.','Working AI feature'],
    ['Automation workflows','Triggers, approvals, routing, notifications and recurring business steps are automated where useful.','Automated workflow'],
    ['Evaluation & guardrails','Quality, failure cases, prompt behavior, permissions and output constraints are tested.','Evaluation results'],
    ['Deployment & monitoring','Usage, cost, errors and key quality signals are monitored after release.','Production AI workflow'],
    ['Refinement','Real usage is reviewed and the system is improved around behavior and outcomes.','Iteration plan'],
   ],
   [
    'Design'=>[['Figma','/assets/platforms/figma.svg','F']],
    'AI'=>[['OpenAI','/assets/platforms/openai.svg','AI'],['RAG / vectors','','RAG']],
    'Development'=>[['Python','','PY'],['Node.js','','NJ'],['REST APIs','','API']],
    'Automation'=>[['Zapier','/assets/platforms/zapier.svg','Z'],['n8n','','N8'],['Make','','MK']],
    'Operations'=>[['Evaluation','','EV'],['Monitoring','','MO'],['Guardrails','','GR']],
   ]
  ],
  'ui-ux'=>[
   'UI/UX Design','A design-only process that turns requirements into developer-ready interfaces, prototypes and reusable systems.',
   [
    ['Brief & goals','We understand the product, users, business goals, constraints and required screens.','Design brief'],
    ['Research & references','Competitors, patterns, visual references and relevant user expectations are reviewed.','Direction board'],
    ['Information architecture','Content, navigation and screen hierarchy are organized.','IA / sitemap'],
    ['User flows','Important journeys and decision points are mapped before visual design.','User flow diagrams'],
    ['Wireframes','Low-fidelity layouts establish structure, hierarchy and feature placement.','Approved wireframes'],
    ['Visual direction','Typography, color, spacing, iconography and component language are defined.','UI direction'],
    ['High-fidelity UI','Final screens and responsive states are designed around the approved direction.','High-fidelity screens'],
    ['Design system','Reusable components, variants, states and core design tokens are organized.','Component system'],
    ['Prototype','Key flows are connected into an interactive prototype for review and handoff.','Clickable prototype'],
    ['Review & refinement','Feedback is applied while keeping flows, consistency and scope aligned.','Approved final design'],
    ['Developer handoff','Figma files, components, assets, spacing and behavior notes are prepared for implementation.','Developer-ready handoff'],
   ],
   [
    'Design'=>[['Figma','/assets/platforms/figma.svg','F'],['FigJam','','FJ']],
    'Supporting tools'=>[['Adobe XD','','XD'],['Photoshop','','PS'],['Illustrator','','AI']],
    'Handoff'=>[['Prototype','','PR'],['Components','','CO'],['Design tokens','','DT']],
   ]
  ],
 ];

 $o='<section class="wrap ft-hww-hero"><div class="ft-hww-hero-copy"><span class="eyebrow">HOW WE WORK</span><h1>From first requirement<br>to <em>real delivery.</em></h1><p>Choose a service and explore how Fraztech moves from discovery and design to development, testing, launch and handover.</p><div class="ft-hww-hero-actions"><a class="button" href="#ft-hww-process">Explore the process <span aria-hidden="true">↓</span></a><a class="button secondary" href="/contact/">Discuss a project <span aria-hidden="true">↗</span></a></div></div><div class="ft-hww-orbit" aria-label="Fraztech delivery process"><div class="ft-hww-orbit-core"><span>IDEA</span><b>→</b><span>LAUNCH</span></div><span class="ft-hww-orbit-node n1">01<br><b>Understand</b></span><span class="ft-hww-orbit-node n2">02<br><b>Design</b></span><span class="ft-hww-orbit-node n3">03<br><b>Build</b></span><span class="ft-hww-orbit-node n4">04<br><b>Test</b></span></div></section>';

 $o.='<section class="wrap section ft-hww-selector" id="ft-hww-process"><div class="section-heading"><span class="eyebrow">CHOOSE A SERVICE</span><h2>One page. A process for every build.</h2><p>Select a service to see the workflow, deliverables and technology options that typically shape that kind of project.</p></div><div class="ft-hww-tabs" role="tablist" aria-label="Service process">';
 $tabs=['mobile-app'=>'Mobile App','web-app'=>'Website / Web App','wordpress'=>'WordPress','shopify'=>'Shopify','saas'=>'SaaS / CMS','ai-automation'=>'AI & Automation','ui-ux'=>'UI/UX Design'];
 foreach($tabs as $k=>$label)$o.='<div class="ft-hww-tab-box"><button type="button" class="ft-hww-tab'.($k==='mobile-app'?' is-active':'').'" data-ft-hww-tab="'.esc_attr($k).'" role="tab" aria-selected="'.($k==='mobile-app'?'true':'false').'">'.esc_html($label).'</button></div>';
 $o.='</div><div class="ft-hww-panels">';
 $first=true;foreach($services as $k=>$cfg){$o.=ft_v52_hww_panel($k,$cfg[0],$cfg[1],$cfg[2],$cfg[3],$first);$first=false;}
 $o.='</div></section>';

 $o.='<section class="ft-hww-quality"><div class="wrap section"><div class="section-heading"><span class="eyebrow">QUALITY BEFORE DELIVERY</span><h2>Designed, built, tested — then handed over.</h2><p>The exact checks vary by project, but the release is reviewed across the areas that matter to the product.</p></div><div class="ft-hww-quality-grid"><article><span>01</span><h3>Functionality</h3><p>Core flows, forms, APIs, permissions and expected error states.</p></article><article><span>02</span><h3>Responsive & device QA</h3><p>Supported screens, browsers or mobile devices are checked against the approved design.</p></article><article><span>03</span><h3>Performance</h3><p>Assets, loading behavior and high-impact performance issues are reviewed before launch.</p></article><article><span>04</span><h3>Client review</h3><p>A release candidate is shared so agreed feedback can be closed before delivery.</p></article></div></div></section>';

 $o.='<section class="wrap section ft-hww-handoff"><div class="ft-hww-handoff-card"><div><span class="eyebrow">LAUNCH & HANDOVER</span><h2>You keep the product.<br>We keep the process clear.</h2><p>Handover can include source code, Figma files, approved assets, deployment access and project-specific documentation. Ownership and support terms are agreed for the project before work starts.</p></div><div class="ft-hww-handoff-list"><span>Source & design files</span><span>Deployment / publishing</span><span>Access & credentials handoff</span><span>Agreed documentation</span><span>Post-launch support plan</span></div></div></section>';

 $o.='<section class="wrap section ft-hww-cta"><div><span class="eyebrow">HAVE A PROJECT IN MIND?</span><h2>Let’s map the right process for it.</h2><p>Share the idea, requirements or even an unfinished brief. We can define the right starting point together.</p></div><a class="button" href="/contact/">Start a conversation <span aria-hidden="true">↗</span></a></section>';
 return $o;
}


function ft_v53_hww_body_layout(){
 $full=ft_v52_hww_layout();
 if(!class_exists('DOMDocument')) return preg_replace('~<section class="wrap ft-hww-hero".*?</section>~s','',$full,1);
 $dom=ft_dom($full);$root=$dom->getElementById('ft-root');$out='';
 if(!$root)return $full;
 foreach($root->childNodes as $n){
  if($n instanceof DOMElement){$classes=' '.preg_replace('/\s+/',' ',trim($n->getAttribute('class'))).' ';if(str_contains($classes,' ft-hww-hero '))continue;}
  $out.=$dom->saveHTML($n);
 }
 return $out;
}
function ft_v53_hww_page_id(){
 $id=(int)get_option('ft_v52_hww_page_id');if($id&&get_post($id)&&get_post_type($id)==='page')return $id;
 $p=get_page_by_path('how-we-work',OBJECT,'page');return $p?(int)$p->ID:0;
}
function ft_v53_ensure_page(){
 $id=ft_v53_hww_page_id();
 if(!$id){
  $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'How We Work','post_name'=>'how-we-work','post_content'=>'','post_excerpt'=>'Explore the Fraztech process from requirements and design to development, testing, launch and handover.'],true);
  if(is_wp_error($id))return ['id'=>0,'error'=>$id->get_error_message()];
 }
 if(get_post_status($id)!=='publish')wp_update_post(['ID'=>$id,'post_status'=>'publish','post_name'=>'how-we-work']);
 update_option('ft_v52_hww_page_id',(int)$id,false);update_post_meta($id,'_ft_key','/how-we-work/');update_post_meta($id,'_ft_original_title','How We Work');update_post_meta($id,'_ft_v53_managed','1');
 return ['id'=>(int)$id,'error'=>''];
}
function ft_v53_decode_el($raw){
 if(function_exists('ft_v451_decode_elementor_json'))return ft_v451_decode_elementor_json($raw);
 $d=json_decode((string)$raw,true);if(is_array($d))return $d;$d=json_decode(wp_unslash((string)$raw),true);return is_array($d)?$d:[];
}
function ft_v53_is_hww_body_widget($el){
 if(!is_array($el)||($el['widgetType']??'')!=='ft-v44-section')return false;
 $c=' '.preg_replace('/\s+/',' ',trim((string)($el['settings']['outer_classes']??''))).' ';
 foreach(['ft-hww-selector','ft-hww-quality','ft-hww-handoff','ft-hww-cta'] as $x)if(str_contains($c,' '.$x.' '))return true;
 return false;
}
function ft_v53_body_status_from_data($data){
 $found=['selector'=>false,'quality'=>false,'handoff'=>false,'cta'=>false];
 foreach((array)$data as $el){
  if(!is_array($el)||($el['widgetType']??'')!=='ft-v44-section')continue;
  $c=' '.preg_replace('/\s+/',' ',trim((string)($el['settings']['outer_classes']??''))).' ';
  if(str_contains($c,' ft-hww-selector '))$found['selector']=true;
  if(str_contains($c,' ft-hww-quality '))$found['quality']=true;
  if(str_contains($c,' ft-hww-handoff '))$found['handoff']=true;
  if(str_contains($c,' ft-hww-cta '))$found['cta']=true;
 }
 $found['complete']=!in_array(false,$found,true);return $found;
}
function ft_v53_install_roadmap_body($force=false){
 $page=ft_v53_ensure_page();if(!empty($page['error']))return ['ok'=>false,'id'=>0,'error'=>$page['error']];$id=(int)$page['id'];
 $raw=(string)get_post_meta($id,'_elementor_data',true);$current=ft_v53_decode_el($raw);
 $status=ft_v53_body_status_from_data($current);if(!$force&&!empty($status['complete']))return ['ok'=>true,'id'=>$id,'changed'=>false,'widgets'=>count($current),'error'=>''];
 if(!function_exists('ft_v44_build_data'))return ['ok'=>false,'id'=>$id,'error'=>'DOM-safe Elementor builder is unavailable.'];
 $body=ft_v53_hww_body_layout();$body_data=ft_v44_build_data($body,$id);
 if(!is_array($body_data)||count($body_data)<4)return ['ok'=>false,'id'=>$id,'error'=>'Roadmap widgets could not be generated.'];
 $new_status=ft_v53_body_status_from_data($body_data);if(empty($new_status['complete']))return ['ok'=>false,'id'=>$id,'error'=>'Generated roadmap is incomplete before write.'];
 if(!get_post_meta($id,'_ft_v53_preappend_backup_saved',true)){
  update_post_meta($id,'_ft_v53_preappend_elementor_data',$raw);update_post_meta($id,'_ft_v53_preappend_layout',(string)get_post_meta($id,'_ft_layout',true));update_post_meta($id,'_ft_v53_preappend_backup_saved','1');update_post_meta($id,'_ft_v53_preappend_backup_time',time());
 }
 $kept=[];foreach((array)$current as $el)if(!ft_v53_is_hww_body_widget($el))$kept[]=$el;
 // Preserve the user's existing Hero and any other existing page sections; append only the roadmap body.
 $merged=array_values(array_merge($kept,$body_data));
 update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($merged)));update_post_meta($id,'_elementor_edit_mode','builder');update_post_meta($id,'_elementor_template_type','wp-page');if(defined('ELEMENTOR_VERSION'))update_post_meta($id,'_elementor_version',ELEMENTOR_VERSION);
 update_post_meta($id,'_ft_layout',wp_slash(ft_v52_hww_layout()));update_post_meta($id,'_ft_v53_roadmap_version','5.3.1');clean_post_cache($id);
 if(class_exists('\\Elementor\\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}}
 return ['ok'=>true,'id'=>$id,'changed'=>true,'widgets'=>count($merged),'body_widgets'=>count($body_data),'error'=>''];
}
function ft_v53_patch_global_doc($id,$kind){
 if(!$id||!get_post($id))return ['ok'=>false,'changed'=>false,'error'=>'Global '.$kind.' document not found.'];
 $raw=(string)get_post_meta($id,'_elementor_data',true);$data=ft_v53_decode_el($raw);if(!$data)return ['ok'=>false,'changed'=>false,'error'=>'Global '.$kind.' Elementor data is empty.'];
 $changed=false;$matched=false;$target=home_url('/how-we-work/');
 $walk=function(&$els) use (&$walk,&$changed,&$matched,$kind,$target){
  foreach($els as &$el){if(!is_array($el))continue;$want=$kind==='header'?'ft-v45-global-header':'ft-v45-global-footer';
   if(($el['widgetType']??'')===$want){$s=&$el['settings'];
    if($kind==='header'){$rows=is_array($s['nav_items']??null)?$s['nav_items']:[];
     foreach($rows as &$r){$label=trim((string)($r['label']??''));$u=is_array($r['url']??null)?(string)($r['url']['url']??''):(string)($r['url']??'');
      if($label==='How We Work'||$label==='Stories & Meetings'||str_contains($u,'/videos/')||str_contains($u,'/how-we-work/')){$matched=true;if($label!=='How We Work'){$r['label']='How We Work';$changed=true;}if(rtrim($u,'/')!==rtrim($target,'/')){$r['url']=function_exists('ft_v45_url_setting')?ft_v45_url_setting($target):['url'=>$target];$changed=true;}}
     }unset($r);if(!$matched){$rows[]=['_id'=>'navhowwework','label'=>'How We Work','url'=>function_exists('ft_v45_url_setting')?ft_v45_url_setting($target):['url'=>$target],'type'=>'normal','submenu'=>''];$matched=true;$changed=true;}$s['nav_items']=$rows;
    }else{$rows=is_array($s['work_links']??null)?$s['work_links']:[];
     foreach($rows as &$r){$label=trim((string)($r['label']??''));$u=is_array($r['url']??null)?(string)($r['url']['url']??''):(string)($r['url']??'');$ll=strtolower($label);
      if(str_contains($ll,'how we work')||str_contains($ll,'meeting')||str_contains($ll,'stor')||str_contains($u,'/videos/')||str_contains($u,'/how-we-work/')){$matched=true;if($label!=='How we work'){$r['label']='How we work';$changed=true;}if(rtrim($u,'/')!==rtrim($target,'/')){$r['url']=function_exists('ft_v45_url_setting')?ft_v45_url_setting($target):['url'=>$target];$changed=true;}}
     }unset($r);if(!$matched){$rows[]=['_id'=>'workhowwework','label'=>'How we work','url'=>function_exists('ft_v45_url_setting')?ft_v45_url_setting($target):['url'=>$target]];$matched=true;$changed=true;}$s['work_links']=$rows;
    }
   }
   if(!empty($el['elements'])&&is_array($el['elements']))$walk($el['elements']);
  }
 };
 $walk($data);if($changed){update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));clean_post_cache($id);if(class_exists('\\Elementor\\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}}}
 return ['ok'=>$matched,'changed'=>$changed,'error'=>$matched?'':'No matching global '.$kind.' widget/link was found.'];
}
function ft_v53_retire_videos(){
 $old=get_page_by_path('videos',OBJECT,'page');if(!$old)return ['ok'=>true,'id'=>0,'status'=>'missing'];$id=(int)$old->ID;if(get_post_status($id)!=='draft')wp_update_post(['ID'=>$id,'post_status'=>'draft']);return ['ok'=>true,'id'=>$id,'status'=>get_post_status($id)];
}
function ft_v53_apply_all($force=false){
 $page=ft_v53_install_roadmap_body($force);
 $h=ft_v53_patch_global_doc((int)get_option('ft_v45_global_header_id'),'header');
 $f=ft_v53_patch_global_doc((int)get_option('ft_v45_global_footer_id'),'footer');
 $old=ft_v53_retire_videos();flush_rewrite_rules(false);
 $ok=!empty($page['ok'])&&!empty($h['ok']);$out=['ok'=>$ok,'page'=>$page,'header'=>$h,'footer'=>$f,'old'=>$old,'time'=>time()];update_option('ft_v53_last_setup',$out,false);if($ok)update_option('ft_v53_setup_version','5.3.1',false);return $out;
}
function ft_v53_maybe_setup(){
 if(!is_user_logged_in()||!current_user_can('manage_options'))return;if(get_option('ft_v53_setup_version')==='5.3.1')return;ft_v53_apply_all(false);
}
add_action('admin_init','ft_v53_maybe_setup',99);add_action('wp_loaded','ft_v53_maybe_setup',99);

add_action('template_redirect',function(){if(is_admin())return;$path=(string)wp_parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH);$home_path=(string)wp_parse_url(home_url('/'),PHP_URL_PATH);if($home_path&&$home_path!=='/'&&str_starts_with($path,$home_path))$path=substr($path,strlen($home_path));$path='/'.trim($path,'/').'/';if($path==='/videos/'){wp_safe_redirect(home_url('/how-we-work/'),301);exit;}},0);

add_action('wp_enqueue_scripts',function(){
 $id=ft_v53_hww_page_id();if(!$id)return;$current=(int)get_queried_object_id();$preview=(int)($_GET['elementor-preview']??0);if($current!==$id&&$preview!==$id)return;
 wp_enqueue_style('ft-how-we-work-v53',plugins_url('assets/how-we-work-v52.css',__FILE__),[], '5.3.1');wp_enqueue_script('ft-how-we-work-v53',plugins_url('assets/how-we-work-v52.js',__FILE__),[], '5.3.1',true);
},120);
add_action('elementor/preview/enqueue_styles',function(){$id=ft_v53_hww_page_id();$preview=(int)($_GET['elementor-preview']??0);if(!$id||($preview&&$preview!==$id))return;wp_enqueue_style('ft-how-we-work-v53',plugins_url('assets/how-we-work-v52.css',__FILE__),[], '5.3.1');});
add_action('elementor/preview/enqueue_scripts',function(){$id=ft_v53_hww_page_id();$preview=(int)($_GET['elementor-preview']??0);if(!$id||($preview&&$preview!==$id))return;wp_enqueue_script('ft-how-we-work-v53',plugins_url('assets/how-we-work-v52.js',__FILE__),[], '5.3.1',true);},30);

function ft_v53_header_url_status(){
 $settings=function_exists('ft_v45_find_global_widget_settings')?ft_v45_find_global_widget_settings('header'):[];$target=rtrim(home_url('/how-we-work/'),'/');foreach((array)($settings['nav_items']??[]) as $r){if(trim((string)($r['label']??''))!=='How We Work')continue;$u=is_array($r['url']??null)?(string)($r['url']['url']??''):(string)($r['url']??'');return rtrim($u,'/')===$target;}return false;
}
add_action('admin_menu',function(){add_submenu_page('fraztech','How We Work','How We Work','manage_options','ft-how-we-work-v53','ft_v53_admin');},36);
function ft_v53_admin(){
 if(!current_user_can('manage_options'))return;$notice='';$result=null;
 if(isset($_POST['ft_v53_apply'])&&check_admin_referer('ft_v53_apply')){$result=ft_v53_apply_all(true);$notice='<div class="notice '.(!empty($result['ok'])?'notice-success':'notice-error').'"><p><strong>'.(!empty($result['ok'])?'How We Work roadmap and links applied successfully.':'Repair did not complete — see status below.').'</strong></p></div>';}
 $id=ft_v53_hww_page_id();$data=$id?ft_v53_decode_el((string)get_post_meta($id,'_elementor_data',true)):[];$st=ft_v53_body_status_from_data($data);$last=$result?:get_option('ft_v53_last_setup',[]);$header_ok=ft_v53_header_url_status();$old=get_page_by_path('videos',OBJECT,'page');
 echo '<div class="wrap"><h1>Fraztech → How We Work V5.3</h1>'.$notice.'<p>V5.3 preserves your existing Hero and appends the interactive roadmap as separate DOM-safe Elementor sections. Header repair runs independently, so a page-build problem can no longer leave the menu pointing at /videos/.</p><table class="widefat striped" style="max-width:900px;margin:18px 0"><tbody>';
 $rows=['How We Work page exists'=>(bool)$id,'Interactive service selector installed'=>(bool)($st['selector']??false),'Quality section installed'=>(bool)($st['quality']??false),'Handover section installed'=>(bool)($st['handoff']??false),'Final CTA installed'=>(bool)($st['cta']??false),'Header URL is /how-we-work/'=>$header_ok,'Old /videos/ page is not public'=>(!$old||get_post_status($old->ID)!=='publish')];foreach($rows as $k=>$v)echo '<tr><td><strong>'.esc_html($k).'</strong></td><td>'.($v?'<span style="color:#087a35;font-weight:700">OK</span>':'<span style="color:#b32d2e;font-weight:700">Needs attention</span>').'</td></tr>';
 echo '</tbody></table>';
 if(!empty($last['page']['error']))echo '<div class="notice notice-error inline"><p><strong>Page error:</strong> '.esc_html($last['page']['error']).'</p></div>';if(!empty($last['header']['error']))echo '<div class="notice notice-warning inline"><p><strong>Header:</strong> '.esc_html($last['header']['error']).'</p></div>';
 echo '<form method="post" style="margin:18px 0">';wp_nonce_field('ft_v53_apply');echo '<button class="button button-primary button-hero" name="ft_v53_apply">Apply / Repair How We Work Now</button></form>';
 if($id)echo '<p><a class="button" href="'.esc_url(admin_url('post.php?post='.$id.'&action=elementor')).'">Edit with Elementor</a> &nbsp; <a class="button" target="_blank" href="'.esc_url(get_permalink($id)).'">View page ↗</a></p>';
 echo '<p style="max-width:850px">This action touches only the How We Work page body, its matching global header/footer links, and the legacy /videos/ destination. It does not rebuild Home, Work, projects, articles or your existing design/image edits.</p></div>';
}
