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
class NgSurvey_Controller_Questions extends NgSurvey_Controller {

	/**
	 * Define the questions controller of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($config = array()) {
	    parent::__construct($config);
	}
	
	/**
	 * Renders the survey questions
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function display () {
	    $nonce = $_REQUEST['_wpnonce'];
	    if ( ! wp_verify_nonce( $nonce, 'edit_questions_nonce' ) ) {
	        die( __( 'This page cannot be accessed directly.', 'ngsurvey' ) );
	    }
	    
	    // Check user authorization
	    $this->authorise( (int) $_REQUEST[ 'post' ] );

	    // Get the survey
	    $model                 = $this->get_model( 'questions' );
	    $pages_model           = $this->get_model( 'pages' );
	    $rules_model           = $this->get_model( 'rules' );
	    $survey                = get_post( (int) $_REQUEST[ 'post' ] );
	    $questions             = array();
	    $question_types        = array();
	    $groups                = array();
	    
	    if( empty($survey) ) {
	        die( __( 'Restricted access.', 'ngsurvey' ) );
	    }
	    
	    // Get the questions already available
	    $pages = $pages_model->get_pages( $survey->ID );
	    if( empty( $pages ) ) {
	        // Something is not alright.
	        die( __( 'Restricted access.', 'ngsurvey' ) );
	    }
	    
	    $questions_data = $model->get_questions( $survey->ID );
	    if ( empty( $questions_data ) ) {
	        $questions_data = array();
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
	    
	    /*
	     * Get the first page display form for each question by applying the filter.
	     * Extensions joining this filter must return the parameter passed by appending the HTML form to it in the below format.
	     * 
	     * e.g. $question->form_html .= $html_form;
	     * 
	     * The extension must check the question type by checking $question->type and apply the values only if question belongs to it.
	     * Otherwise return the parameter without modifying it.
	     */
	    foreach ( $questions_data as &$question ) {
	        foreach ( $question_types as $question_type ) {
	            if( $question->qtype == $question_type->name ) {
	                $question->question_type = $question_type;
	                break;
    	        }
	        }

	        // We don't need the questions not on first page, or the junk data for which no question type found
	        if( $question->page_id != $pages[0]->id || !isset( $question->question_type ) ) {
	            continue;
	        }
	        
	        $question->form_html = '';
	        $question->rules = array();

	        /*
	         * This filter is applied on the question object to build the question form by the supported question extension.
	         * Extension consuming this filter should push the question form HTML string to the html (array) attribute.
	         */
	        $question = apply_filters( 'ngsurvey_fetch_question_form', $question );

	        /*
	         * Get the rules defined by the question extension to build the conditional rules.
	         * The extensions implementing this filter should add ites rules template to the rules array ($question->rules).
	         * The rules template is a json encoded string with the following array properties
	         *
	         * id - Question ID, Question objec sent as a parameter to this filter
	         * field - Name of the extension, e.g. grid
	         * label - Question title
	         * icon - Icon of the question type
	         * type - Data type accepted by the rule input field
	         * input - The HTML field to display
	         * values - Array of label-value pairs for select items
	         * multiple - If it is select input, is multiple selection allowed?
	         * plugin - jQuery plugin to use, e.g. select2
	         * plugin_config - jQuery plugin configuration. e.g. for select2 => (object) array('width'     => 'auto', 'theme'     => 'bootstrap4')
	         * operators - allowed operator types e.g. array( "in", "not_in", "is_empty", "is_not_empty" ),
	         */
	        $question = apply_filters( 'ngsurvey_conditional_rules', $question );

            array_push( $questions, $question );
	    }
	    unset( $question );
	    
	    // Now we have all questions, let us group them for proper display of types on the questions form
	    foreach ( $question_types as $question_type ) {
	        $groups[ $question_type->group ][] = $question_type;
	    }

	    // Build pagewise questions markup for rules actions, to show list of questions created by the user
	    $markup = array();
	    foreach ( $pages as $page ) {
	        $group                 = new stdClass();
	        $group->id             = $page->id;
	        $group->text           = $page->title;
	        $group->children       = array();
	        
	        foreach ( $questions as $question ) {
	            if( $question->page_id != $page->id || !isset( $question->question_type ) ) {
	                continue;
	            }
	            
	            $group_item        = new stdClass();
	            $group_item->id    = $question->id;
	            $group_item->text  = $question->title;
	            $group_item->icon  = $question->question_type->icon;
	            $group->children[] = $group_item;
	        }
	        
	        $markup[] = $group;
	    }
	    
	    // Build the final data and deligate it to the template response
	    $data = array();
	    $data[ 'item' ]        = $survey;
	    $data[ 'questions' ]   = $questions;
	    $data[ 'groups' ]      = $groups;
	    $data[ 'pages' ]       = $pages;
	    $data[ 'rules' ]       = $rules_model->get_rules( $survey->ID, $data[ 'pages' ][0]->id );
	    $data[ 'template' ]    = $this->template;
	    $data[ 'json_qns' ]    = json_encode( $markup );

	    $this->template->set_template_data( $data )->get_template_part( 'admin/edit_questions' );
	}
	
	/**
	 * Ajax handler to create and return a new question
	 * 
	 * @since    1.0.0
	 */
	public function create () {
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    $type          = sanitize_key($_POST['ngform']['qtype']);
	    $title         = isset( $_POST['ngform']['title'] ) ? wp_kses_post( wp_unslash( $_POST['ngform']['title'] ) ) : __( "Your question title..", 'ngsurvey' );

	    if( !$survey_id || !$page_id || empty($type) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'questions' );
	    $question      = $model->create( $survey_id, $page_id, $type, $title );
	    
	    $this->display_question_form( $question );
	}
	
	/**
	 * Ajax handler to save and return a question
	 *
	 * @since    1.0.0
	 */
	public function save () {

	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    $question_id   = (int) $_POST['ngform']['qid'];
	    $type          = sanitize_key($_POST['ngform']['qtype']);

	    if( !$survey_id || !$page_id || !$question_id || empty($type) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'questions' );
	    $question      = $model->get_question( $question_id );

	    /*
	     * This action shall be executed by all question extensions to save their question data when the user save the form.
	     * The question object from the database available as parameter to the implementing plugin. 
	     * The form data can be accessed from $_POST['ngform'].
	     * 
	     * The implementer can use the abstract question class parent method to save the basic details such as question title etc.
	     * The plugin shall override its save_form method to add specific details if necessary, 
	     * however the implementer can chose their own method of saving data to database.  
	     */
	    do_action( 'ngsurvey_save_question_form', $question );
	    
	    // Fetch the updated question to send output to front-end.
	    $question      = $model->get_question( $question_id );
	    
	    $this->display_question_form( $question );
	}
	
	/**
	 * Ajax handler to copy and return a question
	 *
	 * @since    1.0.0
	 */
	public function copy () {
		
		$survey_id     = (int) $_POST['ngform']['sid'];
		$page_id       = (int) $_POST['ngform']['pid'];
		$question_id   = (int) $_POST['ngform']['qid'];
		
		if( !$survey_id || !$page_id || !$question_id ) {
			$this->raise_error();
		}
		
		// Check user authorization
		$this->authorise( $survey_id );
		
		$model         = $this->get_model( 'questions' );
		$question      = $model->get_question( $question_id );
		
		/*
		 * This action shall be executed by all question extensions to copy their question data when the user save the form.
		 * The survey id, page id and question id of the source question are sent as parameters.
		 *
		 * The implementer can use the abstract question class parent method to copy the basic details such as question title etc.
		 * The plugin shall override its copy_question method to add specific details if necessary,
		 * however the implementer can chose their own method of saving data to database.
		 */
		do_action( 'ngsurvey_copy_question', $survey_id, $page_id, $question );

		$this->display_question_form( $question );
	}
	
	/**
	 * Ajax handler to remove a question
	 *
	 * @since    1.0.0
	 */
	public function move () {
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $old_page_id   = (int) $_POST['ngform']['pid'];
	    $new_page_id   = (int) $_POST['ngform']['nid'];
	    $question_id   = (int) $_POST['ngform']['qid'];
	    
	    if( !$survey_id || !$old_page_id || !$question_id ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'questions' );
	    $model->move( $survey_id, $old_page_id, $new_page_id, $question_id );

	    wp_send_json_success();
	}
	
	/**
	 * Ajax handler to remove a question
	 *
	 * @since    1.0.0
	 */
	public function remove () {

	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    $question_id   = (int) $_POST['ngform']['qid'];
	    $type          = sanitize_key($_POST['ngform']['qtype']);
	    
	    if( !$survey_id || !$page_id || !$question_id || empty($type) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'questions' );
	    $question      = $model->get_question( $question_id );
	    
	    $model->remove( $question, $page_id );
	    
	    /*
	     * This filter is to notify the extensions to process the data removal of extension specific data from the database.
	     * All standard data from the database such as question and answers are removed before applying this filter.
	     * The implemnter should clear their specific data such as images etc. with this filter.
	     */
	    apply_filters( 'ngsurvey_remove_question', $question );
	    
	    wp_send_json_success();
	}

	/**
	 * Ajax handler to to handle questions ordering on a page
	 *
	 * @since    1.0.0
	 */
	public function sort () {
	    
	    $ordering      = array_map( 'intval', $_POST['ordering'] );
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    
	    if( !$page_id || empty( $ordering ) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'questions' );
	    $response      = $model->sort( $page_id, $ordering );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    wp_send_json_success( $response );
	}

	/**
	 * Ajax handler to to handle custom actions of a question
	 *
	 * @since    1.0.0
	 */
	public function custom () {
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    $question_id   = (int) $_POST['ngform']['qid'];
	    
	    if( !$survey_id || !$page_id || !$question_id ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'questions' );
	    $question      = $model->get_question( $question_id );
	    $response      = array();

	    /*
	     * Perform custom filter hook if any implemented by the extensions.
	     * This custom filter can be utilized by the extensions to perform and custom ajax function from the question form.
	     * An example action is saving the images of the images type question.
	     * The implementer should return the response array
	     */
	    $response = apply_filters( 'ngsurvey_custom_form_action', $response, $question );

	    wp_send_json_success( $response );
	}
	
	/**
	 * Displays the question to respondent.
	 * 
	 * @param stdClass $question the question object
	 */
	private function display_question_form( $question ) {
		$question_types	= array();
		
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
		
		foreach ( $question_types as $question_type ) {
			if( $question->qtype == $question_type->name ) {
				$question->question_type = $question_type;
				break;
			}
		}
		
	    $question->form_html = '';
	    $question = apply_filters( 'ngsurvey_fetch_question_form', $question );
	    
	    ob_start();
	    $this->template->set_template_data( array('question' => $question, 'template' => $this->template) )->get_template_part( 'admin/form/question' );
	    $html = ob_get_clean();
	    
	    wp_send_json_success( $html );
	}
}
