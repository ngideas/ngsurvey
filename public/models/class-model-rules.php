<?php
/**
 * The file that defines the rules model class
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
 * The rules model class.
 *
 * This is used to define rules model data.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Model_Rules extends NgSurvey_Model {

    /**
     * Define the rules model functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }
    
    /**
     * Gets the conditional rules of the given survey id.
     *
     * @param int $survey_id The id of the survey to get the rules from.
     *
     * @return array The list of Rule objects
     */
    public function get_rules ( $survey_id, $page_id = 0 ) {
        global $wpdb;
        
        $query =
        "SELECT a.id, a.title, a.survey_id, a.page_id, a.rule_content, a.rule_actions, a.sort_order " .
        "FROM {$wpdb->prefix}ngs_rules AS a " .
        "WHERE a.survey_id = %d";
        
        if( $page_id ) {
            $query = $wpdb->prepare( $query . " AND a.page_id = %d", $survey_id, $page_id );
        } else {
            $query = $wpdb->prepare( $query, $survey_id );
        }

        $rules = $wpdb->get_results( $query );
        
        return $rules;
    }

}
