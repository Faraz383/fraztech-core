<?php
if (!defined('ABSPATH')) exit;

/** Target only the four documents touched by V6.1.1. */
function ft_v612_target_pages(){
 $ids=[];$front=(int)get_option('page_on_front');if($front)$ids[]=$front;
 foreach(['about','solutions','services'] as $slug){$p=get_page_by_path($slug);if($p)$ids[]=(int)$p->ID;}
 return array_values(array_unique(array_filter($ids)));
}

/** Repair the specific JSON damage caused when V6.1.1 saved Elementor JSON without wp_slash(). */
function ft_v612_decode_current_elementor($id,&$source='current'){
 $raw=(string)get_post_meta($id,'_elementor_data',true);
 if($raw!==''){$data=json_decode($raw,true);if(is_array($data))return $data;}
 if($raw!==''){
   $fixed=preg_replace_callback('/"outer_attrs":"(.*?)","template_b64":/s',function($m){return '"outer_attrs":'.wp_json_encode($m[1],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).',"template_b64":';},$raw);
   if(is_string($fixed)){$data=json_decode($fixed,true);if(is_array($data)){$source='repaired-current';return $data;}}
 }
 // Last-resort fallbacks are used only if the current document cannot be recovered.
 foreach(['_ft_v50_pre_repair_data','_ft_v49_pre_repair_data'] as $meta){$b=(string)get_post_meta($id,$meta,true);if($b!==''){$data=json_decode($b,true);if(is_array($data)){$source=$meta;return $data;}}}
 foreach(['_ft_v44_backup','_ft_v43_backup','_ft_v42_backup'] as $meta){$b=(string)get_post_meta($id,$meta,true);if($b==='')continue;$obj=json_decode($b,true);$candidate=is_array($obj)?(string)($obj['data']??''):'';if($candidate!==''){$data=json_decode($candidate,true);if(is_array($data)){$source=$meta;return $data;}}}
 return null;
}

function ft_v612_copy_map(){return [
 'INDEPENDENT DESIGNER. CONNECTED TEAM.'=>'INDEPENDENT SOLUTION PROVIDER. CONNECTED TEAM.',
 'INDEPENDENT SOLUTION PROVIDER. CONNECTED TEAM.'=>'INDEPENDENT SOLUTION PROVIDER. CONNECTED TEAM.',
 'Good design.'=>'Think clearly.','Think clearly.'=>'Think clearly.',
 'Solid code.'=>'Design the right solution.','Design solutions.'=>'Design the right solution.','Design the right solution.'=>'Design the right solution.',
 'Real impact.'=>'Build what matters.','Build what matters.'=>'Build what matters.',
 'I’m Muhammad Faraz. I design intuitive digital experiences and bring them to life with a dedicated development team.'=>'I’m Muhammad Faraz — a digital solution provider. I use critical thinking, product strategy, UI/UX, development and AI to turn complex business problems into clear, practical solutions.',
 'I’m Muhammad Faraz. I combine critical thinking, product strategy, UI/UX, development and AI to turn business problems into practical digital solutions.'=>'I’m Muhammad Faraz — a digital solution provider. I use critical thinking, product strategy, UI/UX, development and AI to turn complex business problems into clear, practical solutions.',
 'STRATEGY × DESIGN × DEVELOPMENT'=>'STRATEGY × DESIGN × DEVELOPMENT × AI','STRATEGY × DESIGN × DEVELOPMENT × AI'=>'STRATEGY × DESIGN × DEVELOPMENT × AI',
 'Thoughtfully designed.'=>'Thoughtfully solved.','Thoughtfully solved.'=>'Thoughtfully solved.',
 'From idea to experience.'=>'From problem to practical solution.','From problem to solution.'=>'From problem to practical solution.','From problem to practical solution.'=>'From problem to practical solution.',
 'Design thinking meets practical development. Choose the support your product needs.'=>'Use the capability the problem needs—strategy, UI/UX, software, AI or automation—without forcing every project into the same technical stack.',
 'Strategy, design, development and AI—shaped around the outcome your product actually needs.'=>'Use the capability the problem needs—strategy, UI/UX, software, AI or automation—without forcing every project into the same technical stack.',
 'THE PERSON BEHIND THE WORK'=>'THE PERSON BEHIND THE SOLUTION','THE PERSON BEHIND THE SOLUTIONS'=>'THE PERSON BEHIND THE SOLUTION',
 'I connect design'=>'I start with the problem.','I think critically.'=>'I start with the problem.',
 'with development.'=>'Then design the solution.','I design solutions.'=>'Then design the solution.',
 'UI/UX designer, founder of Fraztech and your point of contact from the first conversation to the final handoff.'=>'Digital solution provider, UI/UX designer and product partner—from problem framing and solution design to delivery and launch.',
 'Independent digital solution provider, UI/UX designer and your point of contact from problem framing to final delivery.'=>'Digital solution provider, UI/UX designer and product partner—from problem framing and solution design to delivery and launch.',
 'Muhammad Faraz · UI/UX designer &amp; product partner'=>'Muhammad Faraz · Digital solution provider &amp; product partner',
 'Muhammad Faraz · Digital solution provider &amp; product partner'=>'Muhammad Faraz · Digital solution provider &amp; product partner',
 'Thoughtful interfaces.'=>'Critical thinking.','Critical thinking.'=>'Critical thinking.',
 'Shared ambition.'=>'Practical solutions.','Practical solutions.'=>'Practical solutions.',
 'I work with businesses and founders to turn complex ideas into digital experiences that feel clear and easy to use.'=>'In an AI-first world, clients do not just need someone who can code. They need a solution partner who can understand the real problem, question assumptions and decide what should be built.',
 'I work with founders and businesses to understand the real problem before choosing a design, technology or AI feature.'=>'In an AI-first world, clients do not just need someone who can code. They need a solution partner who can understand the real problem, question assumptions and decide what should be built.',
 'My focus is UI/UX design. When your project needs a full build, I coordinate with a development team so the design and implementation move in the same direction.'=>'I combine critical thinking, product strategy, UI/UX, development and AI to design the right solution before execution starts.',
 'My role goes beyond screens or code. I connect product thinking, UI/UX, development and AI so each decision supports a clear business and user outcome.'=>'I combine critical thinking, product strategy, UI/UX, development and AI to design the right solution before execution starts.',
 'From focused redesigns to web and mobile products, the work starts with understanding what your users need and what your business is trying to achieve.'=>'When specialist execution is needed, I coordinate my development and AI team while staying responsible for direction, quality and the final outcome.',
 'When specialist execution is needed, I coordinate with my development and AI team while staying responsible for the direction, quality and final solution.'=>'When specialist execution is needed, I coordinate my development and AI team while staying responsible for direction, quality and the final outcome.',
 'A team behind the vision'=>'A solution partner with a connected team','A solution partner with a connected team'=>'A solution partner with a connected team',
 'Today, I work with my team to deliver design and development projects, giving clients a collaborative partner throughout their product journey.'=>'Today, I work as a solution partner—framing the problem, shaping the approach and coordinating the right specialists from design through launch.',
 'Today, I work as the client’s solution partner—framing problems, designing the right approach and coordinating design, development and AI specialists to deliver the product.'=>'Today, I work as a solution partner—framing the problem, shaping the approach and coordinating the right specialists from design through launch.',
 'Team-led delivery'=>'Solution-led delivery','Solution-led delivery'=>'Solution-led delivery',
 'Build the right thing.'=>'Solve the right problem.','Design the right solution.'=>'Solve the right problem.','Solve the right problem.'=>'Solve the right problem.',
 'A practical combination of strategy, design and development for your next stage.'=>'In an AI-first world, the advantage is not simply writing more code. It is understanding the problem, challenging assumptions and choosing the right combination of product thinking, design, software, AI and automation.',
 'In an AI-first world, the advantage is not just code. It is critical thinking, choosing the right approach and building a solution that creates a useful outcome.'=>'In an AI-first world, the advantage is not simply writing more code. It is understanding the problem, challenging assumptions and choosing the right combination of product thinking, design, software, AI and automation.',
 'DESIGN · DEVELOPMENT · AI'=>'STRATEGY · DESIGN · SOFTWARE · AI','STRATEGY · DESIGN · DEVELOPMENT · AI'=>'STRATEGY · DESIGN · SOFTWARE · AI',
 'One team.'=>'Not just development.','Not just development.'=>'Not just development.',
 'A wider set of possibilities.'=>'The right solution for the outcome.','The right solution.'=>'The right solution for the outcome.','The right solution for the outcome.'=>'The right solution for the outcome.',
 'UI/UX, AI solutions, mobile apps, websites, WordPress, WooCommerce, Shopify and the systems behind your business.'=>'From problem framing and solution design to UI/UX, development, AI and automation—bring in only the capabilities that create a useful result.',
 'From problem framing and product strategy to UI/UX, development, AI and automation—use the capabilities the outcome actually needs.'=>'From problem framing and solution design to UI/UX, development, AI and automation—bring in only the capabilities that create a useful result.'
];}

function ft_v612_apply_map_to_data(&$data,$map){$changed=false;
 foreach($data as &$widget){if(!is_array($widget))continue;$fields=&$widget['settings']['fields'];if(isset($fields)&&is_array($fields))foreach($fields as &$f){if(!is_array($f)||!isset($f['text'])||!is_string($f['text']))continue;$v=$f['text'];if(isset($map[$v])&&$map[$v]!==$v){$f['text']=$map[$v];$changed=true;}}
  if(!empty($widget['elements'])&&is_array($widget['elements']))if(ft_v612_apply_map_to_data($widget['elements'],$map))$changed=true;
 }
 return $changed;
}

function ft_v612_repair_and_position(){
 if((string)get_option('ft_v612_repair_done','')==='1')return;
 $all_ok=true;$map=ft_v612_copy_map();
 foreach(ft_v612_target_pages() as $id){
   $source='';$raw=(string)get_post_meta($id,'_elementor_data',true);$layout=(string)get_post_meta($id,'_ft_layout',true);
   if(!metadata_exists('post',$id,'_ft_v612_pre_repair_elementor_data'))update_post_meta($id,'_ft_v612_pre_repair_elementor_data',$raw);
   if(!metadata_exists('post',$id,'_ft_v612_pre_repair_layout'))update_post_meta($id,'_ft_v612_pre_repair_layout',$layout);
   $data=ft_v612_decode_current_elementor($id,$source);
   if(!is_array($data)||!$data){$all_ok=false;continue;}
   ft_v612_apply_map_to_data($data,$map);
   update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));
   update_post_meta($id,'_elementor_edit_mode','builder');
   update_post_meta($id,'_elementor_template_type',get_post_type($id)==='post'?'wp-post':'wp-page');
   if(function_exists('ft_v44_reconstruct_data')){$rebuilt=ft_v44_reconstruct_data($data);if(trim($rebuilt)!=='')update_post_meta($id,'_ft_layout',wp_slash($rebuilt));}
   update_post_meta($id,'_ft_v612_repair_source',$source);clean_post_cache($id);
 }
 if($all_ok){update_option('ft_v612_repair_done','1',false);delete_option('ft_v611_solution_positioning');}
 if(class_exists('\Elementor\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}}
 do_action('litespeed_purge_all','Fraztech Core 6.1.2 repaired Elementor data and positioning');
 if(function_exists('wp_cache_flush'))wp_cache_flush();
}
add_action('admin_init','ft_v612_repair_and_position',2);
add_action('init','ft_v612_repair_and_position',40);

function ft_v612_translation_seed(){return [
 'INDEPENDENT SOLUTION PROVIDER. CONNECTED TEAM.'=>['de'=>'UNABHÄNGIGER LÖSUNGSPARTNER. VERNETZTES TEAM.','ar'=>'مزود حلول مستقل. فريق مترابط.','es'=>'PROVEEDOR INDEPENDIENTE DE SOLUCIONES. EQUIPO CONECTADO.','nl'=>'ONAFHANKELIJKE OPLOSSINGSPARTNER. VERBONDEN TEAM.','fr'=>'FOURNISSEUR DE SOLUTIONS INDÉPENDANT. ÉQUIPE CONNECTÉE.','ur'=>'آزاد سلوشن پرووائیڈر۔ منسلک ٹیم۔'],
 'Think clearly.'=>['de'=>'Klar denken.','ar'=>'فكّر بوضوح.','es'=>'Piensa con claridad.','nl'=>'Denk helder.','fr'=>'Penser clairement.','ur'=>'واضح سوچیں۔'],
 'Design the right solution.'=>['de'=>'Die richtige Lösung gestalten.','ar'=>'صمّم الحل الصحيح.','es'=>'Diseña la solución adecuada.','nl'=>'Ontwerp de juiste oplossing.','fr'=>'Concevoir la bonne solution.','ur'=>'صحیح حل ڈیزائن کریں۔'],
 'Build what matters.'=>['de'=>'Bauen, was zählt.','ar'=>'ابنِ ما يهم.','es'=>'Construye lo que importa.','nl'=>'Bouw wat ertoe doet.','fr'=>'Construire ce qui compte.','ur'=>'وہ بنائیں جو واقعی اہم ہو۔'],
 'I’m Muhammad Faraz — a digital solution provider. I use critical thinking, product strategy, UI/UX, development and AI to turn complex business problems into clear, practical solutions.'=>['de'=>'Ich bin Muhammad Faraz – ein Anbieter digitaler Lösungen. Mit kritischem Denken, Produktstrategie, UI/UX, Entwicklung und KI verwandle ich komplexe Geschäftsprobleme in klare, praktische Lösungen.','ar'=>'أنا محمد فراز، مزود حلول رقمية. أستخدم التفكير النقدي واستراتيجية المنتج وUI/UX والتطوير والذكاء الاصطناعي لتحويل مشكلات الأعمال المعقدة إلى حلول واضحة وعملية.','es'=>'Soy Muhammad Faraz, proveedor de soluciones digitales. Uso pensamiento crítico, estrategia de producto, UI/UX, desarrollo e IA para convertir problemas empresariales complejos en soluciones claras y prácticas.','nl'=>'Ik ben Muhammad Faraz, een digitale oplossingspartner. Ik combineer kritisch denken, productstrategie, UI/UX, development en AI om complexe bedrijfsproblemen om te zetten in heldere, praktische oplossingen.','fr'=>'Je suis Muhammad Faraz, fournisseur de solutions numériques. J’utilise la réflexion critique, la stratégie produit, l’UI/UX, le développement et l’IA pour transformer des problèmes complexes en solutions claires et concrètes.','ur'=>'میں محمد فراز ہوں، ایک ڈیجیٹل سلوشن پرووائیڈر۔ میں تنقیدی سوچ، پروڈکٹ اسٹریٹیجی، UI/UX، ڈیولپمنٹ اور AI کے ذریعے پیچیدہ کاروباری مسائل کو واضح اور عملی حل میں تبدیل کرتا ہوں۔'],
 'THE PERSON BEHIND THE SOLUTION'=>['de'=>'DIE PERSON HINTER DER LÖSUNG','ar'=>'الشخص وراء الحل','es'=>'LA PERSONA DETRÁS DE LA SOLUCIÓN','nl'=>'DE PERSOON ACHTER DE OPLOSSING','fr'=>'LA PERSONNE DERRIÈRE LA SOLUTION','ur'=>'حل کے پیچھے موجود شخص'],
 'I start with the problem.'=>['de'=>'Ich beginne mit dem Problem.','ar'=>'أبدأ بالمشكلة.','es'=>'Empiezo por el problema.','nl'=>'Ik begin bij het probleem.','fr'=>'Je commence par le problème.','ur'=>'میں مسئلے سے آغاز کرتا ہوں۔'],
 'Then design the solution.'=>['de'=>'Dann gestalte ich die Lösung.','ar'=>'ثم أصمم الحل.','es'=>'Luego diseño la solución.','nl'=>'Daarna ontwerp ik de oplossing.','fr'=>'Puis je conçois la solution.','ur'=>'پھر حل ڈیزائن کرتا ہوں۔'],
 'Digital solution provider, UI/UX designer and product partner—from problem framing and solution design to delivery and launch.'=>['de'=>'Digitaler Lösungspartner, UI/UX-Designer und Produktpartner – von der Problemdefinition und Lösungsplanung bis zur Umsetzung und zum Launch.','ar'=>'مزود حلول رقمية ومصمم UI/UX وشريك منتج، من صياغة المشكلة وتصميم الحل إلى التنفيذ والإطلاق.','es'=>'Proveedor de soluciones digitales, diseñador UI/UX y socio de producto, desde definir el problema y diseñar la solución hasta la entrega y el lanzamiento.','nl'=>'Digitale oplossingspartner, UI/UX-designer en productpartner, van probleemdefinitie en oplossingsontwerp tot oplevering en lancering.','fr'=>'Fournisseur de solutions numériques, designer UI/UX et partenaire produit, du cadrage du problème et de la conception de la solution jusqu’à la livraison et au lancement.','ur'=>'ڈیجیٹل سلوشن پرووائیڈر، UI/UX ڈیزائنر اور پروڈکٹ پارٹنر—مسئلے کی وضاحت اور حل کے ڈیزائن سے ڈیلیوری اور لانچ تک۔'],
 'In an AI-first world, clients do not just need someone who can code. They need a solution partner who can understand the real problem, question assumptions and decide what should be built.'=>['de'=>'In einer KI-geprägten Welt brauchen Kunden nicht nur jemanden, der Code schreibt. Sie brauchen einen Lösungspartner, der das eigentliche Problem versteht, Annahmen hinterfragt und entscheidet, was gebaut werden sollte.','ar'=>'في عالم تقوده تقنيات الذكاء الاصطناعي، لا يحتاج العملاء فقط إلى من يكتب الكود، بل إلى شريك حلول يفهم المشكلة الحقيقية ويختبر الافتراضات ويحدد ما الذي يجب بناؤه.','es'=>'En un mundo impulsado por la IA, los clientes no solo necesitan a alguien que programe. Necesitan un socio de soluciones que entienda el problema real, cuestione supuestos y decida qué merece la pena construir.','nl'=>'In een AI-first wereld hebben klanten niet alleen iemand nodig die code schrijft. Ze hebben een oplossingspartner nodig die het echte probleem begrijpt, aannames bevraagt en bepaalt wat gebouwd moet worden.','fr'=>'Dans un monde dominé par l’IA, les clients n’ont pas seulement besoin de quelqu’un qui code. Ils ont besoin d’un partenaire capable de comprendre le vrai problème, de remettre les hypothèses en question et de décider quoi construire.','ur'=>'AI کے دور میں کلائنٹس کو صرف کوڈ لکھنے والا شخص نہیں چاہیے۔ انہیں ایسا سلوشن پارٹنر چاہیے جو اصل مسئلہ سمجھے، مفروضوں پر سوال اٹھائے اور طے کرے کہ حقیقت میں کیا بنانا چاہیے۔'],
 'I combine critical thinking, product strategy, UI/UX, development and AI to design the right solution before execution starts.'=>['de'=>'Ich verbinde kritisches Denken, Produktstrategie, UI/UX, Entwicklung und KI, um vor der Umsetzung die richtige Lösung zu gestalten.','ar'=>'أجمع بين التفكير النقدي واستراتيجية المنتج وUI/UX والتطوير والذكاء الاصطناعي لتصميم الحل الصحيح قبل بدء التنفيذ.','es'=>'Combino pensamiento crítico, estrategia de producto, UI/UX, desarrollo e IA para diseñar la solución adecuada antes de empezar la ejecución.','nl'=>'Ik combineer kritisch denken, productstrategie, UI/UX, development en AI om de juiste oplossing te ontwerpen voordat de uitvoering begint.','fr'=>'Je combine réflexion critique, stratégie produit, UI/UX, développement et IA pour concevoir la bonne solution avant de commencer l’exécution.','ur'=>'میں تنقیدی سوچ، پروڈکٹ اسٹریٹیجی، UI/UX، ڈیولپمنٹ اور AI کو ملا کر عملدرآمد شروع ہونے سے پہلے صحیح حل ڈیزائن کرتا ہوں۔'],
 'When specialist execution is needed, I coordinate my development and AI team while staying responsible for direction, quality and the final outcome.'=>['de'=>'Wenn spezialisierte Umsetzung nötig ist, koordiniere ich mein Entwicklungs- und KI-Team und bleibe für Richtung, Qualität und Ergebnis verantwortlich.','ar'=>'عندما يتطلب المشروع تنفيذًا متخصصًا، أنسق فريق التطوير والذكاء الاصطناعي مع بقائي مسؤولًا عن الاتجاه والجودة والنتيجة النهائية.','es'=>'Cuando se necesita ejecución especializada, coordino a mi equipo de desarrollo e IA y sigo siendo responsable de la dirección, la calidad y el resultado final.','nl'=>'Wanneer specialistische uitvoering nodig is, coördineer ik mijn development- en AI-team en blijf ik verantwoordelijk voor richting, kwaliteit en eindresultaat.','fr'=>'Lorsqu’une exécution spécialisée est nécessaire, je coordonne mon équipe de développement et d’IA tout en restant responsable de la direction, de la qualité et du résultat final.','ur'=>'جب خاص مہارت والی ڈیلیوری درکار ہو تو میں اپنی ڈیولپمنٹ اور AI ٹیم کو کوآرڈینیٹ کرتا ہوں اور سمت، معیار اور حتمی نتیجے کی ذمہ داری خود رکھتا ہوں۔'],
 'Solve the right problem.'=>['de'=>'Das richtige Problem lösen.','ar'=>'حل المشكلة الصحيحة.','es'=>'Resuelve el problema correcto.','nl'=>'Los het juiste probleem op.','fr'=>'Résoudre le bon problème.','ur'=>'صحیح مسئلہ حل کریں۔'],
 'In an AI-first world, the advantage is not simply writing more code. It is understanding the problem, challenging assumptions and choosing the right combination of product thinking, design, software, AI and automation.'=>['de'=>'In einer KI-geprägten Welt liegt der Vorteil nicht darin, einfach mehr Code zu schreiben. Entscheidend sind Problemverständnis, das Hinterfragen von Annahmen und die richtige Kombination aus Produktdenken, Design, Software, KI und Automatisierung.','ar'=>'في عالم تقوده تقنيات الذكاء الاصطناعي، لا تكمن الميزة في كتابة المزيد من الكود، بل في فهم المشكلة وتحدي الافتراضات واختيار المزيج المناسب من التفكير بالمنتج والتصميم والبرمجيات والذكاء الاصطناعي والأتمتة.','es'=>'En un mundo AI-first, la ventaja no está simplemente en escribir más código, sino en entender el problema, cuestionar supuestos y elegir la combinación adecuada de producto, diseño, software, IA y automatización.','nl'=>'In een AI-first wereld zit het voordeel niet simpelweg in meer code schrijven, maar in het probleem begrijpen, aannames uitdagen en de juiste mix kiezen van productdenken, design, software, AI en automatisering.','fr'=>'Dans un monde AI-first, l’avantage ne consiste pas simplement à écrire plus de code, mais à comprendre le problème, remettre les hypothèses en question et choisir la bonne combinaison de réflexion produit, design, logiciel, IA et automatisation.','ur'=>'AI کے دور میں فائدہ صرف زیادہ کوڈ لکھنے میں نہیں، بلکہ مسئلہ سمجھنے، مفروضوں کو چیلنج کرنے اور پروڈکٹ تھنکنگ، ڈیزائن، سافٹ ویئر، AI اور آٹومیشن کا صحیح امتزاج منتخب کرنے میں ہے۔']
];}

function ft_v612_seed_translations(){if((string)get_option('ft_v612_translation_seed','')==='1')return;if(!function_exists('ft_v54_install_translation_table')||!function_exists('ft_v54_translation_table'))return;ft_v54_install_translation_table();global $wpdb;$table=ft_v54_translation_table();foreach(ft_v612_translation_seed() as $src=>$langs)foreach($langs as $target=>$tr){$key=hash('sha256','en|'.$target.'|'.$src);$wpdb->replace($table,['cache_key'=>$key,'target'=>$target,'source_text'=>$src,'translation'=>$tr,'updated_at'=>current_time('mysql')],['%s','%s','%s','%s','%s']);}update_option('ft_v612_translation_seed','1',false);}
add_action('admin_init','ft_v612_seed_translations',36);add_action('init','ft_v612_seed_translations',45);
