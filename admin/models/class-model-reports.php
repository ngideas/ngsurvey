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
class NgSurvey_Model_Reports extends NgSurvey_Model {

    /**
     * Define the reports model functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Returns the responses count by date. 
     * 
     * @param integer $survey_id the ID of the survey
     * @return array list of datewise responses counts
     */
    public function get_count_responses_by_date( $survey_id ) {
        global $wpdb;
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT date(created_date) AS cdate, count(*) AS responses ".
            "FROM {$wpdb->prefix}ngs_responses ".
            "WHERE survey_id = %d ".
            "GROUP BY date(created_date) ".
            "ORDER BY cdate", 
            $survey_id
        ), 'OBJECT' );
        return $results;
    }
    
    /**
     * Returns the responses count by country.
     *
     * @param integer $survey_id the ID of the survey
     * @return array list of countrywise responses counts
     */
    public function get_count_response_by_country( $survey_id ) {
        global $wpdb;
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT country, count(*) AS responses ".
            "FROM {$wpdb->prefix}ngs_tracking ".
            "WHERE post_type = 2 AND post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) ".
            "GROUP BY country " .
            "ORDER BY created_date ASC",
            $survey_id
        ), 'OBJECT' );
        
        return $results;
    }
    
    /**
     * Returns the responses count by locations.
     *
     * @param integer $survey_id the ID of the survey
     * @return array list of locationwise responses counts
     */
    public function get_count_response_by_locations( $survey_id ) {
        global $wpdb;
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT c.country_name as label, count(*) AS value ".
            "FROM {$wpdb->prefix}ngs_tracking a ".
            "INNER JOIN {$wpdb->prefix}ngs_countries c ON c.country_code = a.country " .
            "WHERE a.post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) ".
            "GROUP BY a.country " .
            "ORDER BY value DESC " .
            "LIMIT 20",
            $survey_id 
        ), 'OBJECT' );
        
        return $results;
    }

    /**
     * Returns the responses count by platform.
     *
     * @param integer $survey_id the ID of the survey
     * @return array list of platformwise responses counts
     */
    public function get_count_response_by_platforms( $survey_id ) {
        global $wpdb;
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT platform_name as label, count(*) AS value ".
            "FROM {$wpdb->prefix}ngs_tracking ".
            "WHERE post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) ".
            "GROUP BY platform_name",
            $survey_id
        ), 'OBJECT' );
        
        return $results;
    }

    /**
     * Returns the responses count by browsers.
     *
     * @param integer $survey_id the ID of the survey
     * @return array list of browserwise responses counts
     */
    public function get_count_response_by_browsers( $survey_id ) {
        global $wpdb;
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT browser_name as label, count(*) AS value ".
            "FROM {$wpdb->prefix}ngs_tracking ".
            "WHERE post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) ".
            "GROUP BY browser_name",
            $survey_id
        ), 'OBJECT' );
        
        return $results;
    }
    
    /**
     * Returns the responses count by devices.
     *
     * @param integer $survey_id the ID of the survey
     * @return array list of deviceswise responses counts
     */
    public function get_count_response_by_devices( $survey_id ) {
        global $wpdb;
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT device_type as label, count(*) AS value ".
            "FROM {$wpdb->prefix}ngs_tracking ".
            "WHERE post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) ".
            "GROUP BY device_type",
            $survey_id 
        ), 'OBJECT' );
        
        return $results;
    }

    /**
     * Returns the count of pending and completed responses of the given survey.
     * 
     * @param integer $survey_id the ID of the survey
     * @return stdClass the object encompasses the counts
     */
    public function get_pending_and_completed_count( $survey_id ) {
        global $wpdb;
        
        $completed = $wpdb->get_var( 
            $wpdb->prepare( "SELECT count(*) FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d AND created_date < finished_date", $survey_id )
        );
        
        $pending = $wpdb->get_var(
            $wpdb->prepare( "SELECT count(*) FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d AND created_date > finished_date", $survey_id )
        );
        
        $result = array(
            [ 'label' => __( 'Completed', 'ngsurvey' ), 'value' => $completed ],
            [ 'label' => __( 'Pending', 'ngsurvey' ), 'value' => $pending ]
        );
        
        return $result;
    }

    public function get_survey_tracking_stats( $survey_id ) {
        global $wpdb;
        
        $locations = ( int ) $wpdb->get_var( $wpdb->prepare(
            "SELECT count(distinct(city)) " .
            "FROM {$wpdb->prefix}ngs_tracking " .
            "WHERE post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) " .
            "GROUP BY city",
            $survey_id 
        ) );

        $browsers = ( int ) $wpdb->get_var( $wpdb->prepare( 
            "SELECT count(distinct(browser_name)) " .
            "FROM {$wpdb->prefix}ngs_tracking " .
            "WHERE post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) " .
            "GROUP BY browser_name", 
            $survey_id 
        ) );
        
        $oses = ( int ) $wpdb->get_var( $wpdb->prepare(
            "SELECT count(distinct(platform_name)) " .
            "FROM {$wpdb->prefix}ngs_tracking " .
            "WHERE post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) " .
            "GROUP BY platform_name",
            $survey_id 
        ) );

        $devices = ( int ) $wpdb->get_var( $wpdb->prepare(
            "SELECT count(distinct(device_type)) " .
            "FROM {$wpdb->prefix}ngs_tracking " .
            "WHERE post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) " .
            "GROUP BY device_type",
            $survey_id 
        ) );

        $result = array(
            [ 'label' => __( 'Locations', 'ngsurvey' ), 'value' => $locations ],
            [ 'label' => __( 'Browsers', 'ngsurvey' ), 'value' => $browsers ],
            [ 'label' => __( 'Operating Systems', 'ngsurvey' ), 'value' => $oses ],
            [ 'label' => __( 'Device Types', 'ngsurvey' ), 'value' => $devices ]
        );

        return $result;
    }

    /**
     * Returns the list of last 10 responses.
     *
     * @param integer $survey_id the ID of the survey
     * @return stdClass the object encompasses the responses
     */
    public function get_latest_responses( $survey_id, $limit, $length ) {
        global $wpdb;
        
        $responses = $wpdb->get_results( $wpdb->prepare( 
            "SELECT a.id, a.created_by, a.created_date_gmt, a.finished_date_gmt, u.display_name, a.created_date < a.finished_date as finished ". 
            "FROM {$wpdb->prefix}ngs_responses AS a " .
            "LEFT JOIN {$wpdb->prefix}users AS u ON u.ID = a.created_by " .
            "WHERE survey_id = %d " .
            "ORDER BY created_date DESC " .
            "LIMIT %d " . 
            "OFFSET %d", 
            array( $survey_id, $length, $limit ) 
        ), 'OBJECT' );

        return $responses;
    }
}
