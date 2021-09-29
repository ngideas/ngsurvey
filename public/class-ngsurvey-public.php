<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/public
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/public
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	
	/**
	 * Registers the template to display the survey
	 *
	 * @since    1.0.0
	 * @param    string      The single post display template
	 */
	public function single_survey_content( $content ) {
	    
	    $survey = get_post();

	    if ( is_singular() && in_the_loop() && is_main_query() && $survey->post_type == NGSURVEY_CPT_NAME ) {
	        
	        require_once NGSURVEY_PATH . 'public/controllers/class-controller-survey.php';
	        $controller = new NgSurvey_Controller_Survey( array(
	            'plugin_name' => $this->plugin_name,
	            'version' => $this->version,
	            'title' => __( 'Survey', 'ngsurvey' )
	        ) ); 

	        ob_start();
	        $controller->display();
	        $survey_content = ob_get_clean();
	        
	        $content = $content . $survey_content;
	    }
	    
	    return $content;
	}
	
	/**
	 * Handles the ajax tasks and deligate them to appropriate controller.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function handle_ajax_task() {
	    
	    // check nounce
	    check_ajax_referer( 'ngsurvey_nounce' );
	    
	    // sanitize the task command
	    $command = sanitize_text_field( $_POST['task'] );
	    $command = strtolower( $command );
	    $command = preg_replace( '/[^a-z0-9_.]/', '', $command );
	    
	    // Get the controller and task names
	    list ($type, $task) = explode('.', $command);
	    
	    $sanitized_filename = sanitize_file_name( 'class-controller-' . $type . '.php' );
	    if( !file_exists( NGSURVEY_PATH . 'public/controllers/' . $sanitized_filename ) ) {
	        $error = new WP_Error( '002', __( 'Unauthorised access', 'ngsurvey' ) );
	        wp_send_json_error( $error );
	    }

	    require_once NGSURVEY_PATH . 'public/controllers/' . $sanitized_filename;
	    $class = 'NgSurvey_Controller_' . ucfirst($type);
	    $controller = new $class(array(
	        'plugin_name' => $this->plugin_name,
	        'version' => $this->version,
	        'title' => __( ucfirst($type), 'ngsurvey' )
	    ));
	    
	    try {
	        call_user_func(array($controller, $task));
	    } catch (Exception $e) {
	        wp_send_json_error( new WP_Error( 500, __( $e->getMessage(), 'ngsurvey' ) ) );
	    }
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook = null ) {
	    global $post;
	    
	    $render_styles = get_post_type() == NGSURVEY_CPT_NAME || ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ngsurvey') );
	    
	    /**
	     * Applies the filter to determine if the NgSurvey styles can be rendered.
	     * By default the styles are rendered only on the NgSurvey pages which can be overridden using this filter.
	     * 
	     * @param boolean $render_styles
	     * @since 1.0.6
	     */
	    $render_styles = apply_filters( 'ngsurvey_render_styles', $render_styles );

	    if( !$render_styles ) {
	        return;
	    }
	    
	    wp_enqueue_style( 'dashicons' );
	    $settings = include NGSURVEY_PATH . 'includes/init/class-ngsurvey-settings.php';
	    
	    $plugin_styles = array();
	    $styles = array(
	        'sweetalert2'      => NGSURVEY_URL . 'assets/vendor/sweetalert2/sweetalert2-theme.css',
	        'datetimepicker'   => NGSURVEY_URL . 'assets/vendor/datetimepicker/datetimepicker.css',
	        'select2'          => NGSURVEY_URL . 'assets/vendor/select2/select2.css',
	        'leaflet'          => NGSURVEY_URL . 'assets/vendor/leaflet/leaflet.css',
	        'geosearch'        => NGSURVEY_URL . 'assets/vendor/leaflet-geosearch/geosearch.css'
	    );
	    
	    if( $settings->get_option( 'load_bootstrap_css' ) ) {
	        $styles[ 'bootstrap5' ] = NGSURVEY_URL . 'assets/vendor/bootstrap/bootstrap.min.css';
	    }
	    
	    /**
	     * Apply filter to get the styles from all plugins which can ingect their styles
	     * The plugins should append their styles as an array with attributes handle, url and version
	     *
	     * @param array $plugin_styles the array of styles that needs to be applied
	     * @param string $hook the current url hook
	     *
	     * @return array the processed array of the $plugin_styles
	     */
	    $plugin_styles = apply_filters( 'ngsurvey_enqueue_public_styles', $plugin_styles, $hook );
	    
	    foreach ( $plugin_styles as $plugin_style ) {
	    	$styles[ $plugin_style[ 'handle' ] ] = $plugin_style[ 'url' ];
	    }
	    
	    $styles[ 'ngsurvey' ] = NGSURVEY_URL . 'assets/public/css/ngsurvey-public.css';
	    $handles = array();
	    
	    foreach ( $styles as $handle => $style ) {
	        wp_enqueue_style( $handle, $style, $handles, $this->version, 'all' );
	        $handles[] = $handle;
	    }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook, $skipCheck = false ) {
	    global $post;

	    $render_scripts = get_post_type() == NGSURVEY_CPT_NAME || ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ngsurvey') );
	    
	    /**
	     * Applies the filter to determine if the NgSurvey scripts can be rendered.
	     * By default the scripts are rendered only on the NgSurvey pages which can be overridden using this filter.
	     *
	     * @param boolean $render_scripts
	     * @since 1.0.6
	     */
	    $render_scripts = apply_filters( 'ngsurvey_render_scripts', $render_scripts );
	    
	    if( !$render_scripts ) {
	        return;
	    }
	    
	    $settings = include NGSURVEY_PATH . 'includes/init/class-ngsurvey-settings.php';
	    wp_enqueue_script( 'jquery-ui-sortable', '', array( 'jquery' ), $this->version, false );
	    wp_enqueue_script( 'moment', '', array( 'jquery' ), $this->version, false );
	    $plugin_scripts = array();
	    
	    $scripts = array(
	        'validate'         => NGSURVEY_URL . 'assets/vendor/jquery-validate/jquery.validate.min.js',
	        'select2'          => NGSURVEY_URL . 'assets/vendor/select2/select2.min.js',
	        'datetimepicker'   => NGSURVEY_URL . 'assets/vendor/datetimepicker/datetimepicker.min.js',
	        'sweetalert2'      => NGSURVEY_URL . 'assets/vendor/sweetalert2/sweetalert2.min.js',
	        'leaflet'          => NGSURVEY_URL . 'assets/vendor/leaflet/leaflet.js',
	        'geosearch'        => NGSURVEY_URL . 'assets/vendor/leaflet-geosearch/geosearch.umd.js',
	    );
	    
	    if( $settings->get_option( 'load_bootstrap_js' ) ) {
	        $scripts[ 'bootstrap5' ] = NGSURVEY_URL . 'assets/vendor/bootstrap/bootstrap.bundle.min.js';
	    }
	    
	    $scripts[ 'ngsurvey-common' ] = NGSURVEY_URL . 'assets/admin/js/common.js';
	    
	    /**
	     * Apply filter to get the scripts from all plugins which can ingect their scripts
	     * The plugins should append their scripts as an array with attributes handle, url and version
	     *
	     * @param array $plugin_scripts the array of scripts that needs to be applied
	     * @param string $hook the current url hook
	     *
	     * @return array the processed array of the $plugin_scripts
	     */
	    $plugin_scripts = apply_filters( 'ngsurvey_enqueue_public_scripts', $plugin_scripts, $hook );
	    
	    $ajax_args = array(
	        'ajax_url' => admin_url( 'admin-ajax.php' ),
	        'assets_url' => NGSURVEY_ASSETS,
	        'nonce' => wp_create_nonce( 'ngsurvey_nounce' ),
	        'maps_provider' => $settings->get_option( 'maps_service' ),
	        'google_maps_key' => $settings->get_option( 'google_maps_api_key' )
	    );
	    
	    foreach ( $plugin_scripts as $plugin_script ) {
	        $scripts[ $plugin_script[ 'handle' ] ] = $plugin_script[ 'url' ];
	        
	        if( !empty( $plugin_script['args' ] ) ) {
	            $ajax_args = array_merge( $ajax_args, $plugin_script[ 'args' ] );
	        }
	    }
	    
	    $handles = array('jquery', 'jquery-ui-sortable', 'moment');
	    foreach ( $scripts as $handle => $script ) {
	        wp_enqueue_script( $handle, $script, $handles, $this->version, false );
	        $handles[] = $handle;
	    }
	    
	    wp_enqueue_script( 'ngsurvey-public', NGSURVEY_URL . 'assets/public/js/ngsurvey-public.js', $handles, $this->version, false );

	    // Register ajax
	    wp_localize_script( 'ngsurvey-public', 'ng_ajax', $ajax_args ); 
	}

}
