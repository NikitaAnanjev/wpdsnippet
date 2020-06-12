<?php
///**
// * @package WPDSnippets
// */
//
//class WpdSnippetsPluginActivate
//{
//
//   static $class_title = "ACTIVATION CLASS";
//
//
//
//    function __construct()
//    {
//        /* Hook into the 'init' action so that the function
//        * Containing our post type registration is not
//        * unnecessarily executed.
//        */
//        add_action('init', array($this, 'custom_post_type'), 0);
//        add_action('init', array($this, 'init'));
//    }
//    function init()
//    {
//        WpdSnippetsPluginActivate::setup_schedules();
//
//        add_action('event_start_grabbing', array('WpdSnippetsPluginActivate', 'import_snippets_json_api'));
//    }
//
//    public static function setup_schedules()
//    {
//
//        if (!wp_next_scheduled('event_start_grabbing')) {
//
//            wp_schedule_event(time(), 'hourly', 'event_start_grabbing');
//        }
//    }
//
//
//    public static function activate()
//    {
//
//        //    GENERATE CUSTOM POST TYPES
//        WpdSnippetsPluginActivate::custom_post_type();
//        WpdSnippetsPluginActivate::import_snippets_json_api();
//        WpdSnippetsPluginActivate::unschedule_my_hooks();
//        WpdSnippetsPluginActivate::setup_schedules();
//
////    FLUSH REWRITE RULES
//        flush_rewrite_rules();
//
//
//    }
//
//
//    public static function log_message($message)
//    {
//        $myFile = plugin_dir_path(__FILE__) . 'grab_from_wpdistro_api_' . date('F') . '.txt';
//        $fh = fopen($myFile, 'a') or die("can't open file");
//        $stringData = "\n" . date('Y-m-d H:i:s') . ' :: ' . $message;
//        fwrite($fh, $stringData);
//        fclose($fh);
//    }
//
//    private static function unschedule_my_hooks()
//    {
//
//        $timestamp = wp_next_scheduled('event_start_grabbing');
//
//        if ($timestamp) {
//
//            wp_unschedule_event($timestamp, 'event_start_grabbing');
//        }
//    }
//
//
//    // Our custom post type function
//    public static function custom_post_type()
//    {
//
//// Set UI labels for Custom Post Type
//        $labels = array(
//            'name' => _x('WPD_snippets', 'Post Type General Name'),
//            'singular_name' => _x('WPD_snippet', 'Post Type Singular Name'),
//            'menu_name' => __('WPD_snippets'),
//            'parent_item_colon' => __('Parent WPD_snippet'),
//            'all_items' => __('All WPD_snippets'),
//            'view_item' => __('View WPD_snippet'),
//            'add_new_item' => __('Add New WPD_snippet'),
//            'add_new' => __('Add New'),
//            'edit_item' => __('Edit WPD_snippet'),
//            'update_item' => __('Update WPD_snippet'),
//            'search_items' => __('Search WPD_snippet'),
//            'not_found' => __('Not Found'),
//            'not_found_in_trash' => __('Not found in Trash'),
//        );
//
//// Set other options for Custom Post Type
//
//        $args = array(
//            'label' => __('wpd_snippets'),
//            'description' => __('WPD_snippet news and reviews'),
//            'labels' => $labels,
//            // Features this CPT supports in Post Editor
//            'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),
//            // You can associate this CPT with a taxonomy or custom taxonomy.
//            'taxonomies' => array('type'),
//            /* A hierarchical CPT is like Pages and can have
//            * Parent and child items. A non-hierarchical CPT
//            * is like Posts.
//            */
//            'hierarchical' => false,
//            'public' => true,
//            'show_ui' => true,
//            'show_in_menu' => true,
//            'show_in_nav_menus' => true,
//            'show_in_admin_bar' => true,
//            'menu_position' => 5,
//            'can_export' => true,
//            'has_archive' => true,
//            'exclude_from_search' => false,
//            'publicly_queryable' => true,
//            'capability_type' => 'post',
//            'show_in_rest' => true,
//
//        );
//
//        // Registering your Custom Post Type
//        register_post_type('wpd_snippets', $args);
//
//    }
//
//    //INSERT DATA FROMWPD API
//
//
//   static function get_snippets_via_curl()
//    {
//        $API_URL = 'https://wpdistro.com/wp-json/wp/v2/posts/';
//
//        $ch = curl_init($API_URL);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//        //CURL INITIATE DATA
//        $results = curl_exec($ch);
//        $error = curl_error($ch);
//        if ($error) {
//            return 'Curl failed: ' . $error;
//        }
//        //CURL CLOSE
//        curl_close($ch);
//
//
//        $results = json_decode($results, true);
//
//        if (is_wp_error($results)) {
//
//            return 'JSON failed: ' . $results->get_error_message();
//        }
//
//        if (!is_array($results) || empty($results)) {
//
//            return 'No such data.';
//        }
//        return $results;
//
//    }
//
//
//    public static function import_snippets_json_api()
//    {
//        $mystart = time();
//        WpdSnippetsPluginActivate::log_message("\n" . 'Start');
//        $snippets = WpdSnippetsPluginActivate::get_snippets_via_curl();
//
//        if (!$snippets || !is_array($snippets)) {
//
//            WpdSnippetsPluginActivate::log_message('No snippets' . ('string' === gettype($snippets) ? ' :: ' . $snippets : ''));
//
//            return;
//        }
//
//        WpdSnippetsPluginActivate::log_message($snippets . ' Snippet found');
//
//        WpdSnippetsPluginActivate::process_snippets($snippets);
//
//        WpdSnippetsPluginActivate::log_message('Finished. Total execution time: ' . (time() - $mystart) . ' s' . "\n");
//    }
//
//    static function process_snippets($snippets)
//    {
//
//
//        $i = 0;
//        foreach ($snippets as $snippet) {
//            $snippet_id = $snippet['id'];
//            $code_snippet = htmlspecialchars($snippet['acf']['code']);
//            $slug = $snippet['slug'];
//            $title = $snippet['title']['rendered'];
//            $i++;
//
//              WpdSnippetsPluginActivate::log_message($i . '. Process snippet ' . $snippet_id . "\n");
//
//
//            // CHECK IF THE CAR ALREADY EXISTS
//            $args = array(
//                'post_type' => 'wpd_snippets',
//                'posts_per_page' => 1,
//                'post_status' => 'publish',
//                'meta_query' => array(
//                    array(
//                        'key' => 'import_id',
//                        'value' => $snippet_id,
//                        'compare' => '=',
//                    )
//                )
//            );
//
//            $posts = get_posts($args);
//
//            if (is_wp_error($posts)) {
//                  WpdSnippetsPluginActivate::log_message('Failed to get post with import_id = ' . $snippet_id);
//                continue;
//            }
//
//            $existing_snippet_id = 0;
//
//            if (!$posts || !is_array($posts) || !isset($posts[0])) {
//
//                //If THERE ARE NO POST WITH THE SAME ID THEN UPLOAD THE POST
//                $inserted_snippets = array(
//                    'post_name' => $slug,
//                    'post_title' => $title,
//                    'post_content' => $code_snippet,
//                    'post_type' => 'wpd_snippets',
//                    'post_status' => 'publish'
//                );
//                $existing_snippet_id = wp_insert_post($inserted_snippets);
//
//                //CHECK IF ARRAY HAS ERROR AND CONTINUE
//                if (is_wp_error($existing_snippet_id)) {
//
//                    WpdSnippetsPluginActivate::log_message(   WpdSnippetsPluginActivate::$class_title.'Failed to import snippet with ID = ' . $snippet_id);
//                    continue;
//                }
//
//                update_post_meta($existing_snippet_id, 'import_id', $snippet_id);
//            } else {
//                $post = $posts[0];
//                $existing_snippet_id = $post->ID;
//            }
//        }
//
//    }
//}