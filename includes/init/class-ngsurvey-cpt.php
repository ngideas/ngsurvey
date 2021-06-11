<?php
/**
 * The survey custom post type functionality of the plugin.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/admin/init
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The survey custom post type  functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/admin
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey_CPT {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * The custom post type name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cpt_name    The name of this custom post type.
	 */
	private $cpt_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $cpt_name, $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->cpt_name = $cpt_name;

	}

	/**
	 * Defines the survey post type of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function define_survey_post_type() {
	    
	    $capabilities	= $this->compile_post_type_capabilities('survey', 'surveys');
	    $settings 		= include NGSURVEY_PATH . 'includes/init/class-ngsurvey-settings.php';
	    $url_base 		= __( $settings->get_option( 'survey_url_base', 'survey' ), 'ngsurvey' ); // survey permalink base
	    $cateogry_base 	= __( $settings->get_option( 'category_url_base', 'surveys' ), 'ngsurvey' ); // survey category permalink base
	    
	    $labels = array(
	        'name'                  => _x( 'Surveys', 'Post type general name', 'ngsurvey' ),
	        'singular_name'         => _x( 'Survey', 'Post type singular name', 'ngsurvey' ),
	        'menu_name'             => _x( 'NgSurvey', 'NgSurvey', 'ngsurvey' ),
	        'name_admin_bar'        => _x( 'Survey', 'Add New on Toolbar', 'ngsurvey' ),
	        'add_new'               => __( 'Add New', 'ngsurvey' ),
	        'add_new_item'          => __( 'Add New Survey', 'ngsurvey' ),
	        'new_item'              => __( 'New Survey', 'ngsurvey' ),
	        'edit_item'             => __( 'Edit Survey', 'ngsurvey' ),
	        'view_item'             => __( 'View Survey', 'ngsurvey' ),
	        'all_items'             => __( 'All Surveys', 'ngsurvey' ),
	        'search_items'          => __( 'Search Surveys', 'ngsurvey' ),
	        'parent_item_colon'     => __( 'Parent Surveys:', 'ngsurvey' ),
	        'not_found'             => __( 'No surveys found.', 'ngsurvey' ),
	        'not_found_in_trash'    => __( 'No surveys found in Trash.', 'ngsurvey' ),
	        'featured_image'        => _x( 'Survey Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'ngsurvey' ),
	        'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'ngsurvey' ),
	        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase for this post type.', 'ngsurvey' ),
	        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase for this post type.', 'ngsurvey' ),
	        'insert_into_item'      => _x( 'Insert into survey', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post).', 'ngsurvey' ),
	        'uploaded_to_this_item' => _x( 'Uploaded to this survey', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post).', 'ngsurvey' ),
	        'filter_items_list'     => _x( 'Filter surveys list', 'Screen reader text for the filter links heading on the post type listing screen. Default "Filter posts list"/"Filter pages list".', 'ngsurvey' ),
	        'items_list_navigation' => _x( 'Surveys list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default "Posts list navigation"/"Pages list navigation".', 'ngsurvey' ),
	        'items_list'            => _x( 'Surveys list', 'Screen reader text for the items list heading on the post type listing screen. Default "Posts list"/"Pages list".', 'ngsurvey' ),
	    );
	    
	    $args = array(
	        'public'                => true,
	        'publicly_queryable'    => true,
	        'show_ui'               => true,
	        'show_in_menu'          => true,
	        'query_var'             => true,
	        'has_archive'           => true,
	        'show_in_rest'          => true,
	        'show_in_nav_menus'     => true,
	        'map_meta_cap'          => true,
	        'hierarchical'          => false,
	        'menu_position'         => null,
	        'supports'              => array( 'title', 'excerpt', 'editor', 'author', 'thumbnail' ),
	    	'rewrite'               => array( 'slug' => $url_base ),
	        'labels'                => $labels,
	        'capabilities'          => $capabilities,
	    	'rest_base'             => $cateogry_base,
	        'menu_icon'             => 'dashicons-chart-pie'
	    );
	    
	    register_post_type( $this->cpt_name, $args );
	}
	
	/**
	 * Defines the survey taxonomy of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function define_survey_taxonomy() {
	    
	    $labels = array(
	        'name'              => _x( 'Survey Categories', 'taxonomy general name' ),
	        'singular_name'     => _x( 'Survey Category', 'taxonomy singular name' ),
	        'search_items'      => __( 'Search Survey Categories' ),
	        'all_items'         => __( 'All Survey Categories' ),
	        'parent_item'       => __( 'Parent Survey Category' ),
	        'parent_item_colon' => __( 'Parent Survey Category:' ),
	        'edit_item'         => __( 'Edit Survey Category' ),
	        'update_item'       => __( 'Update Survey Category' ),
	        'add_new_item'      => __( 'Add New Survey Category' ),
	        'new_item_name'     => __( 'New Survey Category' ),
	        'menu_name'         => __( 'Categories' ),
	        'show_ui'           => true,
	        'show_admin_column' => true,
	    );
	    $args = array(
	        'labels'            => $labels,
	        'hierarchical'      => true,
	        'show_in_rest'      => true,
	        'rewrite'           => array( 'slug' => 'surveys' ),
	    );
	    
	    register_taxonomy( $this->cpt_name.'s', $this->cpt_name, $args );
	}
	
	/**
	 * Defines the messages shown after saving the survey. The messages are used to show useful information
	 * such as add questions page to the user instead of the user returning back to list.
	 * 
	 * @param array $messages the list of messages
	 * @return array updated $messages object 
	 */
	public function survey_updated_messages( $messages ) {
	    global $post, $post_ID;
	    
	    if ( $post->post_type != $this->cpt_name ) {
	        return $messages;
	    }
	    
	    $edit_questions_url = admin_url( 'edit.php?post_type='.$this->cpt_name.'&page=edit_questions&post='. $post->ID );
	    
	    $messages[ $this->cpt_name ] = array(
	        0 => '',
	        1 => sprintf( __('Survey updated. <a href="%s">Add Questions</a> to your survey.'), esc_url( $edit_questions_url ) ),
	        2 => __('Custom field updated.'),
	        3 => __('Custom field deleted.'),
	        4 => __('Survey updated.'),
	        5 => isset($_GET['revision']) ? sprintf( __('Survey restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	        6 => sprintf( __('Survey published. <a href="%s">Add Questions</a>'), esc_url( $edit_questions_url ), esc_url( get_permalink( $post_ID ) ) ),
	        7 => __('Survey saved.'),
	        8 => sprintf( __('Survey submitted. <a target="_blank" href="%s">Preview Survey</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	        9 => sprintf( __('Survey scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Survey</a>'),
	            date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
	        10 => sprintf( __('Survey draft updated. <a href="%s">Add Questions</a>'), esc_url( $edit_questions_url ), esc_url( get_permalink( $post_ID ) ) ),
	    );
	    
	    return $messages;
	}
	
	/**
	 * Defines the edit questions page menu. This menu will not be shown on the wp menu,
	 * instead this is used as a menu placeholder to display the edit questions page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_menu_placeholders() {

	    // Define page for extensions
	    require_once NGSURVEY_PATH . 'admin/controllers/class-controller-extensions.php';
	    $controller = new NgSurvey_Controller_Extensions(array(
	        'plugin_name' => $this->plugin_name,
	        'version' => $this->version,
	        'title' => __( 'Extensions', 'ngsurvey' )
	    ));
	    
	    add_submenu_page(
	        'edit.php?post_type='.$this->cpt_name,
	        __( 'Install NgSurvey Extensions', 'ngsurvey' ),
	        __( 'Extensions', 'ngsurvey' ),
	        'manage_options',
	        'ngsurvey_extensions',
	        array($controller, 'display')
        );

	    // Define page for questions edit page
	    require_once NGSURVEY_PATH . 'admin/controllers/class-controller-questions.php';
	    $controller = new NgSurvey_Controller_Questions(array(
	        'plugin_name' => $this->plugin_name,
	        'version' => $this->version,
	        'title' => __( 'Questions', 'ngsurvey' )
	    )); 
	    
	    add_submenu_page(
	        null, // don't display the menu item
	        __( 'Edit Questions', 'ngsurvey' ),
	        __( 'Edit Questions', 'ngsurvey' ),
	        'manage_options',
	        'edit_questions',
	        array($controller, 'display')
        );

	    // Define page for view reports
	    require_once NGSURVEY_PATH . 'admin/controllers/class-controller-reports.php';
	    $controller = new NgSurvey_Controller_Reports(array(
	        'plugin_name' => $this->plugin_name,
	        'version' => $this->version,
	        'title' => __( 'Reports', 'ngsurvey' )
	    ));

	    add_submenu_page(
	        null, // don't display the menu item
	        __( 'View Reports', 'ngsurvey' ),
	        __( 'View Reports', 'ngsurvey' ),
	        'manage_options',
	        'view_reports',
	        array($controller, 'display')
        );
	    
	    // NgSurvey Settings Menu
	    require_once NGSURVEY_PATH . 'admin/controllers/class-controller-settings.php';
	    $controller = new NgSurvey_Controller_Settings(array(
	        'plugin_name' => $this->plugin_name,
	        'version' => $this->version,
	        'title' => __( 'Settings', 'ngsurvey' )
	    ));
	    
	    $this->settings_hook_suffix = add_submenu_page(
            'edit.php?post_type='.NGSURVEY_CPT_NAME,
            __( 'NgSurvey Settings', 'ngsurvey' ),
            __( 'Settings', 'ngsurvey' ),
            'manage_options',
            'ngsurvey_settings',
	        array( $controller, 'display' )
        );
	}
	
	public function handle_shortcode( $attrs = array() ) {
	    $params = shortcode_atts( array( 'id' => 0 ), $attrs );
	    
	    if( empty( $params[ 'id' ] ) || ! get_post( $params[ 'id' ] ) ) {
	        return __( 'No survey found.', 'ngsurvey' );
	    }
	    
	    require_once NGSURVEY_PATH . 'public/controllers/class-controller-survey.php';
	    $controller = new NgSurvey_Controller_Survey( array(
	        'plugin_name' => $this->plugin_name,
	        'version' => $this->version,
	        'title' => __( 'Survey', 'ngsurvey' )
	    ) );
	    
	    ob_start();
	    $controller->display( $params[ 'id' ] );
	    
	    return ob_get_clean();
	}
	
	/**
	 * Render the options page for plugin
	 *
	 * @since  1.0.0
	 */
	public function display_ngsurvey_settings() {
	    include_once NGSURVEY_PATH . 'admin/views/ngsurvey-admin-settings.php';
	}
	
	/**
	 * Triggers after a new survey is created to initiate survey elements. 
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function on_after_create_survey( $ID, $post ) {
	    global $wpdb;
	    
	    $count = $wpdb->get_var($wpdb->prepare(
	        "SELECT count(*) FROM {$wpdb->prefix}ngs_pages WHERE survey_id = %s AND sort_order = 1", $ID));

	    if(!$count) {
    	    $wpdb->insert(
    	        "{$wpdb->prefix}ngs_pages",
    	        array("survey_id" => $ID, "title" => __( "Page 1", 'ngsurvey' ), "sort_order" => 1), 
    	        array("%d", "%s","%d"));
	    }
	}
	
	/**
	 * Triggers after a survey is deleted to clean all other data.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function on_after_delete_survey( $ID, $post = null ) {
	    global $wpdb;
	    
	    // Check if this post is a survey
	    $count = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM {$wpdb->prefix}ngs_pages WHERE survey_id = %d AND sort_order = 1", $ID) );
	    
	    if($count) {
	        // Now clean the data
	        $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}ngs_questions WHERE id IN (SELECT question_id FROM {$wpdb->prefix}ngs_pages_questions_map WHERE page_id IN (SELECT id FROM {$wpdb->prefix}ngs_pages WHERE survey_id = %d))", $ID) );
	        $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}ngs_pages_questions_map WHERE page_id IN (SELECT id FROM {$wpdb->prefix}ngs_pages WHERE survey_id = %d)", $ID) );
	        $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}ngs_response_details WHERE response_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d)", $ID) );
	        $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}ngs_tracking WHERE post_type = 2 AND post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d)", $ID) );
	        $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d", $ID) );
	        $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}ngs_rules WHERE survey_id = %d", $ID) );
	        $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}ngs_pages WHERE survey_id = %d", $ID) );
	    }
	}
	
	/**
	 * Handles the ajax tasks and deligate them to appropriate controller.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function handle_ajax_task() {
	    // check nounce
	    check_ajax_referer( 'ngsa_nounce' );
	    
	    if ( ! current_user_can( 'manage_options' ) ) {
	    	$error = new WP_Error( '001', __( 'Unauthorised access', 'ngsurvey' ) );
	    	wp_send_json_error( $error );
	    }

	    // sanitize the task command
	    $command = sanitize_text_field( $_POST['task'] );
	    $command = strtolower( $command );
	    $command = preg_replace( '/[^a-z0-9_.]/', '', $command );
	    
	    // Get the controller and task names
	    list ($type, $task) = explode('.', $command);
	    
	    $sanitized_filename = sanitize_file_name( 'class-controller-' . $type . '.php' );
	    if( !file_exists( NGSURVEY_PATH . 'admin/controllers/' . $sanitized_filename ) ) {
	        $error = new WP_Error( '003', __( 'Unauthorised access', 'ngsurvey' ) );
	        wp_send_json_error( $error );
	    }
	    
	    require_once NGSURVEY_PATH . 'admin/controllers/' . $sanitized_filename;
	    $class = 'NgSurvey_Controller_' . ucfirst($type);
	    $controller = new $class( array(
	        'plugin_name' => $this->plugin_name,
	        'version' => $this->version,
	        'title' => __( ucfirst($type), 'ngsurvey' )
	    ) );
	    
	    call_user_func( array( $controller, $task ) );
	}
	
	/**
	 * Maps the survey capabilities to the user groups
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function map_survey_capabilities() {
	    $role = get_role( 'administrator' );
	    $capabilities = $this->compile_post_type_capabilities( 'survey', 'surveys' );
	    
	    foreach ($capabilities as $capability) {
	        $role->add_cap( $capability );
	    }
	}
	
	/**
	 * Unmaps the survey capabilities to the user groups
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function unmap_survey_capabilities() {
	    $role = get_role( 'administrator' );
	    $capabilities = $this->compile_post_type_capabilities( 'survey', 'surveys' );
	    
	    foreach ($capabilities as $capability) {
	        $role->remove_cap( $capability );
	    }
	}
	
	/**
	 * Adds columns to the surveys listing grid
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_survey_list_columns( $columns ) {
	    $columns = array(
	        'cb'        => $columns['cb'],
	        'title'     => $columns['title'],
	        'shortcode' => __( 'Shortcode', 'ngsurvey' ),
	        'author'    => $columns['author'],
	        'date'      => $columns['date'],
	    );
	    
	    return $columns;
	}
	
	/**
	 * Renders the questions column in the surveys listing page
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function render_survey_shortcode_column( $column, $post_id ) {
	    
	    if($column == 'shortcode') {
	        echo  '[ngsurvey id="' . (int) $post_id . '"]';
	    }
	}
	
	/**
	 * Adds action buttons for survey custom post type
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function modify_list_row_actions( $actions, $post ) {

	    // Check for your post type.
	    if ( $post->post_type == $this->cpt_name && current_user_can( 'edit_survey', $post->ID ) ) {
	        
	        // Build your links URL.
	        $edit_questions_url = admin_url( 'edit.php?post_type='.$this->cpt_name.'&page=edit_questions&post='. $post->ID );
	        $view_reports_url = admin_url( 'edit.php?post_type='.$this->cpt_name.'&page=view_reports&post='. $post->ID );
	        
            // Include a nonce in this link
	        $edit_questions_link = wp_nonce_url($edit_questions_url, 'edit_questions_nonce' );
	        $view_reports_link = wp_nonce_url($view_reports_url, 'view_reports_nonce' );
            
            // Add the new Copy quick link.
            $actions = 
               array_slice( $actions, 0, 1, true ) + 
               array( 'ngquestions' => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $edit_questions_link ), __ ( 'Add Questions', 'ngsurvey' ) ) ) + 
               array( 'ngreports' => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $view_reports_link ), __ ( 'View Reports', 'ngsurvey' ) ) ) +
               array_slice($actions, 1, count($actions) - 1, true);
	    }
	    
	    return $actions;
	}

	/**
	 * Adds the support for custom query parameters of the survey system
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function add_query_vars( $qvars ) {
	    $qvars[] = 'skey';
	    return $qvars;
	}
	
	/**
	 * Gets the survey type capabilities
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function compile_post_type_capabilities( $singular = 'survey', $plural = 'surveys' ) {
	    return array(
	        'edit_post'              => "edit_$singular",
	        'read_post'              => "read_$singular",
	        'delete_post'            => "delete_$singular",
	        'edit_posts'             => "edit_$plural",
	        'edit_others_posts'      => "edit_others_$plural",
	        'publish_posts'          => "publish_$plural",
	        'read_private_posts'     => "read_private_$plural",
	        'read'                   => "read",
	        'delete_posts'           => "delete_$plural",
	        'delete_private_posts'   => "delete_private_$plural",
	        'delete_published_posts' => "delete_published_$plural",
	        'delete_others_posts'    => "delete_others_$plural",
	        'edit_private_posts'     => "edit_private_$plural",
	        'edit_published_posts'   => "edit_published_$plural",
	        'create_posts'           => "edit_$plural",
	    );
	}
}
