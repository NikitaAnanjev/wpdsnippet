<?php
/**
 * @package WPDSnippets
 */
/**
 * Plugin Name: WPD Snippets
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

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
};

class WpdSnippets
{


    public $plugin_name;

    function __construct()
    {

        $this->plugin_name = plugin_basename(__FILE__);
        /* Hook into the 'init' action so that the function
        * Containing our post type registration is not
        */
        add_action('init', array($this, 'custom_post_type'), 0);
        add_action('init', array($this, 'init'));
    }

    /**
     * SETUP CRON JOB
     */
    public static function setup_schedules()
    {
        if (!wp_next_scheduled('event_start_grabbing')) {
            wp_schedule_event(time(), 'hourly', 'event_start_grabbing');
        }
    }

    /**
     * CRON job hourly updates
     */
    function init()
    {
        WpdSnippets::setup_schedules();
        // Get data from API
        add_action('event_start_grabbing', array($this, 'import_snippets_json_api'));
    }

    function register()
    {
        // Enqueue CSS and SCRIPTS.js
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        // Create custom columns for the post list
        add_filter('manage_edit-wpd_snippets_columns', array($this, 'add_new_wpd_snippets_columns'));
        // Create custom elements in columns for the post list
        add_action('manage_posts_custom_column', array($this, 'bs_projects_table_content'), 10, 2);
        // Creating custom admin section
        add_action('admin_menu', array($this, 'add_admin_pages'));
        // Create Link for the custom admin page from plugin list
        add_filter("plugin_action_links_$this->plugin_name", array($this, 'setting_plugin_link'));
        $this->run_ajax_snippets();
    }

    /**
     * Create custom links in the plugin row of plugin list
     * @param $links
     * @return mixed
     */
    public function setting_plugin_link($links)
    {

        $new_link = '<a href="admin?page=wpdistro_plugin_page">Settings</a>';
        $new_link_sub = '<a href="admin?page=wpd_snippet_submenu_customisation">Customise</a>';

        array_push($links, $new_link);
        array_push($links, $new_link_sub);
        return $links;
    }

    /**
     * Custom plugin page and subpage in admin dashboard menu
     */
    public function add_admin_pages()
    {
        add_menu_page('WPD Snippets', 'WPDistro', 'manage_options', 'wpdistro_plugin_page', array($this, 'admin_index_page'), 'dashicons-image-filter', 110);
        add_submenu_page('wpdistro_plugin_page', 'Snippet Customisation', 'Customise', 'manage_options', 'wpd_snippet_submenu_customisation', array($this, 'wpd_snippet_submenu_customisation'), 100);
    }

    /**
     * Template for custom plugin page
     */
    public function admin_index_page()
    {
        require_once plugin_dir_path(__FILE__) . 'templates/admin_snippet_page.php';
    }

    /**
     *  Template for custom plugin subpage
     */
    public function wpd_snippet_submenu_customisation()
    {
        require_once plugin_dir_path(__FILE__) . 'templates/admin_snippet_sub_page_customise.php';
    }

    /**
     * AJAX activate and diactivate snippets
     */
    function run_ajax_snippets()
    {
        add_action('wp_ajax_my_action', array($this, 'my_action_callback'));

        add_action('admin_print_footer_scripts', array($this, 'my_action_javascript'), 99);
    }

    /**
     * Activate plugin
     */
    function activate()
    {
        WpdSnippets::unschedule_my_hooks();
        WpdSnippets::setup_schedules();
        flush_rewrite_rules();
    }

    /**
     * Deactivate plugin
     */
    function deactivate()
    {
        WpdSnippets::unschedule_my_hooks();

        flush_rewrite_rules();
    }

    /**
     * ENQUEUE style CSS and custom Js files
     */
    function enqueue()
    {
        wp_enqueue_style('pluginstyle', plugins_url('/assets/style.css', __FILE__));
        wp_enqueue_script('pluginscript', plugins_url('/assets/script.js', __FILE__), 'jQuery');
    }

    /**
     * CUSTOM column sections for post list
     * @param $columns
     * @return array
     *
     */
    function add_new_wpd_snippets_columns($columns)
    {
        $new_columns = array();
        $new_columns['cb'] = '<input type="checkbox" />';
        $status = get_query_var('post_status');
        if ('trash' !== $status) {
            $new_columns['activate'] = __('Status', 'wpd_snippets');
        }

        $new_columns['title'] = __('Title', 'wpd_snippets');
        $new_columns['description'] = __('Description', 'wpd_snippets');
        $new_columns['code'] = __('Code', 'wpd_snippets');
        $new_columns['import_id'] = __('ImportID', 'wpd_snippets');
        $new_columns['tags'] = __('Tags', 'wpd_snippets');
        $new_columns['date'] = __('Date', 'wpd_snippets');

        return $new_columns;
    }

    /**
     * Jquery script for ajax,
     * in order to send data without embeded <form> tag.
     * Simply triggering a button or a input boolian toggal true or folse.
     */
    function my_action_javascript()
    {
        ?>
        <script>
            jQuery(document).ready(function ($) {
                $(".scales").click(function () {
                    $getvalue = $(this).val();

                    if ($(this).is(":checked")) {
                        $stat = 1;
                        $(this).siblings('p').text("Active");

                    } else {
                        $stat = 0;
                        $(this).siblings('p').text("Diactivated");
                    }

                    const xhr = new XMLHttpRequest();


                    xhr.onload = function () {
                        xhr.status < 200 || 400 <= xhr.status || console.log(xhr.responseText);
                    };
                    var a = "action=my_action";

                    xhr.open("POST", ajaxurl, true);

                    xhr.setRequestHeader(
                        "Content-Type", "application/x-www-form-urlencoded; charset=UTF-8"
                    );

                    xhr.send(a);
                    var data = {
                        action: 'my_action',
                        toggle: $stat,
                        snippetid: $getvalue
                    };
                    jQuery.post(ajaxurl, data, function (response) {
//                        alert('This is ajax respons: ' + response);
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Receive AJAX CALL BACK and Update Database you can also use 0 or 1 instead true or false.
     */
    function my_action_callback()
    {
        $toggle = $_POST['toggle'];
        $selectedsnippetId = $_POST['snippetid'];
        if ($toggle != 0) {
            update_post_meta($selectedsnippetId, 'snippet_active', true);
        } else {
            update_post_meta($selectedsnippetId, 'snippet_active', false);
        }
        wp_die();
    }


    /**
     * Updating post list elements
     * @param $column_name
     * @param $post_id
     */
    function bs_projects_table_content($column_name, $post_id)
    {
        if ('activate' == $column_name) {

            $active_status = get_post_meta($post_id, 'snippet_active', 'wpd_snippets');
            if (!$active_status == false) {
                $slug = 'Active';
                $status = 'checked';
            } else {
                $slug = 'Deactivated';
                $status = '';
            }
            echo ' <input value="' . $post_id . '" type="checkbox" class="scales" name="scales"' . $status . ' >';
            echo '<p id="current_snippet_' . $post_id . '">' . $slug . ' </p>';
        }

        if ('import_id' == $column_name) {
            $import_id = get_post_meta($post_id, 'import_id', 'wpd_snippets');
            echo $import_id;
        }

        if ('status' == $column_name) {
            $import_id = get_post_meta($post_id, 'import_id', 'wpd_snippets');
            echo $import_id;
        }

        if ('code' == $column_name) {
            $content = get_the_excerpt($post_id);
            $content = wp_trim_words($content, $num_words = 15);
            echo $content;
        }
    }

    /**
     * GET ACTIVE SNIPPETS FROM DATABSE
     */
    public function get_active_snippets()
    {
        // CHECK IF THE CAR ALREADY EXISTS
        $args = array(
            'post_type' => 'wpd_snippets',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'snippet_active',
                    'value' => 1,
                    'compare' => '=',
                )
            )
        );

        $posts = get_posts($args);
        $active_posts = [];
        foreach ($posts as $post) {
            $active_snippet = $post->post_content;
            $active_snippet = sprintf($active_snippet);
//            echo $active_snippet;
//            $active_snippet;
            $this->execute_snippet($active_snippet);
        }
    }

    /**
     * Prepare the code by removing php tags from beginning and end
     *
     * @param string $code
     *
     * @return string
     */
    function prepare_code($code)
    {

        /* Remove <?php and <? from beginning of snippet */
        $code = preg_replace('|^[\s]*<\?(php)?|', '', $code);

        /* Remove ?> from end of snippet */
        $code = preg_replace('|\?>[\s]*$|', '', $code);

        return $code;
    }

    function prepare_retrieve_code($code)
    {
        $code = html_entity_decode($code);
        return $code;
    }

    /**
     * Execute a snippet
     *
     * Code must NOT be escaped, as
     * it will be executed directly
     *
     * @param string $code The snippet code to execute
     * @param int $id The snippet ID
     * @param bool $catch_output Whether to attempt to suppress the output of execution using buffers
     *
     * @return mixed The result of the code execution
     *
     * *********** READ MORE ABOUT eval() ---> PHP function specifics
     *
     */
    function execute_snippet($code)
    {

//        if (empty($code) || defined('CODE_SNIPPETS_SAFE_MODE') && CODE_SNIPPETS_SAFE_MODE) {
//            return false;
//        }
        $code = $this->prepare_retrieve_code($code);

//        print_r($code);

        ob_start();

        eval($code);

        $result = ob_get_contents();


        ob_end_clean();


        return $result;
    }

    /**
     *
     * LOG Writing function creates a txt file in the plugin folder.
     * @param $message
     */

    function log_message($message)
    {
        $myFile = plugin_dir_path(__FILE__) . 'grab_from_wpdistro_api_' . date('F') . '.txt';
        $fh = fopen($myFile, 'a') or die("can't open file");
        $stringData = "\n" . date('Y-m-d H:i:s') . ' :: ' . $message;
        fwrite($fh, $stringData);
        fclose($fh);
    }

    /**
     * This function flush cron hook from the system
     * when you deactivate or unninstall the plugin
     */
    private static function unschedule_my_hooks()
    {

        $timestamp = wp_next_scheduled('event_start_grabbing');

        if ($timestamp) {

            wp_unschedule_event($timestamp, 'event_start_grabbing');
        }
    }


    // Our custom post type function
    function custom_post_type()
    {

        // Set UI labels for Custom Post Type
        $labels = array(
            'name' => _x('WPD_snippets', 'Post Type General Name'),
            'singular_name' => _x('WPD_snippet', 'Post Type Singular Name'),
            'menu_name' => __('WPD_snippets'),
            'parent_item_colon' => __('Parent WPD_snippet'),
            'all_items' => __('All WPD_snippets'),
            'view_item' => __('View WPD_snippet'),
            'add_new_item' => __('Add New WPD_snippet'),
            'add_new' => __('Add New'),
            'edit_item' => __('Edit WPD_snippet'),
            'update_item' => __('Update WPD_snippet'),
            'search_items' => __('Search WPD_snippet'),
            'not_found' => __('Not Found'),
            'not_found_in_trash' => __('Not found in Trash'),
        );
        // Set other options for Custom Post Type
        $args = array(
            'label' => __('wpd_snippets'),
            'description' => __('WPD_snippet news and reviews'),
            'labels' => $labels,
            // Features this CPT supports in Post Editor
            'supports' => array('title', 'editor', 'excerpt', 'author', 'revisions', 'custom-fields',),
            // You can associate this CPT with a taxonomy or custom taxonomy.
            'taxonomies' => array('type'),
            /* A hierarchical CPT is like Pages and can have
            * Parent and child items. A non-hierarchical CPT
            * is like Posts.
            */
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 5,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'post',
            'show_in_rest' => true,

        );
        // Registering your Custom Post Type
        register_post_type('wpd_snippets', $args);
    }

    //INSERT DATA FROMWPD API

    /**
     * @return mixed|string
     * SETUP API DATA
     */
    function get_snippets_via_curl()
    {
        $API_URL = 'https://wpdistro.com/wp-json/wp/v2/posts/';

        $ch = curl_init($API_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //CURL INITIATE DATA
        $results = curl_exec($ch);
        $error = curl_error($ch);
        if ($error) {
            return 'Curl failed: ' . $error;
        }
        //CURL CLOSE
        curl_close($ch);


        $results = json_decode($results, true);

        if (is_wp_error($results)) {

            return 'JSON failed: ' . $results->get_error_message();
        }

        if (!is_array($results) || empty($results)) {

            return 'No such data.';
        }
        return $results;

    }

    /**
     * GET API DATA
     */
    function import_snippets_json_api()
    {
        $mystart = time();
        $this->log_message("\n" . 'Start');
        $snippets = $this->get_snippets_via_curl();

        if (!$snippets || !is_array($snippets)) {

            $this->log_message('No snippets' . ('string' === gettype($snippets) ? ' :: ' . $snippets : ''));

            return;
        }

        $this->log_message($snippets . ' Snippet found');

        $this->process_snippets($snippets);

        $this->log_message('Finished. Total execution time: ' . (time() - $mystart) . ' s' . "\n");
    }

    /**
     * STORE SNIPPET DATA FROM API TO THE CUSTOM POST TYPES
     * @param $snippets
     */
    function process_snippets($snippets)
    {
        $i = 0;
        foreach ($snippets as $snippet) {
            $snippet_id = $snippet['id'];
            $code_snippet = htmlspecialchars($snippet['acf']['code']);
//            $code_snippet = $this->prepare_code($code_snippet);

            $slug = $snippet['slug'];
            $title = $snippet['title']['rendered'];
            $i++;

            $this->log_message($i . '. Process snippet ' . $snippet_id . "\n");


            // CHECK IF THE CAR ALREADY EXISTS
            $args = array(
                'post_type' => 'wpd_snippets',
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'import_id',
                        'value' => $snippet_id,
                        'compare' => '=',
                    )
                )
            );

            $posts = get_posts($args);

            if (is_wp_error($posts)) {
                $this->log_message('Failed to get post with import_id = ' . $snippet_id);
                continue;
            }

            $existing_snippet_id = 0;

            if (!$posts || !is_array($posts) || !isset($posts[0])) {

                //If THERE ARE NO POST WITH THE SAME ID THEN UPLOAD THE POST
                $inserted_snippets = array(
                    'post_name' => $slug,
                    'post_title' => $title,
                    'post_content' => $code_snippet,
                    'post_type' => 'wpd_snippets',
                    'post_status' => 'publish'
                );
                $existing_snippet_id = wp_insert_post($inserted_snippets);

                //CHECK IF ARRAY HAS ERROR AND CONTINUE
                if (is_wp_error($existing_snippet_id)) {

                    $this->log_message('Failed to import snippet with ID = ' . $snippet_id);
                    continue;
                }

                update_post_meta($existing_snippet_id, 'import_id', $snippet_id);
                update_post_meta($existing_snippet_id, 'snippet_active', false);

                update_post_meta($existing_snippet_id, 'code_snippet', $code_snippet);
            } else {
                $post = $posts[0];
                $existing_snippet_id = $post->ID;
            }
        }

    }
}


// ACTIVATE THE CLASS
if (class_exists('WpdSnippets')) {
    $wpdSnippets = new WpdSnippets();
    $wpdSnippets->register();
    $wpdSnippets->run_ajax_snippets();
    $wpdSnippets->get_active_snippets();

}


//ACTIVATION
register_activation_hook(__FILE__, array($wpdSnippets, 'activate'));

//DEACTIVATION
register_deactivation_hook(__FILE__, array($wpdSnippets, 'deactivate'));

// UNINSTALL
//register_uninstall_hook(__FILE__, array($wpdSnippets, 'uninstall'));