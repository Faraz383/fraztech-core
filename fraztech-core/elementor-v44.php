<?php
if (!defined('ABSPATH')) exit;

/*
 * Fraztech Elementor V4.6 — DOM-preserving section widgets with content + design controls.
 *
 * Architecture:
 * - One Elementor widget owns one ORIGINAL top-level section/figure/aside/div.
 * - Elementor keeps its own neutral widget wrapper; the complete original top-level element is rendered INSIDE it.
 * - This prevents Elementor editor CSS from changing the original element's display:grid/flex/block behavior.
 * - The original element and every child inside it keep their approved DOM relationships unchanged.
 * - Text, links and images are exposed as repeater controls; raw HTML remains protected.
 * - Preflight requires a semantic round-trip DOM signature match before migration is unlocked.
 */

function ft_v44_uid($seed=''){
 static $i=0; $i++;
 return substr(md5($seed.'|'.$i.'|'.wp_rand()),0,8);
}
function ft_v44_safe_tag($tag){
 $tag=strtolower((string)$tag);
 $allowed=['section','div','figure','aside','article','main','header','footer','nav'];
 return in_array($tag,$allowed,true)?$tag:'div';
}
function ft_v44_outer_attrs(DOMElement $el){
 $out=[];
 foreach($el->attributes as $a){
  $name=strtolower($a->name);
  if($name==='class'||str_starts_with($name,'on')) continue;
  $out[]=['name'=>$a->name,'value'=>$a->value];
 }
 return $out;
}
function ft_v44_apply_outer_attr($widget,$name,$value){
 $name=strtolower((string)$name);
 if($name===''||$name==='class'||str_starts_with($name,'on')) return;
 if(in_array($name,['data-id','data-element_type','data-e-type','data-widget_type'],true)) return;
 if($name==='style'){
  $value=safecss_filter_attr((string)$value);
  if($value==='') return;
 }
 if(in_array($name,['href','src','action'],true)) $value=esc_url((string)$value);
 $widget->add_render_attribute('_wrapper',$name,(string)$value);
}

add_action('elementor/widgets/register',function($widgets){
 if(!class_exists('\Elementor\Widget_Base')) return;
 if(!class_exists('FT_V44_Section')){
  class FT_V44_Section extends \Elementor\Widget_Base{
   public function get_name(){return 'ft-v44-section';}
   public function get_title(){return 'Fraztech Section';}
   public function get_icon(){return 'eicon-container';}
   public function get_categories(){return ['basic'];}
   public function get_keywords(){return ['fraztech','section','original','editable'];}
   public function has_widget_inner_wrapper(): bool {return false;}

   protected function register_controls(){
    $this->start_controls_section('ft_content',['label'=>'Editable Content']);
    $header_id=(int)get_option('ft_v45_global_header_id');
    $footer_id=(int)get_option('ft_v45_global_footer_id');
    $quick='Images are listed first for quick access. Click an Image row to replace it from the WordPress Media Library. Links and text follow below. The original layout markup is protected so the approved design stays intact.';
    if($header_id||$footer_id){
     $quick.='<br><br><strong>Global site parts:</strong> ';
     if($header_id)$quick.='<a href="'.esc_url(admin_url('post.php?post='.$header_id.'&action=elementor')).'" target="_blank" rel="noopener">Edit Header ↗</a> ';
     if($footer_id)$quick.='<a href="'.esc_url(admin_url('post.php?post='.$footer_id.'&action=elementor')).'" target="_blank" rel="noopener">Edit Footer ↗</a>';
    }
    $this->add_control('section_label',[
     'label'=>'Section',
     'type'=>\Elementor\Controls_Manager::RAW_HTML,
     'raw'=>$quick,
     'content_classes'=>'elementor-panel-alert elementor-panel-alert-info',
    ]);
    $rep=new \Elementor\Repeater();
    $rep->add_control('key',['type'=>\Elementor\Controls_Manager::HIDDEN]);
    $rep->add_control('kind',['type'=>\Elementor\Controls_Manager::HIDDEN]);
    $rep->add_control('label',['type'=>\Elementor\Controls_Manager::HIDDEN]);
    $rep->add_control('text',[
     'label'=>'Text',
     'type'=>\Elementor\Controls_Manager::TEXTAREA,
     'rows'=>3,
     'condition'=>['kind'=>['text','attr']],
    ]);
    $rep->add_control('url',[
     'label'=>'Link',
     'type'=>\Elementor\Controls_Manager::URL,
     'dynamic'=>['active'=>true],
     'condition'=>['kind'=>'link'],
    ]);
    $rep->add_control('image',[
     'label'=>'Choose / Replace Image',
     'type'=>\Elementor\Controls_Manager::MEDIA,
     'media_types'=>['image'],
     'description'=>'Choose an image from the WordPress Media Library or upload a new one.',
     'dynamic'=>['active'=>true],
     'condition'=>['kind'=>'image'],
    ]);
    $rep->add_control('alt',[
     'label'=>'Alt text',
     'type'=>\Elementor\Controls_Manager::TEXT,
     'condition'=>['kind'=>'image'],
    ]);
    // V4.6: per-element design controls. These style the original DOM node directly
    // through Elementor repeater item classes, without inserting layout wrappers.
    $rep->add_control('design_heading',[
     'label'=>'Design / Position',
     'type'=>\Elementor\Controls_Manager::HEADING,
     'separator'=>'before',
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('element_note',[
     'type'=>\Elementor\Controls_Manager::RAW_HTML,
     'raw'=>'These controls style the real element inside the approved Fraztech layout. Use Move X/Y for small position changes; Order works when the parent uses Flex/Grid.',
     'content_classes'=>'elementor-panel-alert elementor-panel-alert-info',
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('text_color',[
     'label'=>'Text Color','type'=>\Elementor\Controls_Manager::COLOR,
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'color: {{VALUE}} !important;'],
     'condition'=>['kind'=>['text','link']],
    ]);
    $rep->add_control('background_color',[
     'label'=>'Background Color','type'=>\Elementor\Controls_Manager::COLOR,
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'background-color: {{VALUE}} !important;'],
     'condition'=>['kind'=>['text','link']],
    ]);
    $rep->add_control('font_size',[
     'label'=>'Font Size','type'=>\Elementor\Controls_Manager::SLIDER,
     'size_units'=>['px','em','rem','vw'],
     'range'=>['px'=>['min'=>6,'max'=>220,'step'=>1],'em'=>['min'=>0.2,'max'=>12,'step'=>0.1],'rem'=>['min'=>0.2,'max'=>12,'step'=>0.1],'vw'=>['min'=>0.2,'max'=>20,'step'=>0.1]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'font-size: {{SIZE}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link']],
    ]);
    $rep->add_control('font_weight',[
     'label'=>'Font Weight','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'',
     'options'=>[''=>'Default','300'=>'300','400'=>'400','500'=>'500','600'=>'600','700'=>'700','800'=>'800','900'=>'900'],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'font-weight: {{VALUE}} !important;'],
     'condition'=>['kind'=>['text','link']],
    ]);
    $rep->add_control('line_height',[
     'label'=>'Line Height','type'=>\Elementor\Controls_Manager::SLIDER,
     'size_units'=>['px','em','rem'],
     'range'=>['px'=>['min'=>6,'max'=>260,'step'=>1],'em'=>['min'=>0.5,'max'=>4,'step'=>0.05],'rem'=>['min'=>0.5,'max'=>8,'step'=>0.05]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'line-height: {{SIZE}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link']],
    ]);
    $rep->add_control('letter_spacing',[
     'label'=>'Letter Spacing','type'=>\Elementor\Controls_Manager::SLIDER,
     'size_units'=>['px','em'],
     'range'=>['px'=>['min'=>-10,'max'=>30,'step'=>0.1],'em'=>['min'=>-0.5,'max'=>2,'step'=>0.01]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'letter-spacing: {{SIZE}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link']],
    ]);
    $rep->add_control('text_align',[
     'label'=>'Text Align','type'=>\Elementor\Controls_Manager::CHOOSE,'default'=>'',
     'options'=>[
      'left'=>['title'=>'Left','icon'=>'eicon-text-align-left'],
      'center'=>['title'=>'Center','icon'=>'eicon-text-align-center'],
      'right'=>['title'=>'Right','icon'=>'eicon-text-align-right'],
     ],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'text-align: {{VALUE}} !important;'],
     'condition'=>['kind'=>['text','link']],
    ]);
    $rep->add_control('display',[
     'label'=>'Display','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'',
     'options'=>[''=>'Default','block'=>'Block','inline-block'=>'Inline Block','inline'=>'Inline','flex'=>'Flex','grid'=>'Grid','none'=>'Hide'],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'display: {{VALUE}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('width',[
     'label'=>'Width','type'=>\Elementor\Controls_Manager::SLIDER,
     'size_units'=>['px','%','vw','em','rem'],
     'range'=>['px'=>['min'=>0,'max'=>1800,'step'=>1],'%'=>['min'=>0,'max'=>100,'step'=>1],'vw'=>['min'=>0,'max'=>100,'step'=>1]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'width: {{SIZE}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('max_width',[
     'label'=>'Max Width','type'=>\Elementor\Controls_Manager::SLIDER,
     'size_units'=>['px','%','vw','em','rem'],
     'range'=>['px'=>['min'=>0,'max'=>1800,'step'=>1],'%'=>['min'=>0,'max'=>100,'step'=>1],'vw'=>['min'=>0,'max'=>100,'step'=>1]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'max-width: {{SIZE}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('height',[
     'label'=>'Height','type'=>\Elementor\Controls_Manager::SLIDER,
     'size_units'=>['px','%','vh','em','rem'],
     'range'=>['px'=>['min'=>0,'max'=>1400,'step'=>1],'%'=>['min'=>0,'max'=>100,'step'=>1],'vh'=>['min'=>0,'max'=>100,'step'=>1]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'height: {{SIZE}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['link','image']],
    ]);
    $rep->add_control('padding',[
     'label'=>'Padding','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('margin',[
     'label'=>'Margin / Spacing','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('border_style',[
     'label'=>'Border Style','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'',
     'options'=>[''=>'Default','none'=>'None','solid'=>'Solid','dashed'=>'Dashed','dotted'=>'Dotted','double'=>'Double'],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'border-style: {{VALUE}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('border_width',[
     'label'=>'Border Width','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px'],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('border_color',[
     'label'=>'Border Color','type'=>\Elementor\Controls_Manager::COLOR,
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'border-color: {{VALUE}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('border_radius',[
     'label'=>'Border Radius','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('opacity',[
     'label'=>'Opacity','type'=>\Elementor\Controls_Manager::SLIDER,
     'range'=>['px'=>['min'=>0,'max'=>1,'step'=>0.05]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'opacity: {{SIZE}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('move_x',[
     'label'=>'Move X','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
     'range'=>['px'=>['min'=>-500,'max'=>500,'step'=>1],'%'=>['min'=>-100,'max'=>100,'step'=>1]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'--ft-v46-move-x: {{SIZE}}{{UNIT}};'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('move_y',[
     'label'=>'Move Y','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
     'range'=>['px'=>['min'=>-500,'max'=>500,'step'=>1],'%'=>['min'=>-100,'max'=>100,'step'=>1]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'--ft-v46-move-y: {{SIZE}}{{UNIT}};'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('scale',[
     'label'=>'Scale','type'=>\Elementor\Controls_Manager::SLIDER,
     'range'=>['px'=>['min'=>0.2,'max'=>3,'step'=>0.01]],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'--ft-v46-scale: {{SIZE}};'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('order',[
     'label'=>'Flex/Grid Order','type'=>\Elementor\Controls_Manager::NUMBER,'min'=>-20,'max'=>20,'step'=>1,
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'order: {{VALUE}} !important;'],
     'condition'=>['kind'=>['text','link','image']],
    ]);
    $rep->add_control('object_fit',[
     'label'=>'Image Fit','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'',
     'options'=>[''=>'Default','cover'=>'Cover','contain'=>'Contain','fill'=>'Fill','none'=>'None','scale-down'=>'Scale Down'],
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}'=>'object-fit: {{VALUE}} !important;'],
     'condition'=>['kind'=>'image'],
    ]);
    $rep->add_control('hover_heading',[
     'label'=>'Hover','type'=>\Elementor\Controls_Manager::HEADING,'separator'=>'before',
     'condition'=>['kind'=>'link'],
    ]);
    $rep->add_control('hover_text_color',[
     'label'=>'Hover Text Color','type'=>\Elementor\Controls_Manager::COLOR,
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}:hover'=>'color: {{VALUE}} !important;'],
     'condition'=>['kind'=>'link'],
    ]);
    $rep->add_control('hover_background_color',[
     'label'=>'Hover Background','type'=>\Elementor\Controls_Manager::COLOR,
     'selectors'=>['{{WRAPPER}} {{CURRENT_ITEM}}:hover'=>'background-color: {{VALUE}} !important;'],
     'condition'=>['kind'=>'link'],
    ]);
    $this->add_control('fields',[
     'label'=>'Section content',
     'type'=>\Elementor\Controls_Manager::REPEATER,
     'fields'=>$rep->get_controls(),
     'title_field'=>'{{{ label }}}',
     'prevent_empty'=>false,
    ]);
    // Protected source data: registered controls so Elementor preserves them, but never shown as raw HTML to the user.
    $this->add_control('outer_tag',['type'=>\Elementor\Controls_Manager::HIDDEN]);
    $this->add_control('outer_classes',['type'=>\Elementor\Controls_Manager::HIDDEN]);
    $this->add_control('outer_attrs',['type'=>\Elementor\Controls_Manager::HIDDEN]);
    $this->add_control('template_b64',['type'=>\Elementor\Controls_Manager::HIDDEN]);
    $this->end_controls_section();

    // V4.6 section-level layout controls target the original root element, not Elementor's shell.
    $this->start_controls_section('ft_section_style',[
     'label'=>'Section Layout & Style',
     'tab'=>\Elementor\Controls_Manager::TAB_STYLE,
    ]);
    $this->add_control('section_background',[
     'label'=>'Background','type'=>\Elementor\Controls_Manager::COLOR,
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'background: {{VALUE}} !important;'],
    ]);
    $this->add_responsive_control('section_width',[
     'label'=>'Width','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','vw'],
     'range'=>['px'=>['min'=>100,'max'=>2000,'step'=>1],'%'=>['min'=>10,'max'=>100,'step'=>1],'vw'=>['min'=>10,'max'=>100,'step'=>1]],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'width: {{SIZE}}{{UNIT}} !important;'],
    ]);
    $this->add_responsive_control('section_max_width',[
     'label'=>'Max Width','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','vw'],
     'range'=>['px'=>['min'=>100,'max'=>2400,'step'=>1],'%'=>['min'=>10,'max'=>100,'step'=>1],'vw'=>['min'=>10,'max'=>100,'step'=>1]],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'max-width: {{SIZE}}{{UNIT}} !important;'],
    ]);
    $this->add_responsive_control('section_min_height',[
     'label'=>'Min Height','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','vh'],
     'range'=>['px'=>['min'=>0,'max'=>1600,'step'=>1],'vh'=>['min'=>0,'max'=>100,'step'=>1]],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'min-height: {{SIZE}}{{UNIT}} !important;'],
    ]);
    $this->add_responsive_control('section_padding',[
     'label'=>'Padding','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
    ]);
    $this->add_responsive_control('section_margin',[
     'label'=>'Margin / Spacing','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
    ]);
    $this->add_responsive_control('section_gap',[
     'label'=>'Inner Gap','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','em','rem','%'],
     'range'=>['px'=>['min'=>0,'max'=>200,'step'=>1],'em'=>['min'=>0,'max'=>10,'step'=>0.1],'rem'=>['min'=>0,'max'=>10,'step'=>0.1]],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'gap: {{SIZE}}{{UNIT}} !important;'],
    ]);
    $this->add_control('section_border_style',[
     'label'=>'Border Style','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'',
     'options'=>[''=>'Default','none'=>'None','solid'=>'Solid','dashed'=>'Dashed','dotted'=>'Dotted','double'=>'Double'],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'border-style: {{VALUE}} !important;'],
    ]);
    $this->add_control('section_border_color',[
     'label'=>'Border Color','type'=>\Elementor\Controls_Manager::COLOR,
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'border-color: {{VALUE}} !important;'],
    ]);
    $this->add_responsive_control('section_border_width',[
     'label'=>'Border Width','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px'],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
    ]);
    $this->add_responsive_control('section_border_radius',[
     'label'=>'Border Radius','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
    ]);
    $this->add_responsive_control('section_move_x',[
     'label'=>'Move Section X','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%'],
     'range'=>['px'=>['min'=>-500,'max'=>500,'step'=>1],'%'=>['min'=>-50,'max'=>50,'step'=>1]],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'--ft-v46-section-x: {{SIZE}}{{UNIT}};'],
    ]);
    $this->add_responsive_control('section_move_y',[
     'label'=>'Move Section Y','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%'],
     'range'=>['px'=>['min'=>-500,'max'=>500,'step'=>1],'%'=>['min'=>-50,'max'=>50,'step'=>1]],
     'selectors'=>['{{WRAPPER}} > .ft-v46-root'=>'--ft-v46-section-y: {{SIZE}}{{UNIT}};'],
    ]);
    $this->end_controls_section();
   }

   public function get_style_depends(): array { return [ 'ft-v44-widget' ]; }
   protected function render(){
    $s=$this->get_settings_for_display();
    echo ft_v44_render_original_element($s); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
   }
   public function render_plain_content(){
    $s=$this->get_settings_for_display();
    echo wp_strip_all_tags(ft_v44_render_inner_from_settings($s));
   }
  }
 }
 if(!class_exists('FT_V44_Compat_Section')){
  class FT_V44_Compat_Section extends FT_V44_Section{
   public function get_name(){return 'ft-v43-section';}
   public function get_title(){return 'Fraztech Section (V4.3 compatibility)';}
  }
 }
 $widgets->register(new FT_V44_Section());
 $widgets->register(new FT_V44_Compat_Section());
});

function ft_v44_field_id($prefix,$i){return substr(md5($prefix.'|'.$i),0,8);}
function ft_v44_text_label($text,$fallback='Text'){
 $text=trim(preg_replace('/\\s+/u',' ',wp_strip_all_tags((string)$text)));
 if($text==='') return $fallback;
 if(function_exists('mb_strlen')&&mb_strlen($text)>68) $text=mb_substr($text,0,65).'…';
 elseif(strlen($text)>68) $text=substr($text,0,65).'…';
 return $text;
}
function ft_v44_url_setting($url,$target='',$rel=''){
 return ['url'=>(string)$url,'is_external'=>$target==='_blank','nofollow'=>str_contains(strtolower((string)$rel),'nofollow'),'custom_attributes'=>''];
}
function ft_v44_walk_editables(DOMNode $node,&$fields,&$counter,$prefix){
 // Never rewrite executable/style text.
 if($node->nodeType===XML_TEXT_NODE){
  $parent=$node->parentNode instanceof DOMElement?strtolower($node->parentNode->tagName):'';
  if(in_array($parent,['script','style'],true)) return;
  $raw=$node->nodeValue;
  if(trim($raw)==='') return;
  preg_match('/^\\s*/u',$raw,$pm); preg_match('/\\s*$/u',$raw,$sm);
  $pre=$pm[0]??''; $suf=$sm[0]??'';
  $core=substr($raw,strlen($pre),strlen($raw)-strlen($pre)-strlen($suf));
  if($core==='') return;
  $counter++; $key='t'.ft_v44_field_id($prefix,$counter); $token='__FTV44_TEXT_'.$key.'__';
  $node->nodeValue=$pre.$token.$suf;
  $fields[]=['_id'=>ft_v44_field_id('row-'.$prefix,$counter),'key'=>$key,'kind'=>'text','label'=>ft_v44_text_label($core),'text'=>$core];
  return;
 }
 if(!($node instanceof DOMElement)) return;
 $tag=strtolower($node->tagName);

 if($tag==='img'){
  $counter++; $key='i'.ft_v44_field_id($prefix,$counter);
  $src=$node->getAttribute('src'); $alt=$node->getAttribute('alt');
  $node->setAttribute('src','__FTV44_IMG_'.$key.'__');
  $node->setAttribute('alt','__FTV44_ALT_'.$key.'__');
  $fields[]=['_id'=>ft_v44_field_id('row-'.$prefix,$counter),'key'=>$key,'kind'=>'image','label'=>'Image: '.ft_v44_text_label($alt?:basename(parse_url($src,PHP_URL_PATH)?:'image'),'Image'),'image'=>['url'=>$src,'id'=>0],'alt'=>$alt];
 }
 if($tag==='a' && $node->hasAttribute('href')){
  $counter++; $key='l'.ft_v44_field_id($prefix,$counter);
  $href=$node->getAttribute('href'); $target=$node->getAttribute('target'); $rel=$node->getAttribute('rel');
  $node->setAttribute('href','__FTV44_LINK_'.$key.'__');
  $fields[]=['_id'=>ft_v44_field_id('row-'.$prefix,$counter),'key'=>$key,'kind'=>'link','label'=>'Link: '.ft_v44_text_label($node->textContent?:$href,'Link'),'url'=>ft_v44_url_setting($href,$target,$rel)];
 }
 if(in_array($tag,['source','iframe'],true) && $node->hasAttribute('src')){
  $counter++; $key='u'.ft_v44_field_id($prefix,$counter); $url=$node->getAttribute('src');
  $node->setAttribute('src','__FTV44_URL_'.$key.'__');
  $fields[]=['_id'=>ft_v44_field_id('row-'.$prefix,$counter),'key'=>$key,'kind'=>'link','label'=>ucfirst($tag).' source','url'=>ft_v44_url_setting($url)];
 }
 if($tag==='video' && $node->hasAttribute('poster')){
  $counter++; $key='u'.ft_v44_field_id($prefix,$counter); $url=$node->getAttribute('poster');
  $node->setAttribute('poster','__FTV44_URL_'.$key.'__');
  $fields[]=['_id'=>ft_v44_field_id('row-'.$prefix,$counter),'key'=>$key,'kind'=>'link','label'=>'Video poster','url'=>ft_v44_url_setting($url)];
 }
 foreach(['placeholder','title','aria-label'] as $attr){
  if(!$node->hasAttribute($attr)) continue;
  $value=$node->getAttribute($attr); if($value==='') continue;
  $counter++; $key='a'.ft_v44_field_id($prefix,$counter);
  $node->setAttribute($attr,'__FTV44_ATTR_'.$key.'__');
  $fields[]=['_id'=>ft_v44_field_id('row-'.$prefix,$counter),'key'=>$key,'kind'=>'attr','label'=>ucfirst(str_replace('-',' ',$attr)).': '.ft_v44_text_label($value),'text'=>$value];
 }

 // Snapshot child list before recursing.
 $children=[]; foreach($node->childNodes as $c) $children[]=$c;
 foreach($children as $c) ft_v44_walk_editables($c,$fields,$counter,$prefix);
}
function ft_v44_inner_html(DOMElement $el){
 $out=''; foreach($el->childNodes as $c) $out.=$el->ownerDocument->saveHTML($c); return $out;
}
function ft_v44_widget_from_element(DOMElement $el,$post_id,$index){
 $fields=[]; $counter=0; $prefix=$post_id.'-'.$index;
 $tag=strtolower($el->tagName); $classes=$el->getAttribute('class'); $attrs=ft_v44_outer_attrs($el);
 ft_v44_walk_editables($el,$fields,$counter,$prefix);
 $inner=ft_v44_inner_html($el);
 $template_b64=base64_encode($inner);
 $boxes=function_exists('ft_v48_boxes_from_template')?ft_v48_boxes_from_template($template_b64):[];
 $label=trim($classes)!==''?explode(' ',trim($classes))[0]:$tag;
 return [
  'id'=>ft_v44_uid($prefix),
  'elType'=>'widget',
  'widgetType'=>'ft-v44-section',
  'isInner'=>false,
  'settings'=>[
   'outer_tag'=>ft_v44_safe_tag($tag),
   'outer_classes'=>$classes,
   'outer_attrs'=>wp_json_encode($attrs),
   'template_b64'=>$template_b64,
   'fields'=>$fields,
   'boxes'=>$boxes,
   '_title'=>'Fraztech: '.$label,
  ],
  'elements'=>[],
 ];
}
function ft_v44_build_data($source,$post_id=0){
 $resolved=ft_resolve(do_shortcode($source));
 $dom=ft_dom($resolved); $root=$dom->getElementById('ft-root'); $els=[]; $i=0;
 if(!$root) return $els;
 $nodes=[]; foreach($root->childNodes as $n) $nodes[]=$n;
 foreach($nodes as $n){
  if($n instanceof DOMElement){$i++; $els[]=ft_v44_widget_from_element($n,$post_id,$i);}
  elseif($n->nodeType===XML_TEXT_NODE && trim($n->nodeValue)!==''){
   // Preserve rare top-level text in a synthetic div without changing its text.
   $d=$dom->createElement('div'); $d->appendChild($dom->createTextNode($n->nodeValue)); $i++; $els[]=ft_v44_widget_from_element($d,$post_id,$i);
  }
 }
 return $els;
}
function ft_v44_tokens($type,$key){
 // V4.4.1 is deliberately backward-compatible with already-saved V4.2/V4.3 Elementor data.
 // Existing documents keep their original token namespace in template_b64; changing only the
 // PHP renderer must never expose those internal tokens on the public site or in the editor.
 $out=[];
 foreach(['42','43','44'] as $v) $out[]='__FTV'.$v.'_'.$type.'_'.$key.'__';
 return $out;
}
function ft_v46_add_target_class(DOMElement $el,$row_id,$kind){
 $row_id=preg_replace('/[^A-Za-z0-9_-]/','',(string)$row_id);
 if($row_id==='')return;
 $classes=preg_split('/\s+/',trim((string)$el->getAttribute('class')),-1,PREG_SPLIT_NO_EMPTY);
 foreach(['elementor-repeater-item-'.$row_id,'ft-v46-style-target','ft-v46-kind-'.sanitize_html_class((string)$kind)] as $c){if($c&&!in_array($c,$classes,true))$classes[]=$c;}
 $el->setAttribute('class',implode(' ',$classes));
 // Stable metadata used only by the Elementor editor preview. It lets a click on
 // the real DOM node focus the matching repeater row in the settings panel.
 // If several editable fields map to the same DOM node (e.g. button link + its
 // text node, or image + alt text), prefer the most useful canvas selection.
 $new_kind=sanitize_html_class((string)$kind);
 $current_kind=(string)$el->getAttribute('data-ft-v47-kind');
 $priority=['attr'=>1,'text'=>2,'link'=>3,'image'=>4];
 if($current_kind===''||($priority[$new_kind]??0)>($priority[$current_kind]??0)){
  $el->setAttribute('data-ft-v47-row',$row_id);
  $el->setAttribute('data-ft-v47-kind',$new_kind);
 }
}
function ft_v46_mark_style_targets($html,$fields){
 if(trim((string)$html)===''||!is_array($fields)||!class_exists('DOMDocument'))return $html;
 $dom=ft_dom($html);$root=$dom->getElementById('ft-root');if(!$root)return $html;
 foreach($fields as $f){
  if(!is_array($f)||empty($f['_id'])||empty($f['key'])||empty($f['kind']))continue;
  $kind=(string)$f['kind'];if(!in_array($kind,['text','link','image','attr'],true))continue;
  $types=$kind==='text'?['TEXT']:($kind==='image'?['IMG','ALT']:($kind==='link'?['LINK','URL']:['ATTR']));
  $tokens=[];foreach($types as $t)$tokens=array_merge($tokens,ft_v44_tokens($t,(string)$f['key']));
  $target=null;$stack=[];foreach($root->childNodes as $n)$stack[]=$n;
  while($stack&&$target===null){$n=array_shift($stack);
   if($n->nodeType===XML_TEXT_NODE&&$kind==='text'){foreach($tokens as $tok){if(str_contains((string)$n->nodeValue,$tok)){$target=$n->parentNode instanceof DOMElement?$n->parentNode:null;break;}}}
   if($n instanceof DOMElement){
    foreach($n->attributes as $a){foreach($tokens as $tok){if(str_contains((string)$a->value,$tok)){$target=$n;break 2;}}}
    foreach($n->childNodes as $c)$stack[]=$c;
   }
  }
  if($target instanceof DOMElement)ft_v46_add_target_class($target,(string)$f['_id'],$kind);
 }
 return ft_dom_html($dom);
}
function ft_v44_render_inner_from_settings($s){
 $html=base64_decode((string)($s['template_b64']??''),true); if($html===false) $html='';
 $html=ft_v46_mark_style_targets($html,$s['fields']??[]);
 foreach(($s['fields']??[]) as $f){
  if(!is_array($f)||empty($f['key'])||empty($f['kind'])) continue;
  $key=$f['key']; $kind=$f['kind'];
  if($kind==='text'){
   $value=(string)($f['text']??''); if(function_exists('ft_v452_repair_text'))$value=ft_v452_repair_text($value);
   $html=str_replace(ft_v44_tokens('TEXT',$key),esc_html($value),$html);
  } elseif($kind==='attr'){
   $value=(string)($f['text']??''); if(function_exists('ft_v452_repair_text'))$value=ft_v452_repair_text($value);
   $html=str_replace(ft_v44_tokens('ATTR',$key),esc_attr($value),$html);
  } elseif($kind==='image'){
   $url=is_array($f['image']??null)?($f['image']['url']??''):'';
   $alt=(string)($f['alt']??''); if(function_exists('ft_v452_repair_text'))$alt=ft_v452_repair_text($alt);
   $html=str_replace(ft_v44_tokens('IMG',$key),esc_url((string)$url),$html);
   $html=str_replace(ft_v44_tokens('ALT',$key),esc_attr($alt),$html);
  }elseif($kind==='link'){
   $url=is_array($f['url']??null)?($f['url']['url']??''):'';
   $html=str_replace(array_merge(ft_v44_tokens('LINK',$key),ft_v44_tokens('URL',$key)),esc_url((string)$url),$html);
  }
 }
 return $html;
}
function ft_v44_render_original_element($s){
 $tag=ft_v44_safe_tag($s['outer_tag']??'div');
 $attrs=[];
 $classes=trim((string)($s['outer_classes']??''));
 $classes=trim($classes.' ft-v46-root');
 if($classes!=='') $attrs['class']=$classes;
 $raw=json_decode((string)($s['outer_attrs']??'[]'),true);
 if(is_array($raw)) foreach($raw as $a){
  if(!is_array($a)||!isset($a['name'])) continue;
  $name=strtolower((string)$a['name']);
  if($name===''||$name==='class'||str_starts_with($name,'on')) continue;
  $attrs[$name]=(string)($a['value']??'');
 }
 $attr='';
 foreach($attrs as $k=>$v){
  if(in_array($k,['checked','disabled','multiple','required','readonly','selected','autofocus','autoplay','controls','loop','muted','open','playsinline','hidden','inert','novalidate','formnovalidate','allowfullscreen','async','defer','nomodule','reversed','itemscope','ismap','default'],true)){
   $attr.=' '.esc_attr($k);
  } else {
   $attr.=' '.esc_attr($k).'="'.esc_attr($v).'"';
  }
 }
 return '<'.$tag.$attr.'>'.ft_v44_render_inner_from_settings($s).'</'.$tag.'>';
}

function ft_v44_reconstruct_widget($w){
 return ft_v44_render_original_element($w['settings']??[]);
}
function ft_v44_reconstruct_data($data){$out='';foreach($data as $w)$out.=ft_v44_reconstruct_widget($w);return $out;}
function ft_v44_sig_node(DOMNode $n){
 if($n->nodeType===XML_TEXT_NODE) return ['t',$n->nodeValue];
 if(!($n instanceof DOMElement)) return null;
 // HTML boolean attributes are semantic by presence, not by serialized value.
 // DOMDocument may normalize checked="" to checked="checked" (same HTML meaning),
 // so canonicalize these attributes before comparing DOM signatures.
 $boolean=['allowfullscreen','async','autofocus','autoplay','checked','controls','default','defer','disabled','formnovalidate','hidden','inert','ismap','itemscope','loop','multiple','muted','nomodule','novalidate','open','playsinline','readonly','required','reversed','selected'];
 $attrs=[]; foreach($n->attributes as $a){$name=strtolower($a->name);$attrs[$name]=in_array($name,$boolean,true)?'__present__':$a->value;} ksort($attrs);
 $children=[]; foreach($n->childNodes as $c){$s=ft_v44_sig_node($c);if($s!==null)$children[]=$s;}
 return ['e',strtolower($n->tagName),$attrs,$children];
}
function ft_v44_signature($html){
 $dom=ft_dom($html); $root=$dom->getElementById('ft-root'); $out=[]; if(!$root)return $out;
 foreach($root->childNodes as $n){$s=ft_v44_sig_node($n);if($s!==null)$out[]=$s;} return $out;
}
function ft_v44_roundtrip($source,$post_id=0,&$why=''){
 try{
  $resolved=ft_resolve(do_shortcode($source));
  $data=ft_v44_build_data($source,$post_id);
  if(!$data){$why='No Elementor section widgets generated.';return false;}
  $rebuilt=ft_v44_reconstruct_data($data);
  if(ft_v44_signature($resolved)!==ft_v44_signature($rebuilt)){$why='Round-trip DOM signature differs from original.';return false;}
  return true;
 }catch(Throwable $e){$why=$e->getMessage();return false;}
}

function ft_v44_seed_item_for_post($id){
 $key=(string)get_post_meta($id,'_ft_key',true); $type=get_post_type($id); $title=get_the_title($id); $slug=get_post_field('post_name',$id);
 foreach(ft_seed() as $item){if(empty($item['layout']))continue;if($key!==''&&isset($item['key'])&&(string)$item['key']===$key)return $item;}
 foreach(ft_seed() as $item){
  if(empty($item['layout'])||($item['type']??'')!==$type)continue;
  $route=trim((string)($item['route']??''),'/'); $item_slug=basename($route?:'home');
  if(($item['title']??'')===$title&&$item_slug===$slug)return $item;
 }
 return null;
}
function ft_v44_backup_layout($id,$meta){
 $raw=get_post_meta($id,$meta,true); if(!$raw)return '';
 $b=json_decode($raw,true); return is_array($b)&&!empty($b['layout'])?(string)$b['layout']:(is_array($b)&&!empty($b['source_layout'])?(string)$b['source_layout']:'');
}
function ft_v44_source_layout($id,&$origin=''){
 $layout=(string)get_post_meta($id,'_ft_layout',true); if(trim($layout)!==''){$origin='_ft_layout';return $layout;}
 foreach(['_ft_v44_backup','_ft_v43_backup','_ft_v42_backup','_ft_v41_backup','_ft_v4_backup','_ft_v3_backup','_ft_em_backup_v2'] as $meta){$layout=ft_v44_backup_layout($id,$meta);if(trim($layout)!==''){$origin=$meta;return $layout;}}
 $item=ft_v44_seed_item_for_post($id);if($item&&trim((string)$item['layout'])!==''){$origin='seed.json';return (string)$item['layout'];}
 $origin='missing';return '';
}
function ft_v44_expected(){static $n=null;if($n!==null)return $n;$n=0;foreach(ft_seed() as $i)if(!empty($i['layout']))$n++;return $n;}
function ft_v44_ids(){return get_posts(['post_type'=>array_merge(['page','post'],array_keys(ft_types())),'post_status'=>['publish','draft','private'],'numberposts'=>-1,'fields'=>'ids','post__not_in'=>get_option('ft_excluded_posts',[])]);}
function ft_v44_preflight(){
 $expected=ft_v44_expected(); $matched=0;$detected=0;$convertible=0;$faithful=0;$failed=[];$origins=[];$sections=0;$fields=0;
 foreach(ft_v44_ids() as $id){
  $item=ft_v44_seed_item_for_post($id); if(!$item)continue; $matched++;
  $origin='';$src=ft_v44_source_layout($id,$origin); if(trim($src)===''){$failed[$id]='No recoverable source layout.';continue;}
  $detected++;$origins[$origin]=($origins[$origin]??0)+1;
  try{
   $data=ft_v44_build_data($src,$id); if(!$data){$failed[$id]='Generated Elementor data is empty.';continue;}
   $convertible++;$sections+=count($data);foreach($data as $w)$fields+=count($w['settings']['fields']??[]);
   $why=''; if(ft_v44_roundtrip($src,$id,$why))$faithful++;else $failed[$id]=$why;
  }catch(Throwable $e){$failed[$id]=$e->getMessage();}
 }
 $missing=max(0,$expected-$matched);
 $ready=class_exists('\Elementor\Plugin')&&class_exists('DOMDocument')&&$missing===0&&$detected===$expected&&$convertible===$expected&&$faithful===$expected&&empty($failed);
 return compact('expected','detected','convertible','faithful','failed','origins','missing','ready','sections','fields');
}
function ft_v44_backup($id,$source,$origin){
 if(metadata_exists('post',$id,'_ft_v44_backup'))return;
 $b=['layout'=>$source,'origin'=>$origin,'content'=>get_post_field('post_content',$id),'data'=>get_post_meta($id,'_elementor_data',true),'mode'=>get_post_meta($id,'_elementor_edit_mode',true),'template'=>get_post_meta($id,'_elementor_template_type',true),'version'=>get_post_meta($id,'_elementor_version',true)];
 update_post_meta($id,'_ft_v44_backup',wp_json_encode($b));
}
function ft_v44_migrate_one($id,&$error=''){
 $origin='';$source=ft_v44_source_layout($id,$origin);if(trim($source)===''){$error='No source layout.';return false;}
 $why='';if(!ft_v44_roundtrip($source,$id,$why)){$error='Fidelity validation failed: '.$why;return false;}
 $data=ft_v44_build_data($source,$id); if(!$data){$error='Generated data empty.';return false;}
 ft_v44_backup($id,$source,$origin);
 update_post_meta($id,'_ft_layout',wp_slash($source));
 update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));
 update_post_meta($id,'_elementor_edit_mode','builder');
 update_post_meta($id,'_elementor_template_type',get_post_type($id)==='post'?'wp-post':'wp-page');
 if(defined('ELEMENTOR_VERSION')) update_post_meta($id,'_elementor_version',ELEMENTOR_VERSION);
 update_post_meta($id,'_ft_elementor_v44','1'); wp_update_post(['ID'=>$id,'post_content'=>'']);
 return true;
}
function ft_v44_migrate_all(){
 $pre=ft_v44_preflight(); if(!$pre['ready'])return ['ok'=>0,'failed'=>['preflight'=>'Preflight/fidelity validation is not clean.'],'preflight'=>$pre];
 $ok=0;$failed=[];foreach(ft_v44_ids() as $id){if(!ft_v44_seed_item_for_post($id))continue;$e='';if(ft_v44_migrate_one($id,$e))$ok++;else$failed[$id]=$e;}
 if(class_exists('\Elementor\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}}
 return ['ok'=>$ok,'failed'=>$failed,'preflight'=>$pre];
}
function ft_v44_restore_original(){
 $n=0;foreach(ft_v44_ids() as $id){if(!ft_v44_seed_item_for_post($id))continue;$origin='';$src=ft_v44_source_layout($id,$origin);if(trim($src)!=='')update_post_meta($id,'_ft_layout',wp_slash($src));delete_post_meta($id,'_elementor_data');delete_post_meta($id,'_elementor_edit_mode');delete_post_meta($id,'_elementor_template_type');delete_post_meta($id,'_elementor_version');delete_post_meta($id,'_ft_elementor_v44');wp_update_post(['ID'=>$id,'post_content'=>'']);$n++;}
 if(class_exists('\Elementor\Plugin')){try{\Elementor\Plugin::$instance->files_manager->clear_cache();}catch(Throwable $e){}}
 return $n;
}

add_action('admin_menu',function(){add_submenu_page('fraztech','Elementor V4.6 Editor','Elementor V4.6 Editor','manage_options','ft-elementor-v44','ft_v44_admin');},32);
function ft_v44_admin(){
 if(!current_user_can('manage_options'))return;$notice='';$report=null;
 if(isset($_POST['ft_v44_preflight'])&&check_admin_referer('ft_v44_preflight'))$report=ft_v44_preflight();
 if(isset($_POST['ft_v44_go'])&&check_admin_referer('ft_v44_go')){$r=ft_v44_migrate_all();$report=$r['preflight'];$notice=($r['ok']===$report['expected']&&empty($r['failed']))?'<div class="notice notice-success"><p><strong>V4.4 migration complete: '.intval($r['ok']).' of '.intval($report['expected']).' documents converted; 0 failed; semantic DOM fidelity passed before write.</strong></p></div>':'<div class="notice notice-error"><p><strong>Migration blocked/incomplete. Converted '.intval($r['ok']).'; failed '.intval(count($r['failed'])).'.</strong></p></div>';}
 if(isset($_POST['ft_v44_restore'])&&check_admin_referer('ft_v44_restore')){$n=ft_v44_restore_original();$notice='<div class="notice notice-success"><p><strong>Original Fraztech rendering restored for '.intval($n).' documents.</strong></p></div>';}
 if($report===null)$report=ft_v44_preflight();
 echo '<div class="wrap"><h1>Fraztech → Elementor V4.6 Full Editing</h1>'.$notice;
 echo '<p>V4.6 keeps the proven DOM-safe renderer and adds per-element design controls for size, spacing, colors, borders, typography, hover, order and position without inserting layout wrappers. Existing content/images are preserved.</p>';
 echo '<table class="widefat striped" style="max-width:820px;margin:18px 0"><tbody>';
 $rows=['Expected source layouts'=>$report['expected'],'Source layouts detected'=>$report['detected'],'Convertible documents'=>$report['convertible'],'Semantic DOM fidelity passed'=>$report['faithful'],'Generated editable sections'=>$report['sections'],'Editable text/link/image fields'=>$report['fields'],'Missing imported documents'=>$report['missing'],'Preflight failures'=>count($report['failed'])];
 foreach($rows as $k=>$v)echo '<tr><td><strong>'.esc_html($k).'</strong></td><td>'.intval($v).'</td></tr>';
 echo '<tr><td><strong>Status</strong></td><td>'.($report['ready']?'<span style="color:#087a35;font-weight:700">READY — migration unlocked</span>':'<span style="color:#b32d2e;font-weight:700">BLOCKED — nothing will be migrated</span>').'</td></tr></tbody></table>';
 if($report['origins']){echo '<p><strong>Recovered from:</strong> ';$b=[];foreach($report['origins'] as $k=>$v)$b[]=esc_html($k).': '.intval($v);echo implode(' &nbsp;·&nbsp; ',$b).'</p>';}
 if($report['failed']){echo '<details style="max-width:900px;margin:12px 0"><summary><strong>Show failures</strong></summary><ul>';foreach(array_slice($report['failed'],0,25,true) as $id=>$e)echo '<li>'.esc_html((string)$id).': '.esc_html($e).'</li>';echo '</ul></details>';}
 echo '<form method="post" style="display:inline-block;margin-right:10px">';wp_nonce_field('ft_v44_preflight');echo '<button class="button" name="ft_v44_preflight">Run Preflight Again</button></form>';
 if($report['ready']){echo '<form method="post" style="display:inline-block">';wp_nonce_field('ft_v44_go');echo '<button class="button button-primary button-hero" name="ft_v44_go">Rebuild DOM-Safe Elementor Data</button></form>';}
 else echo '<p><strong>Convert remains locked until all 76 layouts pass semantic DOM fidelity.</strong></p>';
 echo '<hr style="margin:28px 0"><form method="post">';wp_nonce_field('ft_v44_restore');echo '<button class="button" name="ft_v44_restore">Restore Original Fraztech Rendering</button></form></div>';
}
