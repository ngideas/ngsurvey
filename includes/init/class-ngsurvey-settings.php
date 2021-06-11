<?php
/**
 * The file that defines the NgSurvey settings 
 *
 * Defines the settings class by extending the NgSurvey Settings API.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/controllers
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'NgSurvey_Settings', false ) ):

/**
 * The NgSurvey settings class.
 *
 * This is used to define NgSurvey settings class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Settings extends NgSurvey_Settings_API {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
		    'id' => 'general',
		    'title' => __( 'General', 'ngsurvey' ),
		    'form_fields' => $this->get_settings()
		) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters(
			'ngsurvey_general_settings',
		    array(
		    	"survey_url_base" => array(
		    		"title" => __( "Survey Permalink Base", 'ngsurvey' ),
		    		"type" => "text",
		    		"sanitize_callback" => "sanitize_text_field",
		    		"description" => __( "Enter the base of the survey permalinks. Avoid all special characters. Default: survey", 'ngsurvey' ),
		    		"default" => "survey"
		    	),
		    	"cateogry_url_base" => array(
		    		"title" => __( "Category Permalink Base", 'ngsurvey' ),
		    		"type" => "text",
		    		"sanitize_callback" => "sanitize_text_field",
		    		"description" => __( "Enter the base of the survey category permalinks. Avoid all special characters. Default: surveys", 'ngsurvey' ),
		    		"default" => "surveys"
		    	),
		        "load_bootstrap_js" => array(
    			    "title" => __( "Load Bootstrap 5 JavaScript", 'ngsurvey' ),
    			    "type" => "select",
    			    "description" => __( "Disable this option if your site theme is already loading Bootstrap v5 JavaScript library.", 'ngsurvey' ),
    			    "default" => "1",
    			    "options" => array(
    			        1 => __( "Yes", 'ngsurvey' ),
    			        0 => __( "No, I have it", 'ngsurvey' )
    			    )
    		    ),
		        "load_bootstrap_css" => array (
    		        "title" => __( "Load Bootstrap 5 CSS", 'ngsurvey' ),
    			    "type" => "select",
    		        "description" => __( "Disable this option if your site theme is already loading Bootstrap v5 CSS library.", 'ngsurvey' ),
    			    "default" => "1",
    			    "options" => array(
    			        1 => __( "Yes", 'ngsurvey' ),
    			        0 => __( "No, I have it", 'ngsurvey' )
    			    )
    			),
		        "maps_service" => array(
    			    "title" => __( "Maps Service", 'ngsurvey' ),
    			    "type" => "select",
    			    "description" => __( "Select the default maps service to be used in the questionnaire or reports as applicable.", 'ngsurvey' ),
    			    "default" => "openstreetmap",
    			    "options" => array(
    			        "openstreetmap" => __( "OpenStreetMap", 'ngsurvey' ),
    			        "googlemaps" => __( "Google Maps", 'ngsurvey' ),
    		        )
    			),
		        "google_maps_api_key" => array(
    			    "title" => __( "Google Maps API Key", 'ngsurvey' ),
    			    "type" => "text",
    			    "sanitize_callback" => "sanitize_text_field",
    			    "description" => __( "Enter the Google Maps API key to use Location type questions. The API should have permissions for Google Maps JavaScript API, Geocoding API and Google Maps Places API.", 'ngsurvey' ),
    			    "default" => ""
    			)
            )
		);

		return apply_filters( 'ngsurvey_get_settings_' . $this->id, $settings );
	}
}
endif;

return new NgSurvey_Settings();
