<?php
/**
 * The file that defines the questions model class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
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
        "SELECT a.id, p.survey_id, a.title, a.description, a.qtype, a.params, p.id as page_id ".
        "FROM {$wpdb->prefix}ngs_pages_questions_map AS m ".
        "INNER JOIN {$wpdb->prefix}ngs_pages AS p ON p.id = m.page_id ".
        "INNER JOIN {$wpdb->prefix}ngs_questions AS a ON a.id = m.question_id ".
        "WHERE p.survey_id = %d";
        
        if( $page_id === 0 ) {
            $query = $query . " AND p.sort_order = 1 ";
        } else if( $page_id > 0 ) {
            $query = $query . " AND m.page_id = %d ";
        }
        
        $query = $query . " ORDER BY p.sort_order ASC, m.sort_order ASC";

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
        if( empty( $question) ) {
            return false;
        }
        
        $questions = array( (object) $question );
        $this->initialize_questions( $questions );
        
        return $questions[0];
    }

    /**
     * Creates a new question with default data and returns the question.
     *
     * @param int $survey_id The id of the survey this question belongs to.
     * @param int $page_id The id of the page this question belongs to.
     * @param int $type The question type.
     *
     * @return array The list of Rule objects
     */
    public function create ( $survey_id, $page_id, $type, $title ) {
        global $wpdb;
        
        $question = array("title" => $title, "description" => "", "qtype" => $type );
        $wpdb->insert("{$wpdb->prefix}ngs_questions", $question);
        $question[ 'id' ] = $wpdb->insert_id;
        
        $sort_order = $wpdb->get_var( $wpdb->prepare("SELECT MAX(sort_order) FROM {$wpdb->prefix}ngs_pages_questions_map WHERE page_id = %d", $page_id) );
        
        $map = array('page_id' => $page_id, 'question_id' => $question[ 'id' ], 'sort_order' => $sort_order + 1);
        $wpdb->insert("{$wpdb->prefix}ngs_pages_questions_map", $map);
        
        return $this->get_question( $question[ 'id' ] );
    }

    /**
     * Updates the sort order of the questions in the given page
     *
     * @param int $page_id The id of the page this question belongs to.
     * @param int $ordering The associative array of position-question id ordering
     *
     * @return array The response object, with error element if an error
     */
    public function sort ( $page_id, $ordering ) {
        global $wpdb;
        $response = array();
        
        // Make sure number of existing questions in the page match with request
        $count = $wpdb->get_var( $wpdb->prepare( 
            "SELECT count(*) " .
            "FROM {$wpdb->prefix}ngs_pages_questions_map " .
            "WHERE page_id = %d " .
            "ORDER BY sort_order ASC", $page_id 
        ) );
        
        if( $count < count( $ordering ) ) {
            $response['error'] = __( 'Count of questions does not match the existing questions.', 'ngsurvey' );
            return $response;
        }
        
        // First delete the existing page-question mappings
        $wpdb->delete( "{$wpdb->prefix}ngs_pages_questions_map", array( 'page_id' => $page_id ), array( '%d' ) );
        
        // Now insert new mappings
        foreach ( $ordering as $order => $question_id ) {
            $map = array('page_id' => $page_id, 'question_id' => $question_id, 'sort_order' => $order);
            $wpdb->insert("{$wpdb->prefix}ngs_pages_questions_map", $map);
        }
        
        return $response;
    }
    
    /**
     * Moves the question to another page 
     *
     * @param int $survey_id The id of the survey this page belongs to.
     * @param int $page_id The id of the new page to move the question
     * @param int $question_id The id of the question to move
     *
     * @return array The response object, with error element if an error
     */
    public function move ( $survey_id, $old_page_id, $new_page_id, $question_id ) {
        global $wpdb;
        $response = array();
        
        // First delete the existing page-question mappings
        $wpdb->update(
            "{$wpdb->prefix}ngs_pages_questions_map",
            array( 'page_id' => $new_page_id ),
            array( 'page_id' => $old_page_id, 'question_id' => $question_id ),
            array( '%d' ),
            array( '%d' )
        );
        
        return $response;
    }
    
    /**
     * Removes the question mapping from the selected survey and page. If there are no other surveys using this question,
     * then the question object will be permanently deleted from the database.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $page_id      The id of the page
     */
    public function remove ( $question, $page_id ) {
        
        global $wpdb;
        
        $wpdb->delete(
            "{$wpdb->prefix}ngs_pages_questions_map",
            array( 'question_id' => $question->id, 'page_id' => $page_id ),
            array( '%d', '%d' ) );
        
        $map_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}ngs_pages_questions_map WHERE question_id = %d", $question->id) );
        
        // Delete the question and answers if there are no mappings found
        if($map_count  == 0) {
            $wpdb->delete("{$wpdb->prefix}ngs_questions", array( 'id' => $question->id ), array( '%d' ) );
            $wpdb->delete("{$wpdb->prefix}ngs_answers", array( 'question_id' => $question->id ), array( '%d' ) );
            //$wpdb->delete("{$wpdb->prefix}ngs_rules", array( 'question_id' => $question->id ), array( '%d' ) );
        }
        
        return true;
    }

    /**
     * Gets all questions in the page and delegates the remove action to them.
     *
     * @param int $survey_id The id of the survey this page belongs to.
     * @param int $page_id The id of the page
     *
     * @return array The response array, with error element if an error otherwise the page_id
     */
    public function remove_page ( $survey_id, $page_id ) {
        global $wpdb;
        $response = array();
        $questions = $this->get_questions($survey_id, $page_id);
        
        // Delete/unmap questions
        foreach ( $questions as $question ) {
            if( !$this->remove( $question, $page_id ) ) {
                $response['error'] = __( 'An error occurred while deleting the page.', 'ngsurvey' );
                return $response;
            }
            apply_filters( 'ngsurvey_remove_question', $question );
        }
        
        // Delete the page
        $wpdb->delete( "{$wpdb->prefix}ngs_pages", array( 'id' => $page_id, 'survey_id' => $survey_id ), array( '%d', '%d' ) );
        $wpdb->delete( "{$wpdb->prefix}ngs_rules", array( 'page_id' => $page_id, 'survey_id' => $survey_id ), array( '%d', '%d' ) );

        return $response;
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

        foreach ( $questions as &$question ) {
            $question->params = new NgSurvey_Registry( $question->params );
            $question_ids[] = $question->id;
        }

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
    }
}
