<?php
/**
 * @package WPDSnippets
 */
/**
 * Plugin Name: WPD Snippet
 * Plugin URI: https://www.wpdistro.com/
 * Description: This is allows to effectively use WordPress code snippets.
 * Version: 1.0
 * Author: n.a.
 * Author URI: https://webexpress.dk/
 * Text Domain: wpdsnippet
 **/


if (!defined('ABSPATH')) {
    die();
}

//if (!function_exists('add_action')) {
//    echo 'Not allowed!';
//    exit();
//}
//setup
//define('WPDSNIPPETS_PLUGIN_URL', __FILE__);

class WpdSnippets
{
//METHODS
//    function __construct($snippet_url)
//    {
//        echo $snippet_url;
//    }

    function activate()
    {
//    GENERATE CUSTOM POST TYPES
//    FLUSH REWRITE RULES
    }

    function deactivate()
    {
//    FLUSH REWRITE RULES

    }

    function uninstall()
    {
//        DELETE CUSTOM POST TYPES
//        DELETE ALL THE PLUGIN DATA FROM DB
    }


}

// ACTIVATE THE CLASS
if (class_exists('WpdSnippets')) {
    $wpdSnippets = new WpdSnippets();
}


//ACTIVATION
register_activation_hook(__FILE__, array($wpdSnippets, 'activate'));

//DEACTIVATION
register_deactivation_hook(__FILE__, array($wpdSnippets, 'deactivate'));

// UNINSTALL