<?php
/**
 * The file that defines the base controller class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/abstracts
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The base controller class.
 *
 * This is used to define controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
abstract class NgSurvey_Controller extends NgSurvey_Base {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * Define the base controller of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct($config = array()) {
	    if(!empty($config[ 'plugin_name' ])) {
	        
	        $this->plugin_name = $config[ 'plugin_name' ];
	    }
	    
	    if(!empty($config[ 'version' ])) {
	        
	        $this->version = $config[ 'version' ];
	    }
	    
	    if(!empty($config[ 'page_title' ])) {
	        
	        $this->title = $config[ 'page_title' ];
	    }
	    
	    if(!empty($config[ 'template' ])) {
	        
	        $this->template = $config[ 'template' ];
	    } else {
	        $this->template = new NgSurvey_Template_Loader();
	    }
	    
	    parent::__construct( $config );
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
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * Creates the model with the name $type prefixed by $prefix
	 *  
	 * @param string $type name of the model
	 * @param string $prefix the class name prefix of the model 
	 * 
	 * @return boolean|object the model object on success, false otherwise
	 */
	public function get_model( $type = '', $prefix = 'NgSurvey_Model_' ) {
	    $type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
	    $modelClass = $prefix . ucfirst($type);

	    if ( ! class_exists( $modelClass) ) {

	        $reflector = new ReflectionClass(get_class($this));
	        $sanitized_filename = sanitize_file_name( 'class-model-'.$type.'.php' );
	        $filePath = dirname( dirname( $reflector->getFileName() ) ) . '/models/' . $sanitized_filename;

	        if( !file_exists($filePath) ) {
	            return false;
	        }
	        
	        require_once $filePath;
	        
	        if (!class_exists($modelClass))
	        {
	            return false;
	        }
	    }
	    
	    $config = array(
	        'plugin_name' => $this->plugin_name,
	        'version' => $this->version
	    );
	    
	    return new $modelClass($config);
	}
	
	/**
	 * Throws error and dies. Used in ajax response.
	 * 
	 * If no error code or message is provided, default unauthorised error will be thrown
	 */
	protected function raise_error( $code = '001', $message = 'Unauthorised access' ) {
	    $error = new WP_Error( $code, __( $message, 'ngsurvey' ) );
	    wp_send_json_error( $error );
	}
	
	/**
	 * Checks if the current user is authorized to access the survey operations, if not throws error
	 *
	 * @param int $survey_id the survey id
	 */
	protected function authorise( $survey_id ) {
	    if( current_user_can( 'edit_others_posts' ) ) {
	        return;
	    }

	    if( !current_user_can( 'edit_surveys' ) ) {
	        $this->raise_error();
	    }
	    
	    if( $survey_id ) {
    	    $survey = get_post( (int) $survey_id );
    	    $user = wp_get_current_user();
    	    
    	    if( empty( $survey ) || $survey->post_author != $user->ID ) {
    	        $this->raise_error();
    	    }
	    }
	}
}
