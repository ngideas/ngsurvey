<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/admin
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/admin
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey_Admin {

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
	 * Holds the temporary setting group when displaying plugin settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var string $setting_group the setting group currently iterating over
	 */
	private $setting_groups;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook, $skipCheck = false ) {
	    global $typenow;
	    if( $typenow != NGSURVEY_CPT_NAME && !$skipCheck ) {
	        return;
	    }

	    $plugin_styles = array();
	    $handles = array();
	    
	    $styles = array(
	        'sweetalert2'      => NGSURVEY_URL . 'assets/vendor/sweetalert2/sweetalert2-theme.css',
	        'datetimepicker'   => NGSURVEY_URL . 'assets/vendor/datetimepicker/datetimepicker.css',
	        'select2'          => NGSURVEY_URL . 'assets/vendor/select2/select2.css',
	        'leaflet'          => NGSURVEY_URL . 'assets/vendor/leaflet/leaflet.css',
	        'MarkerCluster'    => NGSURVEY_URL . 'assets/vendor/markercluster/MarkerCluster.css',
	    	'MarkerCluster2'   => NGSURVEY_URL . 'assets/vendor/markercluster/MarkerCluster.Default.css',
	        'query-builder'    => NGSURVEY_URL . 'assets/vendor/querybuilder/query-builder.default.css',
	        'datatables'       => NGSURVEY_URL . 'assets/vendor/datatables/datatables.min.css',
	        'bootstrap5'       => NGSURVEY_URL . 'assets/vendor/bootstrap/bootstrap.min.css'
	    );
	    
	    /**
	     * Apply filter to get the scripts from all plugins which can ingect their stylesheets
	     * The plugins should append their styles as an array with attributes handle, url and version
	     *
	     * @param array $plugin_styles the array of stylesheets that needs to be applied
	     * @param string $hook the current url hook
	     *
	     * @return array the processed array of the $plugin_styles
	     */
	    $plugin_styles = apply_filters( 'ngsurvey_enqueue_admin_styles', $plugin_styles, $hook );
	    
	    foreach ( $plugin_styles as $plugin_style ) {
	        $styles[ $plugin_style[ 'handle' ] ] = $plugin_style[ 'url' ];
	    }
	    
	    $styles[ 'ngsurvey-admin' ] = NGSURVEY_URL . 'assets/admin/css/ngsurvey-admin.css';
	    
	    foreach ( $styles as $handle => $style ) {
	        wp_enqueue_style( $handle, $style, $handles, $this->version, 'all' );
	        $handles[] = $handle;
	    }
	    
	    return $handles;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook, $skipCheck = false ) {
	    global $typenow;

	    if( $typenow != NGSURVEY_CPT_NAME && !$skipCheck ) {
	        return;
	    }

	    wp_enqueue_script( 'jquery-ui-sortable', '', array( 'jquery' ), $this->version, false );
	    wp_enqueue_script( 'moment', '', array( 'jquery' ), $this->version, false );

	    $plugin_scripts = array();
	    $scripts = array(
	        'select2'          => NGSURVEY_URL . 'assets/vendor/select2/select2.min.js',
	        'datetimepicker'   => NGSURVEY_URL . 'assets/vendor/datetimepicker/datetimepicker.min.js',
	        'sweetalert2'      => NGSURVEY_URL . 'assets/vendor/sweetalert2/sweetalert2.min.js',
	        'datatables'       => NGSURVEY_URL . 'assets/vendor/datatables/datatables.min.js',
	        'chartjs'          => NGSURVEY_URL . 'assets/vendor/chartjs/chart.min.js',
	        'chartjs-moment'   => NGSURVEY_URL . 'assets/vendor/chartjs-adapter-moment/chartjs-adapter-moment.min.js',
	        'chartjs-geo-esm'  => NGSURVEY_URL . 'assets/vendor/chartjs-geo/Chart.Geo.esm.js',
	        'chartjs-geo'      => NGSURVEY_URL . 'assets/vendor/chartjs-geo/index.umd.min.js',
	        'leaflet'          => NGSURVEY_URL . 'assets/vendor/leaflet/leaflet.js',
	        'markercluster'    => NGSURVEY_URL . 'assets/vendor/markercluster/leaflet.markercluster.js',
	        'query-builder'    => NGSURVEY_URL . 'assets/vendor/querybuilder/query-builder.standalone.min.js',
	        'bootstrap5'       => NGSURVEY_URL . 'assets/vendor/bootstrap/bootstrap.bundle.min.js',
	        'ngsurvey-common'  => NGSURVEY_URL . 'assets/admin/js/common.js',
	        'ngsurvey-core-qns'=> NGSURVEY_URL . 'assets/admin/js/questions.js',
	    );
	    
	    /**
	     * Apply filter to get the scripts from all plugins which can ingect their scripts
	     * The plugins should append their scripts as an array with attributes handle, url and version
	     * 
	     * @param array $plugin_scripts the array of scripts that needs to be applied
	     * @param string $hook the current url hook
	     * 
	     * @return array the processed array of the $plugin_scripts
	     */
        $plugin_scripts = apply_filters( 'ngsurvey_enqueue_admin_scripts', $plugin_scripts, $hook );
        
	    foreach ( $plugin_scripts as $plugin_script ) {
	        $scripts[ $plugin_script[ 'handle' ] ] = $plugin_script[ 'url' ];
	    }
	    
	    $parts = explode( '_', $hook, 3);
	    $pagenow = end( $parts );
	    
	    switch ( $pagenow ) {
	        case 'edit_questions':
	            $scripts[ 'ngsurvey-form' ] = NGSURVEY_URL . 'assets/admin/js/form.js';
	            break;

	        case 'view_reports':
	            $scripts[ 'ngsurvey-reports' ] = NGSURVEY_URL . 'assets/admin/js/reports.js';
	            break;

	        case 'ngsurvey_extensions':
	            $scripts[ 'ngsurvey-extensions' ] = NGSURVEY_URL . 'assets/admin/js/extensions.js';
	            break;

	        case 'ngsurvey_settings':
	            $scripts[ 'ngsurvey-settings' ] = NGSURVEY_URL . 'assets/admin/js/settings.js';
	            break;
	            
	    }
	    
	    $handles = array( 'moment', 'jquery' );
	    foreach ( $scripts as $handle => $script ) {
	        wp_enqueue_script( $handle, $script, $handles, $this->version, false );
	        $handles[] = $handle;
	    }
	    
	    /**
	     * Apply filter to allow the plugins enqueue any scripts before the application script is loaded.
	     * 
	     * @param handles list of handles already enqueued
	     * @param hook the current hook of the page
	     * 
	     * @return handles the list of handles after
	     */
	    $handles = apply_filters( 'ngsurvey_post_enqueue_admin_scripts', $handles, $hook );
	    
	    wp_enqueue_script( $this->plugin_name.'_app', NGSURVEY_URL . 'assets/admin/js/app.js', $handles, $this->version, false );
	    
	    // Register ajax
	    $ng_nonce = wp_create_nonce( 'ngsa_nounce' );
	    wp_localize_script( $this->plugin_name.'_app', 'ng_ajax', array(
	        'ajax_url'     => admin_url( 'admin-ajax.php' ),
	        'assets_url'   => NGSURVEY_ASSETS,
	        'nonce'        => $ng_nonce, 
	    ) );

	    return $handles;
	}

	/**
	 * Adds the custom header type for defining the NgSurvey plugins as Extension or Question
	 *
	 * @param array $headers the headers that needs to be altered
	 * @return array headers added with Plugin Type
	 */
	public function add_ngsurvey_plugin_header( $headers ) {
	    $headers[] = 'NgSurvey Type';
	    $headers[] = 'NgSurvey ID';
	    $headers[] = 'Update Server';
	    
	    return $headers;
	}

	/**
	 * Check updates for the NgSurvey extensions not hosted on wordpress.org.
	 * This function uses Update Server header of the plugin file to determine if the update information is added or not.
	 * NgSurvey does not use this function, rather updates directly from the wordpress.org plugins site.
	 *
	 * @param object $transient
	 */
	public function check_for_updates( $transient ) {
		$plugins = get_plugins();
		$licenses = get_option( NGSURVEY_LICENSES, array() );
		
		foreach ( $plugins as $plugin ) {
			if( !isset( $plugin[ 'Update Server' ] ) || !in_array( $plugin[ 'Update Server' ], array( 'NgIdeas' ) ) ) {
				continue;
			}
			
			$product_id	= $plugin[ 'NgSurvey ID' ];
			$plugin_id	= $product_id.'/'.$product_id.'.php';
			$email		= get_bloginfo('admin_email');
			$key		= json_encode( $licenses );
			
			$autoupdate = new NgSurvey_AutoUpdate($product_id, $plugin_id, $plugin[ 'Version' ], $email, $key);
			$transient = $autoupdate->check_for_updates( $transient );
		}

		return $transient;
	}

	/**
	 * Sets the information to plugin info dialog
	 *
	 * @param object $def
	 * @param string $action
	 * @param object $args
	 */
	public function set_plugin_info( $def, $action, $args ) {
		if( !isset( $args->slug ) ) {
			return $def;
		}

		$autoupdate = new NgSurvey_AutoUpdate( $args->slug, $args->slug.'/'.$args->slug.'.php', $this->version, get_bloginfo('admin_email'), '' );
		return $autoupdate->plugins_api_call( $def, $action, $args );
	}

	/**
	 * NgSurvey update routines.
	 * 
	 * @param \WP_Upgrader $upgrader
	 * @param array $hook_extra
	 */
	function update_ngsurvey( $upgrader, array $hook_extra ) {
	    require_once plugin_dir_path( __DIR__ ) . 'includes/init/class-ngsurvey-activator.php';
	    NgSurvey_Activator::updateProcessComplete( $upgrader, $hook_extra );
	}
	
	/**
	 * Render the options page for plugin
	 *
	 * @since  1.0.0
	 */
	public function display_ngsurvey_settings() {
	    include_once 'views/ngsurvey-admin-settings.php';
	}
}
