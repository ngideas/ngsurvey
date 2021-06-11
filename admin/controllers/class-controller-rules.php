<?php
/**
 * The file that defines the rules controller class
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
 * The rules controller class.
 *
 * This is used to define rules controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Rules extends NgSurvey_Controller {

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
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    $response      = array();
	    
	    if( !$survey_id || !$page_id ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $data              = new stdClass();
	    $rules_model       = $this->get_model( 'rules' );
	    $pages_model       = $this->get_model( 'pages' );
	    $qns_model         = $this->get_model( 'questions' );
	    
	    $data->rules       = $rules_model->get_rules( $survey_id, $page_id );
	    $data->pages       = $pages_model->get_pages( $survey_id );
	    $data->questions   = array();
	    $questions         = $qns_model->get_questions( $survey_id, $page_id );

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
	        
	        // We don't need the junk data for which no question type found
	        if( !isset( $question->question_type ) ) {
	            continue;
	        }
	        
	        /*
	         * Get the rules defined by the question extension to build the conditional rules.
	         * The extensions implementing this filter should add ites rules template to the rules array. 
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
	        $question->rules = array();
	        $data->questions[] = apply_filters( 'ngsurvey_conditional_rules', $question );
	    }
	    
	    ob_start();
	    $this->template->set_template_data( $data )->get_template_part( 'admin/form/rules' );
	    $response['html'] = ob_get_clean();

	    wp_send_json_success( $response );
	}

	/**
	 * Ajax handler to to handle new page creation
	 *
	 * @since    1.0.0
	 */
	public function create () {
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    $title         = sanitize_text_field( $_POST['title'] );
	    
	    if( !$survey_id || !$page_id || empty( $title ) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $rules_model   = $this->get_model( 'rules' );
	    $pages_model   = $this->get_model( 'pages' );
	    $response      = $rules_model->create( $survey_id, $page_id, $title );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    $data          = new stdClass();
	    $data->rules   = $rules_model->get_rules( $survey_id, $page_id );
	    $data->pages   = $pages_model->get_pages( $survey_id );
	    
	    ob_start();
	    $this->template->set_template_data( $data )->get_template_part( 'admin/form/rules' );
	    $response['html'] = ob_get_clean();
	    
	    wp_send_json_success( $response );
	}

	/**
	 * Ajax handler to to handle rule updates
	 *
	 * @since    1.0.0
	 */
	public function save () {
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    $rule_id       = (int) $_POST['ngform']['rid'];
	    $title         = sanitize_text_field( $_POST['ngform']['title'] );
	    $rule_content  = wp_kses_post( wp_unslash( $_POST['ngform']['rule_content'] ) );

	    // Make sure rule content is proper json object
        $rule_content  = json_decode( utf8_encode( $rule_content ), true );
	    $rule_content  = json_encode( $rule_content );

	    if( !$survey_id || !$rule_id || !$page_id || empty( $title ) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $rule = array();
	    $rule[ 'title' ] = $title;
	    $rule[ 'rule_content' ] = $rule_content;
	    $rule[ 'rule_actions' ] = json_encode( (object) array( 
	        'action' => sanitize_key($_POST['ngform']['action']),
	        'page' => (int) $_POST['ngform']['action_page'],
	        'question' => (int) $_POST['ngform']['action_question']
	    ) );
	    
	    $rules_model   = $this->get_model( 'rules' );
	    $pages_model   = $this->get_model( 'pages' );
	    $response      = $rules_model->update( $survey_id, $page_id, $rule_id, $title, $rule );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    $data          = new stdClass();
	    $data->rules   = $rules_model->get_rules( $survey_id, $page_id );
	    $data->pages   = $pages_model->get_pages( $survey_id );
	    
	    ob_start();
	    $this->template->set_template_data( $data )->get_template_part( 'admin/form/rules' );
	    $response['html'] = ob_get_clean();
	    
	    wp_send_json_success( $response );
	}

	/**
	 * Ajax handler to to handle delete rule function
	 *
	 * @since    1.0.0
	 */
	public function remove () {
	    
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    $rule_id       = (int) $_POST['ngform']['rid'];
	    
	    if( !$survey_id || !$page_id || !$rule_id ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    // Need to use questions model as we delegate remove function to the question
	    $model = $this->get_model( 'rules' );
	    $response = $model->remove_rule( $survey_id, $page_id, $rule_id );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    wp_send_json_success();
	}

	/**
	 * Ajax handler to to handle rules ordering
	 *
	 * @since    1.0.0
	 */
	public function sort () {
	    
	    $ordering      = array_map( 'intval', $_POST['ordering'] );
	    $survey_id     = (int) $_POST['ngform']['sid'];
	    $page_id       = (int) $_POST['ngform']['pid'];
	    
	    if( !$survey_id || !$page_id || empty( $ordering ) ) {
	        $this->raise_error();
	    }
	    
	    // Check user authorization
	    $this->authorise( $survey_id );
	    
	    $model         = $this->get_model( 'rules' );
	    $response      = $model->sort( $survey_id, $page_id, $ordering );
	    
	    if( isset( $response['error'] ) ) {
	        $error = new WP_Error( '002', $response['error'] );
	        wp_send_json_error( $error );
	    }
	    
	    wp_send_json_success( $response );
	}
}
