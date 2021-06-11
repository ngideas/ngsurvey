<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    NgSurvey
 * @subpackage NgSurvey/includes
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      NgSurvey_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		$this->load_dependencies();
		$this->plugin_name = NGSURVEY_MAIN_NAME;
		$this->version = NGSURVEY_VERSION;
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - NgSurvey_Loader. Orchestrates the hooks of the plugin.
	 * - NgSurvey_i18n. Defines internationalization functionality.
	 * - NgSurvey_Admin. Defines all hooks for the admin area.
	 * - NgSurvey_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
	    
	    /**
	     * The class responsible for loading common dependancies of the core plugin.
	     */
	    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/init/class-ngsurvey-dependancies.php';

		$this->loader = new NgSurvey_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		// Load default question types
		$this->loader->add_action( 'plugins_loaded', $this, 'init_question_types', 20 );
		
		// Load survey CPT
	    $plugin_cpt =  new NgSurvey_CPT( NGSURVEY_CPT_NAME, $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $plugin_cpt, 'define_survey_post_type' );
		$this->loader->add_action( 'init', $plugin_cpt, 'define_survey_taxonomy' );
		
		// Add survey_author capabilities
		$this->loader->add_action( 'init', $plugin_cpt, 'map_survey_capabilities' );
		$this->loader->add_action( 'after_switch_theme', $plugin_cpt, 'map_survey_capabilities' );
		
		// Remove survey_author capabilities
		$this->loader->add_action( 'switch_theme', $plugin_cpt, 'unmap_survey_capabilities' );
		
		// Add admin menu
		$this->loader->add_action( 'admin_menu', $plugin_cpt, 'add_menu_placeholders' );
		
		// Add shortcode
		$this->loader->add_action( 'manage_ngsurvey_posts_custom_column', $plugin_cpt, 'render_survey_shortcode_column', 10, 2 );
		
		// Add shortcode column header in list page
		$this->loader->add_filter( 'manage_ngsurvey_posts_columns', $plugin_cpt, 'add_survey_list_columns' );
		
		// Add shortcode column in listing page
		$this->loader->add_filter( 'post_row_actions', $plugin_cpt, 'modify_list_row_actions', 10, 2 );
		
		// Allow skey variable accepted internally by wordpress as a parameter in URLs
		$this->loader->add_filter( 'query_vars', $plugin_cpt, 'add_query_vars', 10, 2 );
		
		// Customize the messages shown on survey edit page for custom post type
		$this->loader->add_filter( 'post_updated_messages', $plugin_cpt, 'survey_updated_messages' );
		
		// Add plugin shortcode
		add_shortcode( 'ngsurvey', array( $plugin_cpt, 'handle_shortcode' ) );
		
		// Initialize the survey whenever a new post is created
		$this->loader->add_action( 'draft_ngsurvey', $plugin_cpt, 'on_after_create_survey', 10, 2 );
		$this->loader->add_action( 'publish_ngsurvey', $plugin_cpt, 'on_after_create_survey', 10, 2 );
		$this->loader->add_action( 'after_delete_post', $plugin_cpt, 'on_after_delete_survey', 10 );
		
		// Attach the ajax handler
		$this->loader->add_action( 'wp_ajax_ngsa_ajax_handler', $plugin_cpt, 'handle_ajax_task' );
		
		// Initialize survey metabox for storing survey settings
		$plugin_metabox = new NgSurvey_Options( NGSURVEY_CPT_NAME, $this->get_plugin_name() );
		$this->loader->add_action( 'add_meta_boxes', $plugin_metabox, 'register_survey_options' );
		$this->loader->add_action( 'save_post', $plugin_metabox, 'save_survey_options' );
		$this->loader->add_action( 'save_post', $plugin_metabox, 'save_revisions' );
		$this->loader->add_action( 'wp_restore_post_revision', $plugin_metabox, 'restore_revisions' );
		$this->loader->add_filter( '_wp_post_revision_fields', $plugin_metabox, 'get_revisions_fields', 10, 2 );
		$this->loader->add_filter( '_wp_post_revision_field_my_meta', $plugin_metabox, 'display_revisions_fields', 10, 2 );

		// Load plugin scripts
		$plugin_admin = new NgSurvey_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		// Post upgrade actions
		$this->loader->add_action( 'upgrader_process_complete', $plugin_admin, 'update_ngsurvey', 10, 2);
		
		// Check extensions updates
		$this->loader->add_filter( 'pre_set_site_transient_update_plugins', $plugin_admin, 'check_for_updates' );
		$this->loader->add_filter( 'plugins_api', $plugin_admin, 'set_plugin_info', 10, 3 );
		
		// Apply filter to load NgSurvey plugin header
		$this->loader->add_filter( 'extra_plugin_headers', $plugin_admin, 'add_ngsurvey_plugin_header' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new NgSurvey_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		// Front-end UI customization
		$this->loader->add_filter( 'the_content', $plugin_public, 'single_survey_content' );
		
		// Attach the ajax handler
		$this->loader->add_action( 'wp_ajax_ngsurvey_ajax_handler', $plugin_public, 'handle_ajax_task' );
		$this->loader->add_action( 'wp_ajax_nopriv_ngsurvey_ajax_handler', $plugin_public, 'handle_ajax_task' );
	
	}
	
	/**
	 * Loads the default question types
	 *
	 * @since 1.0.0
	 */
	public function init_question_types() {
	    $classes   = array();
	    $classes[ 'NgSurvey_Header_Question' ]	= include NGSURVEY_PATH . 'includes/questions/class-header-question.php';
	    $classes[ 'NgSurvey_Choice_Question' ]	= include NGSURVEY_PATH . 'includes/questions/class-choice-question.php';
	    $classes[ 'NgSurvey_Textbox_Question' ] = include NGSURVEY_PATH . 'includes/questions/class-textbox-question.php';
	    
	    /**
	     * Apply filters to get the supported question types.
	     * The supported plugins must return a class which is extended from <code>NgSurvey_Question</code>
	     * 
	     * @return array the list of classes applied and then returned
	     */
	    $classes = apply_filters( 'ngsurvey_question_types_classes', $classes );

	    foreach ( $classes as $name => $plugin ) {
	        // Add filter to inject the question type to list of available question types
	    	add_filter( 'ngsurvey_fetch_question_types', array( $plugin, 'get_type' ) );
	        
	        // Add action to save the question form
	    	add_action( 'ngsurvey_save_question_form', array( $plugin, 'save_form' ), 10, 2 );
	        
	    	// Add action to duplicate the question
	    	add_action( 'ngsurvey_copy_question', array( $plugin, 'copy_question' ), 10, 3 );
	    	
	    	// Add action to handle custom form operation
	    	add_action( 'ngsurvey_custom_form_action', array( $plugin, 'handle_custom' ), 10, 2 );
	        
	        // Add filter to render the response form when showing single survey
	    	add_filter( 'ngsurvey_response_form', array( $plugin, 'get_display' ) );
	        
	        // Add filter to inject the question form for adding new question/edit question
	    	add_filter( 'ngsurvey_fetch_question_form', array( $plugin, 'get_form' ) );
	        
	        // Add filter to inject conditional rules of the question type
	    	add_filter( 'ngsurvey_conditional_rules', array( $plugin, 'get_rules' ) );
	        
	        // Add filter to show response details of a user response
	    	add_filter( 'ngsurvey_survey_results', array( $plugin, 'get_results' ) );
	        
	        // Add filter to get the consolidated report of this question type
	    	add_filter( 'ngsurvey_consolidated_report', array( $plugin, 'get_reports' ) );
	        
	        // Add filter to validate the user response
	    	add_filter( 'ngsurvey_validate_response', array( $plugin, 'validate' ), 10, 2 );
	        
	        // Add filter to return the user response data that should be saved to data
	    	add_filter( 'ngsurvey_filter_user_responses', array( $plugin, 'filter_response_data' ), 10, 3 );
	    }
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    NgSurvey_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
