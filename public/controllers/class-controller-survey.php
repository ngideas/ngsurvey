<?php
/**
 * The file that defines the survey controller class
 *
 * A class definition that includes attributes and functions used across 
 * public-facing side of the site.
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
 * The survey controller class.
 *
 * This is used to define survey controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Survey extends NgSurvey_Controller {

	/**
	 * Define the survey controller of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $config = array() ) {
	    parent::__construct($config);
	}
	
	/**
	 * Renders the survey
	 *
	 * @return mixed
	 * 
	 * @since 1.0.0
	 */
	public function display ( $post = null ) {
	    // Get the survey
	    $survey            = get_post( $post );
	    $survey_key        = get_query_var( 'skey' );
	    $question_objects  = array();

	    if( empty( $survey ) ) {
	        die( __( 'Restricted access.', 'ngsurvey' ) );
	    }

	    $responses_model   = $this->get_model( 'responses' );
	    $response          = $responses_model->check_response( $survey->ID );
	    
	    if( isset( $response[ 'error' ] ) ) {
	        // Either show the end message or the final report
	        $this->template->set_template_data( $response )->get_template_part( 'public/error_message' );
	        return;
	    }

	    // Get the questions already available
	    $pages_model       = $this->get_model( 'pages' );
	    $pages             = $pages_model->get_pages( $survey->ID );
	    
	    if( empty( $pages ) ) {
	        // Something is not alright.
	        die( __( 'Restricted access.', 'ngsurvey' ) );
	    }

	    $questions_model   = $this->get_model( 'questions' );
	    $questions_data    = $questions_model->get_questions( $survey->ID, $pages[ 0 ]->id );
	    
	    if ( empty( $questions_data ) ) {
	        $questions_data = array();
	    }
	    
	    // Get only rules for this page which has show/hide question rules
	    $page_id           = $pages[ 0 ]->id;
	    $rules_model       = $this->get_model( 'rules' );
	    $rules             = $rules_model->get_rules( $survey->ID, $page_id );

	    foreach ( $rules as $i => $rule ) {
	        $rule_json = json_decode( $rule->rule_actions );

	        if( !empty($rule_json->action) && !in_array( $rule_json->action, array( 'show_question', 'hide_question' ) ) ) {
	            unset( $rules[$i] );
	        }
	        
	        if( !empty($rule_json->action) && $rule_json->action == 'show_question' ) {
	            // hide by default
	            foreach ( $questions_data as &$question ) {
	                if( $rule_json->question == $question->id ) {
	                    $question->hidden = true;
	                }
	            }
	            unset($question);
	        }
	    }
	    
	    $response_id       = isset( $response['response_id' ] ) ? $response['response_id' ] : 0;
	    $question_objects  = $this->get_question_objects( $page_id, $response_id, $questions_data );

	    // Build the final data and deligate it to the template response
	    $data = array();
	    $data[ 'item' ]        = $survey;
	    $data[ 'questions' ]   = $question_objects;
	    $data[ 'pages' ]       = $pages;
	    $data[ 'rules' ]       = $rules;
	    $data[ 'template' ]    = $this->template;
	    $data[ 'pid' ]         = $page_id;
	    $data[ 'skey' ]        = $survey_key;
	    $data[ 'rid' ]         = $response_id;
	    $data[ 'is_last' ]     = count( $pages ) == 1 ? true : false;

	    $this->template->set_template_data( $data )->get_template_part( 'public/single_survey' );
	}
	
	/**
	 * The function to save the response form and render the next page, if any, or finalize the response.
	 * The function does the following set of actions during the operation:
	 *
	 * 1. Check if there is already an existing response to use or create new response if there is none.
	 * 2. Saves the response form data, i.e. the answers submitted by the user, if any.
	 * 3. Check if the response finished, i.e. no more pages exist to display, then finalize survey response.
	 * 4. If there are more pages, get questions for the next page to display.
	 * 
	 * @since 1.0.0
	 */
	public function save () {
	    $pages_model           = $this->get_model( 'pages' );
	    $rules_model           = $this->get_model( 'rules' );
	    $responses_model       = $this->get_model( 'responses' );
	    
	    $survey_id             = isset( $_POST[ 'ngform' ][ 'sid' ] ) ? (int) $_POST[ 'ngform' ][ 'sid' ] : 0;
	    $page_id               = isset( $_POST[ 'ngform' ][ 'pid' ] ) ? (int) $_POST[ 'ngform' ][ 'pid' ] : 0;
	    $response_id           = isset( $_POST[ 'ngform' ][ 'rid' ] ) ? (int) $_POST[ 'ngform' ][ 'rid' ] : 0;
	    $survey                = get_post( $survey_id );
	    
	    if( !$page_id || empty( $survey ) ) {
	        $this->raise_error( '001' );
	    }
	    
	    $messages = array();
	    
	    /**
	     * Apply filters to get the validation messages from interested plugins.
	     *
	     * At this point, no operations done on the request data.
	     * If there are any messages added by the plugins, show them to the user and do not allow to proceed with save response.
	     */
	    $messages = apply_filters( 'ngsurvey_response_pre_save', $messages );

	    if( !empty( $messages ) && strlen( implode( $messages ) ) > 0 ) {
	        $error = new WP_Error( '002', implode('<br/>', $messages) );
	        wp_send_json_error( $error );
	    }
	    
	    // Get all available pages of survey first.
	    $pages = $pages_model->get_pages( $survey_id );
	    
	    // You must have pages and the response id should be present if it is not first page
	    if( empty( $pages ) || ( !$response_id && $pages[ 0 ]->id != $page_id ) ) {
	        // Something is not alright.
	        $this->raise_error( '003' );
	    }
	    
	    // Create the response if this is first page and there is no response id
	    if( !$response_id ) {
	        $response_id = $responses_model->create_response( $survey_id );
	        
	        if( !$response_id ) {
	            // Unable to create new response, raise error
	            $error = new WP_Error( '004', __( 'An error occurred while creating the response.', 'ngsurvey' ) );
	            wp_send_json_error( $error );
	        }
	    }
	    // If the response id is present, check if it not already completed
	    else if( $responses_model->is_response_finished( $response_id ) ){
	        $this->raise_error( '005' );
	    }
	    
	    // Get the question types and page rules
	    $question_objects = $this->get_questions( $survey_id, $page_id );
	    
	    // Validate the response
	    $errors = $this->validate( $question_objects );
	    if( !empty( $errors ) ) {
	        wp_send_json_error( implode('<br/>', $errors), 422 );
	    }
	    
	    // Save responses
	    $result = $this->save_response_data( $survey_id, $page_id, $response_id, $question_objects );
	    if( !$result ) {
	        $error = new WP_Error( '006', __( 'Unable to process user responses.', 'ngsurvey' ) );
	        wp_send_json_error( $error );
	    }
	    
	    // Get the next page if any, otherwise finalize response
	    $page_rules = $rules_model->get_rules( $survey_id, 0 );
	    $next_page_id = $this->get_next_page_id( $page_id, $pages, $page_rules, $response_id );
	    
	    if( !$next_page_id ) {
	        // finalize response
	        $response = $this->finalize( $survey, $response_id );
	        wp_send_json_success( $response );
	    }
	    
	    // If we are here, we have next page to display
	    $this->show_page( $survey, $pages, $next_page_id, $response_id );
	}
	
	/**
	 * Gets the next page to be displayed by checking the rules and response
	 *
	 * @param integer $current_page_id the current page the user responded to
	 * @param array $pages list of all pages
	 * @param array $rules list of all rules on this page
	 * @param array $ng_form response data
	 *
	 * @return integer $next_page_id the id of the next page
	 * 
	 * @since 1.0.0
	 */
	private function get_next_page_id( $current_page_id, $pages, $page_rules, $response_id ) {
	    $next_page_id  = 0;
	    $parser        = new NgSurvey_Rules_Parser();
	    $continue_next = false;
	    
	    for( $i = 0; $i < count( $pages ); $i++ ) {
	    	// Unless this is the current page the user is saving response to, we are not interested.
	        if( $pages[ $i ]->id != $current_page_id && !$continue_next) {
	            continue;
	        }
	        
	        // found the current page, find next page
	        if( $continue_next ) {
	        	// Previous page is skipped by a rule, so check if this page is allowed or not
	            $next_page_id = $pages[ $i ]->id;
	        } else {
	        	// This is the first iteration, so the next page is immediate next to the current
	            $next_page_id = isset( $pages[ $i + 1 ] ) ? $pages[ $i + 1 ]->id : 0;
	        }
	        
	        // If we get the next page, check the rules allow it to show or not.
	        if( $next_page_id ) {
	        	// initially, set to not check next page unless some rule allow
	            $continue_next = false;
	            
	            foreach ( $page_rules as $page_rule ) {
	                
	                $ruleActions = json_decode( $page_rule->rule_actions );
	                if( ( empty( $ruleActions->action ) ) ||
	                    ( !in_array( $ruleActions->action, array( 'show_page', 'skip_page', 'finalize' ) ) ) || 
	                	( $ruleActions->page > 0 && $ruleActions->page != $next_page_id ) ||
	                	( $ruleActions->page == 0 && $page_rule->page_id != $current_page_id )
	                	){
	                    continue;
	                }
	                
	                $isValid = $parser->validate_rules($page_rule->rule_content, $response_id);
	                switch ( $ruleActions->action ) {
	                    case 'show_page':
	                        // Show this page only if the rules are met, otherwise continue to next page
	                        $continue_next = !$isValid;
	                        break;
	                        
	                    case 'skip_page':
	                        // Skip page if rules are met
	                        $continue_next = $isValid;
	                        break;
	                        
	                    case 'finalize':
	                        // finalize response if rules are met
	                    	if( $isValid ) {
	                    		$next_page_id = 0;
	                    	}
	                        break;
	                }
	            }
	        }
	        
	        if( !$next_page_id || !$continue_next ) {
	            // We don't get any skip page/show page hints in rules
	            break;
	        }
	    }
	    
	    return $next_page_id;
	}
	
	/**
	 * Shows the questions of a given page
	 *
	 * @param object $survey the survey object
	 * @param array $pages the list of pages
	 * @param integer $page_id the page which should be shown
	 * 
	 * @since 1.0.0
	 */
	private function show_page ( $survey, $pages, $page_id, $response_id ) {
	    
	    $rules_model           = $this->get_model( 'rules' );
	    $page_rules            = $rules_model->get_rules( $survey->ID, 0 );
	    $question_objects      = $this->get_questions( $survey->ID, $page_id, $response_id, $pages, $page_rules );
	    
	    // Build the final data and deligate it to the template response
	    $data = array();
	    $data[ 'item' ]        = $survey;
	    $data[ 'pages' ]       = $pages;
	    $data[ 'questions' ]   = $question_objects;
	    $data[ 'rules' ]       = $page_rules;
	    $data[ 'template' ]    = $this->template;
	    $data[ 'rid' ]         = $response_id;
	    $data[ 'pid' ]         = $page_id;
	    
	    foreach ( $pages as $i => $page ) {
	        if( $page->id == $page_id && empty( $pages[ $i + 1 ] ) ) {
	            $data[ 'is_last' ] = true;
	            break;
	        }
	    }
	    
	    ob_start();
	    $this->template->set_template_data( $data )->get_template_part( 'public/single_survey' );
	    $html = ob_get_clean();
	    
	    wp_send_json_success( $html );
	}
	
	/**
	 * Gets the questions wrapped inside the respective question types
	 *
	 * @param integer $survey_id the id of the survey
	 * @param integer $page_id the id of the page to get the questions from
	 *
	 * @return array list of questions wrapped inside the respective question type
	 * 
	 * @since 1.0.0
	 */
	private function get_questions( $survey_id, $page_id, $response_id = 0, $pages = false, $rules = false ) {
	    
	    $questions_model   = $this->get_model( 'questions' );
	    $questions_data    = $questions_model->get_questions( $survey_id, $page_id );
	    $question_objects  = array();
	    
	    if ( empty( $questions_data ) ) {
	        return $question_objects;
	    }
	    
	    if( $rules ) {
	        $parser = new NgSurvey_Rules_Parser();
	        
	        // Check if any of the current page questions are hidden by conditional rules from previous pages
	        foreach ( $pages as $page ) {
	            
	            // Proceed only till the current page, future page rules need not be validated
	            if( $page->id == $page_id ) {
	                
	                // Hide questions with the "Show Question" conditional rule
	                foreach ( $rules as $rule ) {
	                    $ruleActions = json_decode( $rule->rule_actions );
	                    
	                    if( !empty( $ruleActions->action ) && $ruleActions->action == 'show_question' ) {
	                        foreach ( $questions_data as $idx => $question ) {
	                            if( $ruleActions->question == $question->id ) {
	                                $question->hidden = true;
	                            }
	                        }
	                    }
	                }
	                break; // do not proceed to next step and break here
	            }
	            
	            // If we are here, that means this is one of the already answered pages
	            foreach ( $rules as $rule ) {
	                if( $rule->page_id != $page->id ) {
	                    continue;
	                }
	                
	                $ruleActions = json_decode( $rule->rule_actions );
	                if( empty( $ruleActions->action ) || !in_array( $ruleActions->action, array( 'show_future_qn', 'hide_future_qn' ) ) ) {
	                    continue;
	                }
	                
	                foreach ( $questions_data as $idx => $question ) {
	                    if( $question->id != $ruleActions->question ) {
	                        continue;
	                    }
	                    
	                    $isValid = $parser->validate_rules( $rule->rule_content, $response_id );
	                    if( ( $ruleActions->action == 'show_future_qn' && !$isValid ) || ( $ruleActions->action == 'hide_future_qn' && $isValid ) ) {
	                        unset( $questions_data[ $idx ] );
	                    }
	                }
	            }
	        }
	    }
	    
	    return $this->get_question_objects( $page_id, $response_id, $questions_data );
	}
	
	/**
	 * Gets the available question fields for the current page to display.
	 * The method try to resolve the responses details into the fields to populate submitted responses.
	 * 
	 * @param int $page_id the id of the page to be displayed
	 * @param int $response_id response id
	 * @param array $questions_data the question data
	 * 
	 * @return array questions fields
	 * 
	 * @since 1.0.0
	 */
	private function get_question_objects( $page_id, $response_id, $questions_data ) {
	    $question_types = array();
	    $question_objects = array();
	    $responses = array();
	    
	    if( $response_id ) {
	        $responses_model = $this->get_model( 'responses' );
	        $responses = $responses_model->get_response_details( $response_id );
	    }
	    
	    /*
	     * Get all available question types to display the drag and drop types on question form.
	     * Extensions supporting the question types should join this filter type and append their question type to the return value.
	     * The filter expect an object with atleast 5 attributes added to the return value as follows:
	     *
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
	    
	    foreach ( $questions_data as $question ) {
	        foreach ( $question_types as $question_type ) {
	            if( $question->qtype == $question_type->name ) {
	                $question->question_type = $question_type;
	                break;
	            }
	        }
	        
	        // We don't need the the junk data for which no question type found
	        if( !isset( $question->question_type ) ) {
	            continue;
	        }
	        
	        $question->responses = array();
	        if( !empty( $responses ) ) {
	            foreach ( $responses as $response ) {
	                if( $response[ 'page_id' ] == $page_id && $response[ 'question_id' ] == $question->id ) {
	                    $question->responses[] = $response;
	                }
	            }
	        }
	        
	        $question->response_form = '';
	        
	        /*
	         * Adds the hook to fetch the response form from the implementing extension of this question type.
	         * The basic form details like headers including title, description, wireframe etc are handled by the framework.
	         * The implementing extension should only return the response form for rendering the input components.
	         */
	        $question = apply_filters( 'ngsurvey_response_form', $question );
	        
	        $question_objects[] = $question;
	    }
	    
	    return $question_objects;
	}
	
	/**
	 * Saves the response data
	 *
	 * @param array $question_objects list of questions wrapped in their types
	 * @param array $ng_form response data
	 *
	 * @return array boolean status
	 * 
	 * @since 1.0.0
	 */
	private function save_response_data( $survey_id, $page_id, $response_id, $questions ) {
	    $filtered_data = array();
	    foreach ( $questions as $question ) {
	        if( !isset( $_POST[ 'ngform' ]['answers'][$question->id] ) ) {
	            continue;
	        }
	        
	        /*
	         * Apply filter to get the data from the hooked extensions, an additional parameter with user response data is passed as second argument.
	         * The extensions should add data which can be saved directly to the database.
	         * The extensions can add multiple filtered response rows as per the requirement, however all such rows should follow below rules.
	         *
	         * 1. Each data row must be an associative array with 3 properties - answer_id, column_id, answer_data
	         *    e.g. array( 'answer_id' => $answer_id, 'column_id' => $column_id, 'answer_data' => null )
	         *
	         * 2. The custom answer, if supported by the extension, given by the user should be populated as an additonal row with answer_id = 1, column_id = 1
	         *    e.g. array( 'answer_id' => 1, 'column_id' => 0, 'answer_data' => $response_data['custom'] )
	         *
	         * 3. For data rows, the answer_data field shall be set to null and it is populated only for custom answers
	         */
	        $filtered_data[ $question->id ] = apply_filters( 'ngsurvey_filter_user_responses', array(), $question );
	    }
	    
	    if( empty( $filtered_data ) ) {
	        return true;
	    }
	    
	    $responses_model = $this->get_model( 'responses' );
	    $result = $responses_model->save_response_data( $survey_id, $page_id, $response_id, $filtered_data );
	    
	    return $result;
	}
	
	/**
	 * Validates the response object against the give questions and page rules.
	 * The method tries to invoke the validate function on each question object by passing respecting question data.
	 *
	 * @param array $questions the list of question objects
	 * @param array $page_rules the list of page rules
	 * @param array $ng_form the request form data
	 *
	 * @return array list of errors if any
	 * 
	 * @since 1.0.0
	 */
	private function validate( $questions ) {
	    $errors = array();
	    
	    foreach ( $questions as $question ) {
	        /*
	         * The validation of responses shall be delegated to the respective extensions.
	         * The extensions should populate the validation errors to the $errors array and return it.
	         * The response data can be accessed from $_POST[ 'ngform' ]['answers'][$question->id]
	         * 
	         * @since 1.0.0
	         */
	        $errors = apply_filters( 'ngsurvey_validate_response', $errors, $question );
	    }
	    
	    return $errors;
	}
	
	/**
	 * Finalizes the survey response and return the final message/report.
	 *
	 * @param object $survey the current survey object
	 * @param integer $response_id id the id the response to be finalized
	 *
	 * @return string the end message/report of the survey response
	 * 
	 * @since 1.0.0
	 */
	private function finalize( $survey, $response_id ) {
	    $responses_model = $this->get_model( 'responses' );
	    $responses_model->finalize_response( $survey->ID, $response_id );
	    $options = get_post_meta($survey->ID, 'ngsurvey_settings', true);
	    
	    $data = array( 'title' => __( 'Thank you for your response.', 'ngsurvey' ), 'message' => '' );
	    if( !empty( $options ) ) {
	        if( !empty( $options[ 'end_of_survey_title' ] ) ) {
	            $data[ 'title' ] = $options[ 'end_of_survey_title' ];
	        }
	        
	        if( !empty( $options[ 'end_of_survey_message' ] ) ) {
	            $data[ 'message' ] = $options[ 'end_of_survey_message' ];
	        }
	    }
	    
	    /*
	     * Apply filter to the end of survey message shown to the user after the user response is finished
	     * The argument is an array with two properties, a title and the message.
	     */
	    $data = apply_filters( 'ngsurvey_end_of_survey_message_data', $data );
	    
	    ob_start();
	    $this->template->set_template_data( $data )->get_template_part( 'public/success_message' );
	    $html = ob_get_clean();
	    
	    // Filter the finalize page HTML message
	    $html = apply_filters( 'ngsurvey_end_of_survey_message', $html, $survey, $response_id );
	    
	    /*
	     * Complete all actions hooked to the survey response to perform end of survey operations.
	     * This hook is the best place to execute all actions right after the survey response is completed.
	     */
	    do_action( 'ngsurvey_survey_complete', $survey->ID, $response_id );
	    
	    wp_send_json_success( $html );
	}
}
