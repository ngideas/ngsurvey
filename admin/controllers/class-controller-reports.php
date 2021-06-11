<?php
/**
 * The file that defines the questions controller class
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
 * The questions controller class.
 *
 * This is used to define questions controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Reports extends NgSurvey_Controller {

	/**
	 * Define the questions controller of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($config = array()) {
	    parent::__construct($config);
	}
	
	/**
	 * Renders the survey report
	 *
	 * @return void nothing
	 */
	public function display () {
	    $nonce = $_REQUEST['_wpnonce'];
	    if ( ! wp_verify_nonce( $nonce, 'view_reports_nonce' ) ) {
	        die( __( 'This page cannot be accessed directly.', 'ngsurvey' ) );
	    }
	    
	    // Check user authorization
	    $this->authorise( (int) $_REQUEST[ 'post' ] );

	    $survey                = get_post( (int) $_REQUEST[ 'post' ] );

	    // Build the final data and deligate it to the template response
	    $data = array();
	    $data[ 'item' ]        = $survey;
	    $data[ 'template' ]    = $this->template;
	    $reports_model         = $this->get_model( 'reports' );

	    $data[ 'datewise_responses' ]      = $reports_model->get_count_responses_by_date( $survey->ID );
	    $data[ 'countrywise_responses' ]   = $reports_model->get_count_response_by_country( $survey->ID );
	    $data[ 'finished_pending_count' ]  = $reports_model->get_pending_and_completed_count( $survey->ID );
	    $data[ 'locations_responses' ]     = $reports_model->get_count_response_by_locations( $survey->ID );
	    $data[ 'platforms_responses' ]     = $reports_model->get_count_response_by_platforms( $survey->ID );
	    $data[ 'browsers_responses' ]      = $reports_model->get_count_response_by_browsers( $survey->ID );
	    $data[ 'devices_responses' ]       = $reports_model->get_count_response_by_devices( $survey->ID );
	    $data[ 'tracking_stats' ]          = $reports_model->get_survey_tracking_stats( $survey->ID );
	    $data[ 'latest_responses' ]        = $reports_model->get_latest_responses( $survey->ID, 0, 6 );
	    
	    $this->template->set_template_data( $data )->get_template_part( 'admin/survey_reports' );
	}
	
	/**
	 * Renders the custom answers listing
	 */
	public function get_custom_answers() {
	    $survey_id         = (int) $_POST['ngform']['sid'];
	    $question_id       = (int) $_POST['ngform']['qid'];
	    $start             = (int) isset( $_POST[ 'start' ] ) ? $_POST[ 'start' ] : 0;
	    $length            = (int) isset( $_POST[ 'length' ] ) ? $_POST[ 'length' ] : 1000;
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $questions_model   = $this->get_model( 'questions' );
	    $question          = $questions_model->get_question( $question_id );

	    if( !$question ) {
	        $this->raise_error();
	    }
	    
	    if( $length > 1000 || $length < 0 ) {
	        $length = 15;
	    }

	    $responses_model   = $this->get_model( 'responses' );
	    $comments          = $responses_model->get_custom_answers( $survey_id, $question_id, $length, $start );

	    ob_start();
	    if( $this->template->get_template_part( 'reports/custom/' . $question->qtype, $question->qtype, null, false ) ) {
	        $this->template->set_template_data( array('custom_answers' => $comments) )->get_template_part( 'reports/custom/' . $question->qtype, $question->qtype );
	    } else {
	        $this->template->set_template_data( array('custom_answers' => $comments) )->get_template_part( 'admin/reports/custom/default' );
	    }
	    $response = ob_get_clean();
	    
	    wp_send_json_success( $response );
	}
}
