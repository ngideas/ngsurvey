<?php
/**
 * The file that defines the page header question type class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/extensions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'NgSurvey_Header_Question', false ) ):

/**
 * The survey page header question type class.
 *
 * This is used to define page header question type class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Header_Question extends NgSurvey_Question {
    
    /**
     * Define the base question type functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct( $config = array() ) {
        
        $config = array_merge( $config, array(
            'name'    => 'header',
            'group'   => 'special',
            'icon'    => 'dashicons dashicons-admin-post',
            'title'   => __( 'Page Header', 'ngsurvey' ),
            'options' => array(),
            'template_prefix' => 'questions/',
        ) );
        
        parent::__construct( $config );
    }
    
    /**
     * The function to filter the response data and return the array of rows to save into database.
     *
     * @since    1.0.0
     * @access   public
	 * @var      array $filtered_data the filtered data returned to caller
	 * @var      stdClass $question the question object
     * 
     * @return   array $filtered_data the filtered response data
     */
    public function filter_response_data ( $filtered_data, $question ) {
        return $filtered_data;
    }
}

endif;

return new NgSurvey_Header_Question();