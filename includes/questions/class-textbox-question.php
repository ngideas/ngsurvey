<?php
/**
 * The file that defines the textbox question type class
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

if( ! class_exists( 'NgSurvey_Textbox_Question', false ) ):

/**
 * The survey textbox question type class.
 *
 * This is used to define textbox question type class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Textbox_Question extends NgSurvey_Question {
    
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
            'name'    => 'textbox',
            'group'   => 'text',
            'icon'    => 'dashicons dashicons-media-text',
            'title'   => __( 'Single line Textbox', 'ngsurvey' ),
            'template_prefix' => 'questions/',
            'options' => array(
                (object) [
                    'title'    => __( 'Textbox Type', 'ngsurvey' ),
                    'type'     => 'select',
                    'name'     => 'textbox_type',
                    'help'     => __( 'Select the type of HTML text elements should be displayed to the users. For advanced usage, select Textbox and use regular expressions.', 'ngsurvey' ),
                    'options'  => [ 
                        'text'      => __( 'Plain Textbox', 'ngsurvey' ), 
                        'password'  => __( 'Password', 'ngsurvey' ), 
                        'email'     => __( 'Email', 'ngsurvey' ), 
                        'number'    => __( 'Number', 'ngsurvey' ) 
                    ],
                    'default'  => 'text',
                    'filter'   => 'key',
                ],
                (object) [
                    'title'    => __( 'Regular Expression', 'ngsurvey' ),
                    'type'     => 'text',
                    'name'     => 'regular_expression',
                    'help'     => __( 'Enter the regular expression which you can use to validate the user input. Leave blank for no regex validation.', 'ngsurvey' ),
                    'options'  => null,
                    'default'  => null,
                ],
                (object) [
                    'title'    => __( 'Validation Message', 'ngsurvey' ),
                    'type'     => 'text',
                    'name'     => 'validation_message',
                    'help'     => __( 'The message that should be displayed if the regex validation fails.', 'ngsurvey' ),
                    'options'  => null,
                    'default'  => null,
                ],
            ),
        ) );
        
        parent::__construct( $config );
    }
    
    /**
     * Returns the rules templates to support conditional rules of this question.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $rules The conditional rule template of this question
     */
    public function get_rules ( $question ) {
        if( $question->qtype != $this->name ) {
            return $question;
        }
        
        $rule = (object) array(
            'id'            => $question->id,
            'field'         => $this->name,
            'label'         => $question->title,
            'icon'          => $this->icon,
            'type'          => 'string',
            'operators'     => array( "equal", "not_equal", "is_empty", "is_not_empty" ),
        );
        array_push( $question->rules, json_encode( $rule ) );
        
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
     * @var      string    $report    The html report to be displayed on the consolidated report page.
     */
    public function get_reports ( $question ) {
        if( $question->qtype != $this->name ) {
            return $question;
        }
        
        $question->chart_data = $this->get_all_dates( $question->survey_id, $question->id, 5000 );
        return parent::get_reports( $question );
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
        if( $question->qtype != $this->name ) {
            return $filtered_data;
        }
        
        if( !empty( $_POST[ 'ngform' ][ 'answers' ][ $question->id ][ 'response' ][0] ) ) {
            $answer_data = sanitize_textarea_field( $_POST[ 'ngform' ][ 'answers' ][ $question->id ][ 'response' ][0] );
            $filtered_data[] = array( 'answer_id' => 0, 'column_id' => 0, 'answer_data' => $answer_data );
        }
        
        if( !empty( $_POST[ 'ngform' ][ 'answers' ][ $question->id ]['custom'] ) ) {
            $custom_text = wp_kses_post( wp_unslash( $_POST[ 'ngform' ][ 'answers' ][ $question->id ]['custom'] ) );
            $filtered_data[] = array( 'answer_id' => 1, 'column_id' => 0, 'answer_data' => $custom_text );
        }
        
        return $filtered_data;
    }
    
    /**
     * Returns the responses statistics of the responses grouped by week/month
     *
     * @param integer $survey_id the id of the survey
     * @param integer $question_id the id of the question
     * @param string $group group type - weekly or monthly
     *
     * @return object the associative array with the date/wk/month as key, records as values
     */
    private function get_all_dates( $survey_id, $question_id, $limit = 1000 ) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(r.created_date,'%%y-%%m-%%d') cdate, count(*) responses ".
            "FROM {$wpdb->prefix}ngs_response_details AS a " .
            "INNER JOIN {$wpdb->prefix}ngs_responses AS r ON r.id = a.response_id " .
            "WHERE r.survey_id = %d AND a.question_id = %d " .
            "GROUP BY cdate " .
            "ORDER BY r.created_date DESC " .
            "LIMIT %d",
            array( $survey_id, $question_id, $limit ),
        ));
        
        return $results ? $results : array();
    }
}

endif;

return new NgSurvey_Textbox_Question();