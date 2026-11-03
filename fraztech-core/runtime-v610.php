<?php
if (!defined('ABSPATH')) exit;

/** Purge stale generated/page cache once after this runtime version is installed. */
function ft_v610_refresh_runtime_cache(){
    $version='6.1.2';
    if ((string)get_option('ft_core_runtime_version','') === $version) return;
    update_option('ft_core_runtime_version',$version,false);

    // Official LiteSpeed purge hook. Safe when LiteSpeed is not installed.
    do_action('litespeed_purge_all','Fraztech Core '.$version.' runtime refresh');
    if (function_exists('wp_cache_flush')) wp_cache_flush();
}
add_action('admin_init','ft_v610_refresh_runtime_cache',1);
add_action('init','ft_v610_refresh_runtime_cache',1);
