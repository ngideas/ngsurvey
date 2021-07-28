<?php
/**
 * The file that defines the base class
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
 * The NgSurvey base class.
 *
 * This is used to define controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
abstract class NgSurvey_Base {

	/**
	 * The unique identifier of this plugin. Must on lowercase alphanumeric [a-z0-9] and no special characters.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $type    The string used to uniquely identify this plugin.
	 */
	public $name = null;
	
	/**
	 * The title of this extension.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $type    The title of the plugin
	 */
	public $title = null;
	
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	public $version;
	
	/**
	 * The template loader object.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $template    The template loader object.
	 */
	public $template;
	
	/**
	 * The full path the plugin extension file
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_file    The plugin file.
	 */
	public $plugin_file;
	
	/**
	 * Define the base controller of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $config = array() ) {
	    if(!empty($config[ 'name' ])) {
	        
	        $this->name = $config[ 'name' ];
	    }
	    
	    if(!empty($config[ 'title' ])) {
	        
	        $this->title = $config[ 'title' ];
	    }
	    
	    if(!empty($config[ 'version' ])) {
	        
	        $this->version = $config[ 'version' ];
	    }
	    
	    if(!empty($config[ 'template' ])) {
	        
	        $this->template = $config[ 'template' ];
	    } else {
	        $this->template = new NgSurvey_Template_Loader();
	    }
	    
	    if(!empty($config[ 'plugin_file' ])) {
	        
	        $this->plugin_file = $config[ 'plugin_file' ];
	    }
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
	 * Enqueue the plugin JavaScript file to the parent
	 *
	 * @param array $scripts the combined scripts
	 */
	public function enqueue_admin_scripts( $scripts, $type ) {
	    $fileName = (new ReflectionClass(static::class))->getFileName();
	    
	    if( !file_exists( trailingslashit( plugin_dir_path( dirname( $fileName ) ) ) . 'media/js/ngsurvey-'.$type.'-'.$this->name.'.js' ) ) {
	        return $scripts;
	    }
	    
	    $scripts[] = [
	        'handle'    => 'ngs-' . $type . '-' . $this->name,
	        'url'       => trailingslashit( plugin_dir_url( dirname( $fileName ) ) ) . 'media/js/ngsurvey-'.$type.'-'.$this->name.'.js',
	        'file'      => trailingslashit( plugin_dir_path( dirname( $fileName ) ) ) . 'media/js/ngsurvey-'.$type.'-'.$this->name.'.js',
	        'version'   => $this->version
	    ];
	    
	    return $scripts;
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
}
