<?php
/**
 * The file that defines the rules model class
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
    public function get_rules ( $survey_id, $page_id ) {
        global $wpdb;
        
        $query =
        "SELECT a.id, a.title, a.survey_id, a.page_id, a.rule_content, a.rule_actions, a.sort_order " .
        "FROM {$wpdb->prefix}ngs_rules AS a " .
        "WHERE a.survey_id = %d AND a.page_id = %d";
        
        $rules = $wpdb->get_results( $wpdb->prepare( $query , $survey_id, $page_id ) );
        
        return $rules;
    }

    /**
     * Creates the rules with the given title.
     *
     * @param int $survey_id The id of the survey this rule belongs to.
     * @param int $page_id The id of the page this rule belongs to.
     * @param int $title The title of the rule
     *
     * @return array The response array, with error element if an error otherwise the rule_id
     */
    public function create ( $survey_id, $page_id, $title ) {
        global $wpdb;
        
        // Make sure number of existing questions in the page match with request
        $max_order = $wpdb->get_var( $wpdb->prepare(
            "SELECT max(sort_order) " .
            "FROM {$wpdb->prefix}ngs_rules " .
            "WHERE survey_id = %d AND page_id = %d ", 
            $survey_id, $page_id
        ) );
        
        $new_rule = array("survey_id" => $survey_id, "page_id" => $page_id, "title" => $title, "sort_order" => $max_order + 1, "rule_content" => "{}", "rule_actions" => "{}");
        $wpdb->insert(
            "{$wpdb->prefix}ngs_rules",
            $new_rule,
            array("%d", "%d", "%s","%d", "%s", "%s"));
        $new_rule['id'] = $wpdb->insert_id;
        
        return $new_rule;
    }
    
    public function update ( $survey_id, $page_id, $rule_id, $title, $rule ) {
        global $wpdb;
        $response = array();
        
        $wpdb->update(
            "{$wpdb->prefix}ngs_rules",
            $rule,
            array( 'id' => $rule_id, 'page_id' => $page_id, 'survey_id' => $survey_id ),
            array( '%s', '%s', '%s' ),
            array( '%d', '%d', '%d' )
            );
        
        return $response;
    }

    /**
     * Removes the selected rule from the page conditional rules.
     *
     * @param int $survey_id The id of the survey this rule belongs to.
     * @param int $page_id The page id of the rule
     * @param int $rule_id The id of the rule
     *
     * @return array The response array, with error element if an error otherwise the page_id
     */
    public function remove_rule ( $survey_id, $page_id, $rule_id ) {
        global $wpdb;
        $response = array();

        $wpdb->delete( 
            "{$wpdb->prefix}ngs_rules", 
            array( 'id' => $rule_id, 'page_id' => $page_id, 'survey_id' => $survey_id ), 
            array( '%d', '%d', '%d' ) );
        
        return $response;
    }

    /**
     * Updates the sort order of the rules of the given survey
     *
     * @param int $survey_id The id of the survey these rules belongs to.
     * @param int $page_id The id of the page this rule belongs to.
     * @param int $ordering The associative array of position-rule id ordering
     *
     * @return array The response object, with error element if an error
     */
    public function sort ( $survey_id, $page_id, $ordering ) {
        global $wpdb;
        $response = array();
        
        // First update with negative values so that the unique constraint will not fail
        $sql = "UPDATE {$wpdb->prefix}ngs_rules SET sort_order = CASE id ";
        foreach ( $ordering as $order => $rule_id ) {
            $sql = $sql . "WHEN " . ((int) $rule_id) . " THEN -" . ( (int) $order ) . " ";
        }
        $sql = $sql . "END WHERE survey_id = %d AND page_id = %d";
        $wpdb->query( $wpdb->prepare( $sql, $survey_id, $page_id ) );
        
        // Now flip the sign
        $sql = "UPDATE {$wpdb->prefix}ngs_rules SET sort_order = - sort_order WHERE survey_id = %d AND page_id = %d AND sort_order < 0";
        $wpdb->query( $wpdb->prepare( $sql, $survey_id, $page_id ) );
        
        return $response;
    }
}
