<?php
/**
 * The file that defines the reports model class
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
 * This is used to define reports model data.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Model_Responses extends NgSurvey_Model {

    /**
     * Define the reports model functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Returns the list of responses.
     * 
     * @param integer $survey_id the ID of the survey
     * @param string $searchTerm search string, if any, otherwise null
     * @param string $order the default ordering
     * @param number $limit number of records to fetch
     * @param number $offset start index of the records
     * 
     * @return stdClass the object with list of <code>responses</code> and <code>total</code> count.
     */
    public function get_list( $survey_id, $searchTerm = null, $order = null, $limit = 0, $offset = 0 ) {
        global $wpdb;
        
        $orderBy = !empty( $order ) ? $order : 'a.id DESC';
        $search = $searchTerm ? ' AND display_name like %s' : '';
        $vars = $searchTerm ? array( $survey_id, '%' . $wpdb->esc_like( $searchTerm ) . '%' ) : array( $survey_id );
        $data = new stdClass();
        
        $fromWhere = 
            "FROM {$wpdb->prefix}ngs_responses AS a " .
            "LEFT JOIN {$wpdb->prefix}ngs_tracking AS t ON t.post_id = a.id AND t.post_type = 2  " .
            "LEFT JOIN {$wpdb->prefix}users AS u ON u.ID = a.created_by " .
            "WHERE a.survey_id = %d";
        $data->total = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) " . $fromWhere, $vars ) );

        if( !empty( $search ) ) {
            $fromWhere = $fromWhere . $search;
            $data->filtered = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) " . $fromWhere, $vars ) );
        } else {
            $data->filtered = $data->total;
        }
        
        // Add length to the query if set
        $limitQuery = " ";
        if( $limit ) {
            $limitQuery = "LIMIT %d OFFSET %d";
            $vars[] = $limit;
            $vars[] = $offset;
        }
        
        $data->responses = $wpdb->get_results( 
            $wpdb->prepare( 
                "SELECT a.id, a.created_by, a.created_date_gmt, a.finished_date_gmt, u.display_name, a.created_date < a.finished_date as finished, " .
                "t.country, t.state, t.city, t.browser_name, t.browser_version, t.browser_engine, t.platform_name, t.platform_version, t.device_type, t.brand_name, t.model_name " .
                $fromWhere . " " .
                "ORDER BY " . $orderBy . " " .
                $limitQuery, 
                $vars 
            ), 
            'OBJECT' 
        );

        return $data;
    }
    
    /**
     * Gets the detailed report of the responses counts grouped by question answer
     * 
     * @param integer $survey_id the id of the survey 
     * @param integer $response_id the id of the response, if 0, then all responses are considered
     * @param string $order the ordering of the responses
     * @param number $offset the start index of the records being retieved
     * @param number $limit number of records to retrieve
     * 
     * @return array the list of records 
     */
    public function get_responses_report( $survey_id, $response_id = 0, $order = null, $offset = 0, $limit = 0 ) {
        global $wpdb;
        
        $vars = array();
        $whereQuery = "";

        if( $response_id ) {
            $whereQuery = "a.response_id = %d";
            $vars[] = $response_id;
        } else {
            $whereQuery = "a.response_id IN (select id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d)";
            $vars[] = $survey_id;
        }
        
        $orderBy = !empty( $order ) ? $order : 'a.page_id ASC, a.question_id ASC';
        
        // Add length to the query if set
        $limitQuery = " ";
        if( $limit ) {
            $limitQuery = "LIMIT %d OFFSET %d";
            $vars[] = $limit;
            $vars[] = $offset;
        }
        
        // Get all counts of the responses grouped by page and question
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.page_id, a.question_id, a.answer_id, a.column_id, count(*) as responses ".
                "FROM {$wpdb->prefix}ngs_response_details AS a " .
                "WHERE " . $whereQuery . " " .
                "GROUP BY a.page_id, a.question_id, a.answer_id, a.column_id " .
                "ORDER BY " . $orderBy . " " .
                $limitQuery,
                $vars
                ),
            'OBJECT'
        );

        return $data;
    }
    
    /**
     * Gets the data of a single response
     *
     * @param integer $survey_id the id of the survey
     * @param integer $response_id the id of the response, if 0, then all responses are considered
     *
     * @return array the list of answers given by the user to a response
     */
    public function get_single_response( $response_id ) {
        global $wpdb;
        
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.page_id, a.question_id, a.answer_id, a.column_id, answer_data ".
                "FROM {$wpdb->prefix}ngs_response_details AS a " .
                "WHERE a.response_id = %d",
                $response_id
            ),
            'ARRAY_A'
        );
        
        return $data;
    }
    
    /**
     * Gets the custom answers of the given question id
     * 
     * @param integer $survey_id the id of the survey
     * @param integer $question_id the id of the question
     * @param number $limit number of items to get
     * @param number $offset start index of to retrieve the records from
     * @return array the list of custom answers objects
     */
    public function get_custom_answers($survey_id, $question_id, $limit = 0, $offset = 0) {
        global $wpdb;

        $vars = array($survey_id, $question_id);
        $limitQuery = " ";
        
        if( $limit ) {
            $limitQuery = "LIMIT %d OFFSET %d";
            $vars[] = $limit;
            $vars[] = $offset;
        }
        
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.response_id, a.page_id, a.question_id, a.answer_id, a.column_id, a.answer_data, r.created_date,
                    c.country_name, t.state, t.city, t.browser_name, t.platform_name, platform_version, t.device_type
                FROM {$wpdb->prefix}ngs_response_details a
                INNER JOIN {$wpdb->prefix}ngs_responses r ON a.response_id = r.id
                INNER JOIN {$wpdb->prefix}ngs_tracking t ON t.post_id = a.response_id AND t.post_type = 2
                LEFT JOIN {$wpdb->prefix}ngs_countries c ON c.country_code = t.country
                WHERE r.survey_id = %d AND a.question_id = %d AND a.answer_id = 1 AND a.answer_data IS NOT NULL AND a.answer_data != ''
                ORDER BY r.created_date DESC " . 
                $limitQuery,
                $vars 
            ),
            'OBJECT'
        );
        
        return $data;
    }
    
    public function get_total_responses_by_question( $survey_id ) {
        global $wpdb;

        $data = $wpdb->get_results($wpdb->prepare(
            "SELECT a.question_id, count(distinct a.response_id ) AS responses " .
            "FROM {$wpdb->prefix}ngs_response_details a " .
            "WHERE a.response_id IN (select id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) " .
            "GROUP BY a.question_id ",
            $survey_id
        ), 'OBJECT_K');
        
        return $data ? $data : array();
    }
    
    /**
     * Deletes the given responses of the survey.
     * 
     * @param integer $survey_id the survey id
     * @param array $response_ids list of response ids to delete
     * 
     * @return boolean true if successful else false
     */
    public function delete_responses( $survey_id, $response_ids ) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}ngs_responses " .
            "WHERE survey_id = %d AND id IN (" . implode( ",", $response_ids ) . ")",
            $survey_id
        ));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}ngs_response_details " .
            "WHERE response_id IN (" . implode( ",", $response_ids ) . ")",
        ));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}ngs_tracking " .
            "WHERE post_id IN (" . implode( ",", $response_ids ) . ") AND post_type = 2",
        ));
        
        return true;
    }
}
