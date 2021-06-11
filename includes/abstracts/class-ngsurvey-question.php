<?php

/**
 * The file that defines the question base class
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
abstract class NgSurvey_Question extends NgSurvey_Base {

	/**
	 * The group this question type belongs to.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $type    The string used to uniquely identify this plugin.
	 */
	public $group = null;
	
	/**
	 * The title of the question type. Show to the user for selecting and creating the question.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $title    The title of this question type.
	 */
	public $title = null;
	
	/**
	 * The icon of the question type. Show to the user for selecting and creating the question.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $icon    The icon of this question type.
	 */
	public $icon = null;
	
	/**
	 * The javascript file name located in the question plugin path, if any to be included. 
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $js    The JavaScript file name in the question plugin path.
	 */
	public $js = null;
	
	/**
	 * The CSS file name located in the question plugin path, if any to be included.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $css    The CSS file name in the question plugin path.
	 */
	public $css = null;
	
	/**
	 * The options array encapsulates options that are applied to the question.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $options    The options data array.
	 */
	public $options = null;
	
	/**
	 * The list of color codes used to display the reports
	 * 
	 * @var array the list of named color codes
	 */
	public $ng_colors = null;
	
	/**
	 * The base directory of the template files, relative to the templates folder.
	 * 
	 * @var string
	 * 
	 * @since    1.0.0
	 */
	protected $template_prefix = '';

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
	    
	    $this->group       = isset( $config[ 'group'] )     ? $config[ 'group' ]     : 'special';
	    $this->title       = isset( $config[ 'title' ] )    ? $config[ 'title' ]     : $this->name;
	    $this->icon        = isset( $config[ 'icon' ] )     ? $config[ 'icon' ]      : 'far fa-question-circle';
	    $this->css         = isset( $config[ 'css' ] )      ? $config [ 'css' ]      : $this->name . '.css';
	    $this->js          = isset( $config[ 'js' ] )       ? $config [ 'js' ]       : $this->name . '.js';
	    
	    $this->template_prefix = isset( $config[ 'template_prefix' ] ) ? $config[ 'template_prefix' ] : '';
	    
	    // Default options
	    $this->options = array(
	        (object) [ 
	            'title'    => 'Set as Required',
	            'type'     => 'select',
	            'name'     => 'required',
	            'help'     => 'Shows this question results in the consolidated report.',
	            'options'  => [ 1 => 'Yes', 0 => 'No' ],
	            'default'  => 0,
	            'filter'   => 'uint',
	        ],
	        (object) [
	            'title'    => 'Show in Report',
	            'type'     => 'select',
	            'name'     => 'show_in_report',
	            'help'     => 'If enabled, the user must answer this question to submit their response.',
	            'options'  => [ 1 => 'Show', 0 => 'Hide' ],
	            'default'  => 1,
	            'filter'   => 'uint',
	        ],
	        (object) [
	            'title'    => 'Question Class',
	            'type'     => 'text',
	            'name'     => 'question_class',
	            'help'     => 'The CSS class name that should be added to the question block in the response page.',
	            'options'  => null,
	            'default'  => null,
	        ],
	        (object) [
	            'title'    => 'Title Class',
	            'type'     => 'text',
	            'name'     => 'title_class',
	            'help'     => 'The CSS class name that should be added to the title block in the response page.',
	            'options'  => null,
	            'default'  => null,
	        ],
	        (object) [
	            'title'    => 'Description Class',
	            'type'     => 'text',
	            'name'     => 'description_class',
	            'help'     => 'The CSS class name that should be added to the description block in the response page.',
	            'options'  => null,
	            'default'  => null,
	        ],
	        (object) [
	            'title'    => 'Question Body Class',
	            'type'     => 'text',
	            'name'     => 'body_class',
	            'help'     => 'The CSS class name that should be added to the question body block in the response page.',
	            'options'  => null,
	            'default'  => null,
	        ],
	    );
	    
	    if( !empty( $config[ 'options' ] ) ) {
	        $this->options = array_merge( $config[ 'options' ], $this->options );
	    }
	    
	    $this->ng_colors = array(
	        'red' => 'rgb(255, 99, 132)',
	        'orange' => 'rgb(255, 159, 64)',
	        'yellow' => 'rgb(255, 205, 86)',
	        'green' => 'rgb(75, 192, 192)',
	        'blue' => 'rgb(54, 162, 235)',
	        'purple' => 'rgb(153, 102, 255)',
	        'grey' => 'rgb(119, 136, 153)',
	        'indianred' => 'rgb(205, 92, 92)',
	        'teal' => 'rgb(0, 128, 128)',
	        'steelblue' => 'rgb(70, 130, 180)',
	        'sandybrown' => 'rgb(244, 164, 96)',
	        'olive' => 'rgb(128, 128, 0)',
	        'mediumvoiletred' => 'rgb(199, 21, 133)',
	        'gold' => 'rgb(255, 215, 0)',
	        'indigo' => 'rgb(75, 0, 130)',
	        'lightgreen' => 'rgb(144, 238, 144)',
	        'wheat' => 'rgb(245, 222, 179)',
	        'rosybrown' => 'rgb(188, 143, 143)',
	        'skyblue' => 'rgb(135, 206, 235)',
	        'lime' => 'rgb(0, 255, 0)',
	        'success' => 'rgb(40, 167, 69)',
	        'info' => 'rgb(23, 162, 184)',
	        'danger' => 'rgb(220, 53, 69)'
	    );
	}
	
	/**
	 * Enqueue the plugin JavaScript file to the parent
	 *
	 * @param array $scripts the combined scripts
	 */
	public function enqueue_admin_scripts( $scripts, $hook, $type = 'question' ) {
	    if( $hook == NGSURVEY_CPT_NAME . '_page_view_reports' || $hook == NGSURVEY_CPT_NAME . '_page_edit_questions' ) {
	       return parent::enqueue_admin_scripts( $scripts, $type );
	    }
	    
	    return $scripts;
	}
	
	/**
	 * Returns the question type parameters to build new questions.
	 */
	public function get_type( $question_types ) {
	    $question_types[] = (object) [
	        'name'      => $this->name,
	        'group'     => $this->group,
	        'icon'      => $this->icon,
	        'title'     => $this->title,
	        'options'   => $this->options
	    ];
	    
	    return $question_types;
	}

	/**
	 * The function that needs to be extended by the child class to display the question form on the edit questions page.
	 * By default the template file will be loaded from form layout and the question object is injected to it. 
	 * 
	 * The form title and description fields are automatically handled by the framework and
	 * this function need not display them again.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $form    The question form rendered in html format.
	 */
	public function get_form ( $question ) {
	    if( $question->qtype != $this->name ) {
	        return $question;
	    }
	    
	    ob_start();
	    $this->template->set_template_data( $question )->get_template_part( $this->template_prefix . 'form/'.$this->name );
	    $question->form_html .= ob_get_clean();
	    
	    if( !isset( $question->question_type ) ) {
	        $types = $this->get_type( array() );
	        $question->question_type = $types[0];
	    }
	    
	    return $question;
	}
	
	/**
	 * The function that needs to be implemented by the child class to render the question on the survey results page of the user response.
	 * By default the template file will be loaded from form layout and the question object is injected to it.
	 *
	 * The title and description of the question will be automatically handled by the framework and
	 * this method need not display them.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $results    The html results of the question displayed on the user response result
	 */
	public function get_results ( $question ) {
	    if( $question->qtype != $this->name ) {
	        return $question;
	    }
	    
	    ob_start();
	    $this->template->set_template_data( $question )->get_template_part( $this->template_prefix . 'results/'.$this->name );
	    $question->results_html .= ob_get_clean();
	    
	    return $question;
	}
	
	/**
	 * The function that needs to be implemented by the child class to render the question on the survey consolidated report page.
	 * By default the template file will be loaded from reports layout and the question object is injected to it.
	 *
	 * The title and description of the question will be automatically handled by the framework and
	 * this method need not display them.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $question    The html report to be displayed on the consolidated report page.
	 */
	public function get_reports ( $question ) {
	    if( $question->qtype != $this->name ) {
	        return $question;
	    }
	    
	    ob_start();
	    $this->template->set_template_data( $question )->get_template_part( $this->template_prefix . 'reports/'.$this->name );
	    $question->reports_html .= ob_get_clean();
	    
	    if( $this->template->get_template_part( $this->template_prefix . 'reports/custom/' . $this->name, null, false ) ) {
	        ob_start();
	        $this->template->set_template_data( $question )->get_template_part( $this->template_prefix . 'reports/custom/'.$this->name );
	        $question->custom_html .= ob_get_clean();
	    }
	    
	    return $question;
	}
	
	/**
	 * Removes the question mapping from the selected survey and page. If there are no other surveys using this question,
	 * then the question object will be permanently deleted from the database.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $page_id      The id of the page
	 */
	public function remove ( $page_id ) {
	    return true;
	}
	
	/**
	 * The abstract function that needs to be extended by the child class to
	 * save the data submitted through the edit questions form.
	 *
	 * @since    1.0.0
	 * @access   protected
     * @var      int        $question  the question object
     * @var      array      $ng_form   The array containing all unsanitized form data
     * 
	 * @var      boolean    $status    True on success, false otherwise
	 */
	public function save_form ( $question ) {
	    if( $question->qtype != $this->name ) {
	        return;
	    }

	    global $wpdb;
	    $options = new NgSurvey_Registry();

	    if( !empty ( $_POST['ngform'][ 'options' ] ) ) {
	        foreach ( $this->options as  $option ) {
	            
	            $option_value = $option->default;
	            $option->filter = isset( $option->filter ) ? $option->filter : null;

	            if( isset( $_POST['ngform']['options'][ $option->name ] ) ) {
	                $option_value = wp_unslash( $_POST['ngform']['options'][ $option->name ] ); //WPCS: The content is sanitized at the below code
	            }
	            
	            switch ( $option->filter ) {
	                case 'uint':
	                    $option_value = (int) $option_value;
	                    break;

	                case 'htmlclass':
	                    $option_value = sanitize_html_class( $option_value );
	                    break;
	                    
	                case 'html':
	                    $option_value = wp_kses_post( $option_value );
	                    break;
	                    
	                case 'key':
	                    $option_value = sanitize_key( $option_value );
	                    break;
	                    
	                default:
	                    $option_value = sanitize_text_field( $option_value );
	                    break;
	            }
	            
    	        $options->__set( $option->name, $option_value );
	        }
	    }
	    
	    $wpdb->update(
	        "{$wpdb->prefix}ngs_questions",
	        array (
	            'title'        => wp_kses_post( wp_unslash( $_POST['ngform']['title'] ) ),
	            'description'  => wp_kses_post( wp_unslash( $_POST['ngform']['description'] ) ),
	            'params'       => $options->toJSON()
    	    ),
    	    array( 'id' => $question->id ),
    	    array(
    	        '%s',
    	        '%s',
    	        '%s'
    	    ),
    	    array( '%d' )
	    );
	}
	
	/**
	 * The abstract function that needs to be extended by the child class to
	 * copy the given question to a new question including the answers of the parent question.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      int        $question  the question object
	 * @var      array      $ng_form   The array containing all unsanitized form data
	 *
	 * @var      boolean    $status    True on success, false otherwise
	 */
	public function copy_question ( $survey_id, $page_id, $question ) {
		if( $question->qtype != $this->name ) {
			return;
		}
		
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 
			"INSERT INTO {$wpdb->prefix}ngs_questions (title, description, qtype, params) SELECT title, description, qtype, params FROM {$wpdb->prefix}ngs_questions WHERE id = %d", 
			$question->id
		) );
		$new_question = $wpdb->insert_id;
		
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}ngs_answers (question_id, answer_type, title, image, sort_order) SELECT %d, answer_type, title, image, sort_order FROM {$wpdb->prefix}ngs_answers WHERE question_id = %d",
			$new_question, $question->id
		) );
		
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}ngs_pages_questions_map (page_id, question_id, sort_order) " .
			"SELECT %d, %d, MAX(a.sort_order) + 1 FROM {$wpdb->prefix}ngs_pages_questions_map AS a WHERE a.page_id = %d",
			$page_id, $new_question, $page_id
		) );
	}
	
	/**
	 * The function that needs to be extended by the child class to
	 * provide the conditional rules templates to build the user defined rules.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $rules The conditional rule template for this question
	 */
	public function get_rules ( $question ) {
	    return $question;
	}
	
	/**
	 * The base function to handle custom actions from the questions. The question plugin can invoke this
	 * function to perform its own actions and return value to the browser.
	 * An example usecase is uploading images of the Image type question using custom ajax action.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      object    $return    Return value
	 */
	public function handle_custom ( $response, $question ) {
	    return $response;
	}

	/**
	 * The function that needs to be implemented by the child class to render the question on the single survey response page.
	 * By default the template file will be loaded from results layout and the question object is injected to it.
	 *
	 * The title and description of the question will be automatically handled by the framework and
	 * this method need not display them.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $display    The question form to be displayed in the response form
	 */
	public function get_display ( $question ) {
	    if( $question->qtype != $this->name ) {
	        return $question;
	    }
	    
	    ob_start();
	    $this->template->set_template_data( $question )->get_template_part( $this->template_prefix . 'survey/'.$this->name );
	    $question->response_form = ob_get_clean();
	    
	    return $question;
	}

	/**
	 * The function to validate the user response for this question.
	 * Extensions overriding this method must validate the extension name with question type.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @var      array $form_data request form data
	 * 
	 * @return   string error if any, true otherwise
	 */
	public function validate( $errors, $question ) {
	    return $errors;
	}

	/**
	 * The abstract function that needs to be extended by the child class to 
	 * filter the response data and return the array of rows to save into database.
	 * The function should return the array of processed user responses, for example
     * array( 
     *  0 => array( 'answer_id' => $answer, 'column_id' => $column, 'answer_data' => null ), 
     *  1 => array( 'answer_id' => 1, 'column_id' => 0, 'answer_data' => $custom_answer ) 
     * )
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $filtered_data the filtered data returned to caller
	 * @var      stdClass $question the question object
     * @var      array $response_data the user response data associated with this question
     * 
     * @return   array $filtered_data the filtered response data
	 */
	abstract protected function filter_response_data ( $filtered_data, $question );
}
