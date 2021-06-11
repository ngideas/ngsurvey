<?php
/**
 * The file that defines the pages controller class
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
 * The pages controller class.
 *
 * This is used to define pages controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Pages extends NgSurvey_Controller {

	/**
	 * Define the rules controller of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($config = array()) {
	    parent::__construct($config);
	}

	/**
	 * Ajax handler to to handle page load function to load questions of a page
	 *
	 * @since    1.0.0
	 */
	public function display () {
	    
	    $survey_id     = (int) $_POST[ 'ngform' ]['sid'];
	    $page_id       = (int) $_POST[ 'ngform' ]['pid'];
	    $response      = '';
	    
	    if( !$survey_id || !$page_id ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'questions' );
	    $questions     = $model->get_questions( $survey_id, $page_id );
	    
	    if ( empty( $questions ) ) {
	        $questions         = array();
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
	    $question_types = array();
	    $question_types = apply_filters( 'ngsurvey_fetch_question_types', $question_types );
	    
	    foreach ( $questions as $question ) {
	        foreach ( $question_types as $question_type ) {
	            if( $question->qtype == $question_type->name ) {
	                $question->question_type = $question_type;
	                break;
	            }
	        }
	        
	        // We don't need the questions not on first page, or the junk data for which no question type found
	        if( !isset( $question->question_type ) ) {
	            continue;
	        }

	        /*
	         * This filter is applied on the question object to build the question form by the supported question extension.
	         * Extension consuming this filter should push the question form HTML string to the html (array) attribute.
	         */
	        $question->form_html = '';
	        $question = apply_filters( 'ngsurvey_fetch_question_form', $question );
	        
	        ob_start();
	        $this->template->set_template_data( array(
	            'question' => $question, 
	            'template' => $this->template
	        ) )->get_template_part( 'admin/form/question' );
	        $html = ob_get_clean();
	        
	        $response = $response . $html;
	    }
	    
	    wp_send_json_success( $response );
	}

	/**
	 * Ajax handler to to handle new page creation
	 *
	 * @since    1.0.0
	 */
	public function create () {
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $title         = sanitize_text_field( $_POST['title'] );
	    
	    if( !$survey_id || empty( $title ) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'pages' );
	    $response      = $model->create( $survey_id, $title );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    $data = new stdClass();
	    $data->pages = $model->get_pages( $survey_id );

	    ob_start();
	    $this->template->set_template_data( $data )->get_template_part( 'admin/form/pages' );
	    $response['html'] = ob_get_clean();
	    
	    wp_send_json_success( $response );
	}

	/**
	 * Ajax handler to to handle page title updates
	 *
	 * @since    1.0.0
	 */
	public function update () {
	    
	    $title         = sanitize_text_field( $_POST['title'] );
	    $page_id       = (int) $_POST['ngform']['pid'];
	    
	    if( !$page_id || empty( $title ) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'pages' );
	    $response      = $model->update ( $page_id, $title );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    wp_send_json_success( $title );
	}

	/**
	 * Ajax handler to to handle delete page function
	 *
	 * @since    1.0.0
	 */
	public function remove () {
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    
	    if( !$survey_id || !$page_id ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    // Need to use questions model as we delegate remove function to the question
	    $model = $this->get_model( 'questions' ); 
	    $response = $model->remove_page( $survey_id, $page_id );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    wp_send_json_success();
	}
	
	/**
	 * Ajax handler to to handle pages ordering 
	 *
	 * @since    1.0.0
	 */
	public function sort () {
	    
		$ordering      = array_map( 'intval', $_POST['ordering'] );
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    
	    if( !$survey_id || empty( $ordering ) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'pages' );
	    $response      = $model->sort( $survey_id, $ordering );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    wp_send_json_success( $response );
	}
}
