<?php
/**
 * The file that defines the locations model class
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
 * The locations model class.
 *
 * This is used to define locations model data.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Model_Locations extends NgSurvey_Model {

    /**
     * Define the reports model functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Returns the list of last 10 responses.
     *
     * @param integer $survey_id the ID of the survey
     * @return stdClass the object encompasses the responses
     */
    public function get_list( $survey_id, $searchTerm, $order, $start, $length ) {
        global $wpdb;
        
        $orderBy = !empty( $order ) ? $order : 'a.responses DESC';
        $search = !empty($searchTerm) ? ' AND (country like %s OR state like %s OR city like %s)' : '';
        $term = '%' . $wpdb->esc_like( $searchTerm ) . '%';
        $vars = !empty($searchTerm) ? array( $survey_id,  $term, $term, $term) : array( $survey_id );
        $data = new stdClass();
        
        $query = 
            "SELECT c.country_name as country, state, city, count(*) as responses " .
            "FROM {$wpdb->prefix}ngs_tracking AS a " .
            "INNER JOIN {$wpdb->prefix}ngs_countries c ON c.country_code = a.country " .
            "WHERE a.post_id IN (SELECT id FROM {$wpdb->prefix}ngs_responses WHERE survey_id = %d) ";
        $groupBy =  "GROUP BY country, state, city ";
        
        $data->total = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM (" . $query . $groupBy . ") t", $vars ) );

        if( !empty( $search ) ) {
            $query = $query . $search;
            $data->filtered = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM (" . $query . $groupBy . ") t", $vars ) );
        } else {
            $data->filtered = $data->total;
        }
        
        $vars = array_merge( $vars, array( $length, $start ) );
        $data->responses = $wpdb->get_results( 
            $wpdb->prepare( 
                $query . 
                $groupBy .
                "ORDER BY " . $orderBy . " " .
                "LIMIT %d " . 
                "OFFSET %d", 
                $vars 
            ), 
            'OBJECT' 
        );

        return $data;
    }
}
