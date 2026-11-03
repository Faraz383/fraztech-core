<?php
if (!defined('ABSPATH')) exit;
/** Fraztech V6.1.9 — browser-native 3D cursor hotspot + front-tip animation. */
function ft_v619_motion_assets(){
    if (is_admin()) return;
    wp_enqueue_style('ft-final-motion-v619', plugins_url('assets/final-motion-v619.css', __FILE__), [], '6.2.9');
    wp_enqueue_script('ft-final-motion-v619', plugins_url('assets/final-motion-v619.js', __FILE__), [], '6.2.9', true);
    wp_script_add_data('ft-final-motion-v619','strategy','defer');
}
add_action('wp_enqueue_scripts','ft_v619_motion_assets',95);
