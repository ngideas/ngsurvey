<?php

/**
 * The file that defines the reports base class
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
 * The survey question base class.
 *
 * This is used to define base question class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
abstract class NgSurvey_Extension extends NgSurvey_Controller {

    /**
	 * Define the base question type functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct ( $config = array() ) {
	    
	    parent::__construct( $config );
	}
	
	/**
	 * Enqueue the plugin JavaScript file to the parent
	 *
	 * @param array $scripts the combined scripts
	 */
	public function enqueue_admin_scripts( $scripts, $hook, $type = 'extension' ) {
	    return parent::enqueue_admin_scripts( $scripts, $type );
	}
	
	/**
	 * Loads the extension settings stored in settings.json file.
	 * 
	 * @param array $extensions the return array to which the settings are injected
	 * @return array settings array
	 */
	public function extension_settings( $extensions ) {
	    $fileName = (new ReflectionClass(static::class))->getFileName();
	    if( !file_exists( trailingslashit( plugin_dir_path( dirname( $fileName ) ) ) . 'settings.json' ) ) {
	        return $extensions;
	    }
	    
	    array_push( $extensions, array(
	        'name' => $this->name,
	        'title' => $this->title,
	        'settings' => trailingslashit( plugin_dir_path( dirname( $fileName ) ) ) . 'settings.json'
	    ));
	    
	    return $extensions;
	}
}
