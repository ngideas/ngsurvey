<?php
/**
 * The file that defines the questions model class
 *
 * A class definition that includes attributes and functions used across
 * public-facing side of the site.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/models
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The questions model class.
 *
 * This is used to define questions model data.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Model_Questions extends NgSurvey_Model {

    /**
     * Define the questions model functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Gets the questions of the given survey id, optionally given page id.
     *
     * @param int $survey_id The id of the survey to get the questions from.
     * @param int $page_id The page id of the questions. if false, returns all questions from the survey.
     *
     * @return array The list of Question objects
     */
    public function get_questions ( $survey_id, $page_id = false ) {
        global $wpdb;
        
        $query = 
        "SELECT a.id, a.title, a.description, a.qtype, a.params, p.id as page_id, 0 as validate, 0 as hidden ".
        "FROM {$wpdb->prefix}ngs_pages_questions_map AS m ".
        "INNER JOIN {$wpdb->prefix}ngs_pages AS p ON p.id = m.page_id ".
        "INNER JOIN {$wpdb->prefix}ngs_questions AS a ON a.id = m.question_id ".
        "WHERE p.survey_id = %d";
        
        if( $page_id === 0 ) {
            $query = $query . " AND p.sort_order = 1 ";
        } else if( $page_id > 0 ) {
            $query = $query . " AND m.page_id = %d ";
        }
        
        $query = $query . " ORDER BY m.sort_order ASC";

        if( $page_id > 0 ) {
            $query = $wpdb->prepare( $query , $survey_id, $page_id );
        } else {
            $query = $wpdb->prepare( $query , $survey_id );
        }

        $questions = $wpdb->get_results( $query );
        
        // Convert params to json object
        if( empty( $questions ) ) {
            return array();
        }
        
        $this->initialize_questions( $questions );

        return $questions;
    }

    /**
     * Gets the question of the given id.
     *
     * @param int $question_id The id of the question to get the data.
     *
     * @return array The question object
     */
    public function get_question ( $question_id ) {
        global $wpdb;
        
        $query =
        "SELECT a.id, a.title, a.description, a.qtype, a.params ".
        "FROM {$wpdb->prefix}ngs_questions AS a ".
        "WHERE a.id = %d";
        
        $question = $wpdb->get_row( $wpdb->prepare( $query, $question_id ), 'OBJECT' );
        $questions = array( (object) $question );
        $this->initialize_questions( $questions );
        
        return $questions[0];
    }

    /**
     * Loads the question details such as answers, params etc.
     *
     * @param array $questions questions array
     */
    private function initialize_questions ( &$questions ) {
        global $wpdb;
        $question_ids = array();
        
        if( empty( $questions ) ) {
            return;
        }
        
        // Parameters
        foreach ( $questions as &$question ) {
            $question->params = new NgSurvey_Registry( $question->params );
            $question_ids[] = $question->id;
        }
        
        // Answers
        $query = "SELECT id, question_id, answer_type, title, sort_order, image " .
            "FROM {$wpdb->prefix}ngs_answers " .
            "WHERE question_id IN (" . implode(', ', array_fill( 0, count( $question_ids ), '%d') ) . ") " .
        	"ORDER BY sort_order ASC";
        $answers = $wpdb->get_results( $wpdb->prepare( $query, ...$question_ids ) );
        
        if( empty( $answers ) ) {
            $answers = array();
        }
        
        foreach ( $questions as &$question ) {
            $question->answers = array();
            $question->columns = array();
            
            foreach ( $answers as $answer ) {
                if( $answer->question_id == $question->id ) {
                    if( $answer->answer_type == 'y' ) {
                        $question->columns[] = $answer;
                    } else {
                        $question->answers[] = $answer;
                    }
                }
            }
        }
        
        // Responses
        foreach ( $questions as &$question ) {
            $question->responses = array();
        }
    }

}
