<?php
/**
 * The file that defines the consolidated report controller class
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
 * The consolidated report controller class.
 *
 * This is used to define consolidated report controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Consolidated extends NgSurvey_Controller {

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
	    $survey_id         = (int) $_POST[ 'ngform' ][ 'sid' ];
	    $survey            = get_post( $survey_id ); 
	    
	    if( !$survey ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $questions_model   = $this->get_model( 'questions' );
	    $question_data     = $questions_model->get_questions( $survey_id, false );
	    $responses_model   = $this->get_model( 'responses' );
	    $responses_data    = $responses_model->get_responses_report( $survey_id );
	    $question_stats    = $responses_model->get_total_responses_by_question( $survey_id );
	    $question_types    = array();
	    
	    if ( empty( $question_data ) ) {
	        $question_data = array();
	    }
	    
	    /*
	     * Get all available question types to display the drag and drop types on question form.
	     * Extensions supporting the question types should join this filter type and append their question type to the return value.
	     * The filter expect an object with atleast 5 attributes added to the return value as follows:
	     * 1. name, e.g. myfirstextension
	     * 2. group, e.g. choice, grid or special
	     * 3. icon, e.g. dashicons dashicons-yes-alt
	     * 4. title e.g. My First Extension
	     * 5. options e.g. array()
	     *
	     * e.g. format:
	     * { 'name' => 'myfirstextension', 'group' => 'choice', 'icon' => 'dashicons dashicons-yes-alt', 'title' => 'My First Extension', 'options' => $options }
	     */
	    $question_types = apply_filters( 'ngsurvey_fetch_question_types', $question_types );

	    $questions = array();
	    foreach ( $question_data as $question ) {
	        if( !$question->params->get( 'show_in_report' ) ) {
	            continue;
	        }
	        
	        foreach ( $question_types as $question_type ) {
	            if( $question->qtype == $question_type->name ) {
	                $question->question_type = $question_type;
	                break;
	            }
	        }
	        
	        // We don't need the junk data for which no question type found
	        if( !isset( $question->question_type ) ) {
	            continue;
	        }
	        
	        $question->responses = array();
	        if( !empty( $responses_data ) ) {
	            foreach ( $responses_data as $stat ) {
	                if( $stat->page_id == $question->page_id && $stat->question_id == $question->id ) {
	                    $question->responses[] = $stat;
	                }
	            }
	        }
	        
	        // Question specifics: Custom Answers
	        $question->custom_answers = $responses_model->get_custom_answers($survey_id, $question->id, 5);
	        $question->num_responses = isset( $question_stats[ $question->id ] ) ? $question_stats[ $question->id ]->responses : 0;
	        $question->reports_html = '';
	        $question->custom_html = '';

	        /*
	         * Get the question consolidated report html and custom html (if any) report.
	         * The extensions attaching to this filter should populate html property values and optionally custom_html.
	         */
	        $question = apply_filters( 'ngsurvey_consolidated_report', $question );

	        $questions[] = $question;
	    }
	    
	    ob_start();
	    $this->template->set_template_data( array('questions' => $questions, 'template' => $this->template) )->get_template_part( 'admin/reports/consolidated' );
	    $response = ob_get_clean();
	    
	    wp_send_json_success( $response );
	}
}
