<?php
/**
 * The file that defines the ajax controller class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
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

/**
 * The ajax controller class.
 *
 * This is used to define ajax controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Ajax extends NgSurvey_Controller {

	/**
	 * Define the questions controller of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($config = array()) {
	    parent::__construct($config);
	}

	/**
	 * Executes the ajax task by calling the dependent extensions and sends the data out.
	 * 
	 * @since 1.0.0
	 */
    public function execute() {
        $data      = null;
        $operation = isset( $_POST[ 'operation' ] ) ? sanitize_text_field( $_POST[ 'operation' ] ) : '';
        $survey_id = isset( $_POST[ 'ngform' ][ 'sid' ] ) ? (int) $_POST[ 'ngform' ][ 'sid' ] : 0;
        
        if( empty( $operation ) ) {
            $this->raise_error();
        }
        
        // Check user authorization
        $this->authorise( $survey_id );

        /*
         * Apply the filter to get the data from the implementing extensions and push it to the caller.
         * By this filter, extensions can execute their custom actions and get the data they need.
         * By reaching this method means all security is passed by using NgSurvey nounce. 
         * 
         * The implementing functions make sure to exectute only intended operation. 
         * Send a operation request parameter and validate it before applying operation on the data. 
         * 
         * @since 1.0.0
         */
        $data = apply_filters( 'ngsurvey_ajax', $data, $operation );
        
        wp_send_json_success( $data );
    }
}
