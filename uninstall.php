<?php

/**
 * Trigger this file on plugin uninstall
 *
 * @package WPDSnippets
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die();
}


//CLEAR DB STORED DATA
$snippets = get_posts(
    array(
        'post_type' => 'wpd_snippets',
        'numberposts' => -1
    ));


foreach ($snippets as $snippet){
    wp_delete_post($post->ID, true);
}

//global $wpdb;

//$wpdb->query("DELETE FROM wp_posts WHERE post_type = 'wpd_snippets'");
//$wpdb->query("DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT if FROM wp_posts)");
//$wpdb->query("DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT if FROM wp_posts)");