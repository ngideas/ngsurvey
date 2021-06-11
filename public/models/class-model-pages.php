<?php
/**
 * The file that defines the pages model class
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
        
        $rules = $wpdb->get_results( $wpdb->prepare( $query , $survey_id ) );
        
        return $rules;
    }
}
