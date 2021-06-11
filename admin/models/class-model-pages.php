<?php
/**
 * The file that defines the pages model class
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
 * The pages model class.
 *
 * This is used to define pages model data.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Model_Pages extends NgSurvey_Model {

    /**
     * Define the pages model functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Gets the pages of the given survey id.
     *
     * @param int $survey_id The id of the survey to get the pages from.
     *
     * @return array The list of Page objects
     */
    public function get_pages ( $survey_id ) {
        global $wpdb;
        
        $query =
        "SELECT a.id, a.title, sort_order " .
        "FROM {$wpdb->prefix}ngs_pages AS a " .
        "WHERE a.survey_id = %d " .
        "ORDER BY sort_order ASC";
        
        $pages = $wpdb->get_results( $wpdb->prepare( $query , $survey_id ) );
        
        return $pages;
    }

    /**
     * Creates the page with the given title.
     *
     * @param int $survey_id The id of the survey this page belongs to.
     * @param int $title The title of the page
     *
     * @return array The response array, with error element if an error otherwise the page_id
     */
    public function create ( $survey_id, $title ) {
        global $wpdb;
        
        // Make sure number of existing questions in the page match with request
        $max_order = $wpdb->get_var( $wpdb->prepare(
            "SELECT max(sort_order) " .
            "FROM {$wpdb->prefix}ngs_pages " .
            "WHERE survey_id = %d ", 
            $survey_id
        ) );
        
        $new_page = array("survey_id" => $survey_id, "title" => $title, "sort_order" => $max_order + 1);
        $wpdb->insert(
            "{$wpdb->prefix}ngs_pages",
            $new_page,
            array("%d", "%s","%d"));
        $new_page['id'] = $wpdb->insert_id;
        
        return $new_page;
    }

    /**
     * Updates the page title of the given page id.
     *
     * @param int $page_id The id of the page this question belongs to.
     * @param int $title The new title of the page
     *
     * @return array The response array, with error element if an error
     */
    public function update ( $page_id, $title ) {
        global $wpdb;
        $response = array();

        $wpdb->update(
            "{$wpdb->prefix}ngs_pages",
            array(
                'title' => $title,
            ),
            array( 'id' => $page_id ),
            array( '%s' ),
            array( '%d' )
        );
        
        return $response;
    }
    
    /**
     * Updates the sort order of the pages of the given survey
     *
     * @param int $survey_id The id of the survey these pages belongs to.
     * @param int $ordering The associative array of position-page id ordering
     *
     * @return array The response object, with error element if an error
     */
    public function sort ( $survey_id, $ordering ) {
        global $wpdb;
        $response = array();
        
        // First update with negative values so that the unique constraint will not fail
        $sql = "UPDATE {$wpdb->prefix}ngs_pages SET sort_order = CASE id ";
        foreach ( $ordering as $order => $page_id ) {
            $sql = $sql . "WHEN " . ((int) $page_id) . " THEN -" . ( (int) $order ) . " ";
        }
        $sql = $sql . "END WHERE survey_id = %d";
        $wpdb->query( $wpdb->prepare( $sql, $survey_id ) );
        
        // Now flip the sign
        $sql = "UPDATE {$wpdb->prefix}ngs_pages SET sort_order = - sort_order WHERE survey_id = %d AND sort_order < 0";
        $wpdb->query( $wpdb->prepare( $sql, $survey_id ) );
        
        return $response;
    }
}
