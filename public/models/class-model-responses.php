<?php
/**
 * The file that defines the responses model class
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

use DeviceDetector\DeviceDetector;

/**
 * The responses model class.
 *
 * This is used to define responses model data.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Model_Responses extends NgSurvey_Model {

    /**
     * Define the rules model functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct ( $config = array() ) {
        parent::__construct( $config );
    }

    /**
     * Gets the user answers of a given response id
     * 
     * @param integer $response_id the response id
     * @param number $page_id optional page id
     * @return array the list of user answers
     */
    public function get_response_details( $response_id, $page_id = 0 ) {
        global $wpdb;

        $query =
        "SELECT a.page_id, a.question_id, a.answer_id, a.column_id, a.answer_data ".
        "FROM {$wpdb->prefix}ngs_response_details AS a ".
        "WHERE a.response_id = %d";
        
        if( $page_id > 0 ) {
            $query = $query . " AND a.page_id = %d ";
        }
        
        $query = $query . " ORDER BY a.page_id ASC, a.question_id ASC";
        
        if( $page_id > 0 ) {
            $query = $wpdb->prepare( $query , $response_id, $page_id );
        } else {
            $query = $wpdb->prepare( $query , $response_id );
        }
        
        $responses = $wpdb->get_results( $query, 'ARRAY_A' );
        
        return !empty( $responses ) ? array_values( $responses ) : array();
    }

    /**
     * Checks if the user already completed the survey and return the appropriate message or response id
     * 
     * @param integer $survey_id the survey to check if the user has taken it
     * 
     * @return string[] the array with either the "error" message or "response_id" values
     */
    public function check_response ( $survey_id ) {
        global $wpdb;
        $cookie_name    = wp_hash( 'ngsurvey_response_key_' . $survey_id, 'nonce' );
        $survey_key     = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_text_field( $_COOKIE[ $cookie_name ] ) : sanitize_text_field( get_query_var( 'skey', null ) );
        $response       = array();
        $params         = get_post_meta( $survey_id, 'ngsurvey_settings', true );

        // Check if the response key is available in cookie, if yes validate it
        if( !empty( $survey_key ) ) {

            $response = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, created_by, created_date_gmt, finished_date_gmt ".
                "FROM {$wpdb->prefix}ngs_responses ".
                "WHERE survey_id = %d AND survey_key = %s",
                $survey_id, $survey_key
            ), 'ARRAY_A' );
            
            if( empty( $response ) ) {
                $status = new stdClass();
                
                // Check if there is already an existing invitation
                $status->exists = $this->check_survey_invitation($survey_id, $survey_key);
                
                if( ! $status->exists ) {
                    /*
                     * Checks if the survey key is created by the third party plugin. If yes, create the response with this.
                     *
                     * survey_key - the unique survey key from the request
                     */
                    $status = apply_filters( 'ngsurvey_check_survey_key', $status, $survey_id, $survey_key );
                }
                
                if( $status->exists ) {
                    $this->create_response( $survey_id, $survey_key );
                    
                    // Reload the response created
                    $response = $wpdb->get_row( $wpdb->prepare(
                        "SELECT id, created_by, created_date_gmt, finished_date_gmt ".
                        "FROM {$wpdb->prefix}ngs_responses ".
                        "WHERE survey_id = %d AND survey_key = %s",
                        $survey_id, $survey_key
                    ), 'ARRAY_A' );
                }
            }

            if( !empty( $response ) && $response[ 'finished_date_gmt' ] != '0000-00-00 00:00:00' ) {
                // The response is already finished.
                $response['error'] = __( 'You already took this survey. Thank you.', 'ngsurvey' );
                return $response;
            }
            
            // If we are here, the response does not exist or is not yet finalized. So return the response id, if exist.
            $response[ 'response_id' ] = !empty( $response[ 'id' ] ) ? $response[ 'id' ] : 0;

            return $response;
        }

        // If cookie restriction is enabled, check if there is a cookie response available
        if( empty( $survey_key) && !empty( $params[ 'tracking_method' ] ) && in_array( 'cookie', $params[ 'tracking_method' ] ) ) {
            
            $cookie_name = wp_hash( 'ngsurvey_response_id_' . $survey_id, 'nonce' );
            $id = (int) isset( $_COOKIE[ $cookie_name ] ) ? $_COOKIE[ $cookie_name ] : 0;
            if( $id ) {
                $response = $wpdb->get_row( $wpdb->prepare(
                    "SELECT id, created_by, created_date_gmt, finished_date_gmt ".
                    "FROM {$wpdb->prefix}ngs_responses ".
                    "WHERE id = %d AND survey_id = %s",
                    $id, $survey_id
                ), 'ARRAY_A' );
                
                if( !empty( $response ) && $response[ 'finished_date_gmt' ] != '0000-00-00 00:00:00' ) {
                    // The response is already finished.
                    $response['error'] = __( 'You already took this survey. Thank you.', 'ngsurvey' );
                    return $response;
                }
                
                // If we are here, the response does not exist or is not yet finalized. So return the response id, if exist.
                $response[ 'response_id' ] = !empty( $response[ 'id' ] ) ? $response[ 'id' ] : 0;
                
                return $response;
            }
        }

        // if the user is registered user, check if any response registered on their name
        $user_id = get_current_user_id();
        if( $user_id && !empty( $params[ 'tracking_method' ] ) && in_array( 'username', $params[ 'tracking_method' ] ) ) {

            $response = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, created_by, created_date_gmt, finished_date_gmt ".
                "FROM {$wpdb->prefix}ngs_responses ".
                "WHERE survey_id = %d AND created_by = %d",
                $survey_id, $user_id
            ), 'ARRAY_A' );
            
            if( !empty( $response ) && $response[ 'finished_date_gmt' ] != '0000-00-00 00:00:00' ) {
                // The response is already finished.
                $response['error'] = __( 'You already took this survey. Thank you.', 'ngsurvey' );
                return $response;
            }
            
            // If we are here, the response does not exist or is not yet finalized. So return the response id, if exist.
            $response[ 'response_id' ] = !empty( $response[ 'id' ] ) ? $response[ 'id' ] : 0;
            
            return $response;
        }
        
        // If the IP address restriction is enabled, check if there is already existing response with user IP address
        if( !empty( $params[ 'tracking_method' ] ) && in_array( 'ip', $params[ 'tracking_method' ] ) ) {
            
            $ip_address = NgSurvey_Utils::get_user_ip_address();
            
            if( !empty( $ip_address ) ) {
                $response = $wpdb->get_row( $wpdb->prepare(
                    "SELECT r.id, r.created_by, r.created_date_gmt, r.finished_date_gmt ".
                    "FROM {$wpdb->prefix}ngs_responses r ".
                    "INNER JOIN {$wpdb->prefix}ngs_tracking t ON r.post_id = r.id AND t.post_type = 2 ".
                    "WHERE survey_id = %d AND r.ip_address = %s",
                    $survey_id, $ip_address
                ), 'ARRAY_A' );
                
                if( !empty( $response ) && $response[ 'finished_date_gmt' ] != '0000-00-00 00:00:00' ) {
                    // The response is already finished.
                    $response['error'] = __( 'You already took this survey. Thank you.', 'ngsurvey' );
                    return $response;
                }
                
                // If we are here, the response does not exist or is not yet finalized. So return the response id, if exist.
                $response[ 'response_id' ] = !empty( $response[ 'id' ] ) ? $response[ 'id' ] : 0;
                
                return $response;
            }
        }

        // There is no existing response with cookie and user id. 
        return $response;
    }
    
    /**
     * Checks the survey key exists in the invitations.
     * They key is checked if it is already attempted, if not return true.
     *
     * @param string $survey_key the survey key to check
     *
     * @return true if the key exists and is not attempted, false otherwise
     */
    public function check_survey_invitation( $survey_id, $survey_key ) {
        global $wpdb;
        
        $invitation_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT a.id " .
            "FROM {$wpdb->prefix}ngs_invitations AS a " .
            "WHERE a.survey_id = %d AND a.survey_key = %s AND a.workflow_status < 2",
            $survey_id, $survey_key
        ) );
        
        if( $invitation_id ) {
            $wpdb->update(
                "{$wpdb->prefix}ngs_invitations",
                array(
                    'workflow_status' => '2',
                    'modified_by' => get_current_user_id(),
                    'last_modified_date' => current_time( 'mysql', false ),
                    'last_modified_date_gmt' => current_time( 'mysql', true )
                ),
                array( 'ID' => $invitation_id ),
                array(
                    '%d',
                    '%s',
                    '%s'
                ),
                array( '%d' )
            );
            
            return true;
        }
        
        return false;
    }

    /**
     * Creates the response record in database and return the id of the new response
     * 
     * @param integer $survey_id the survey id of the response
     * 
     * @return integer the response id
     */
    public function create_response ( $survey_id, $survey_key = null ) {
        global $wpdb;
        
        $map = array(
            'survey_id'         => $survey_id, 
            'survey_key'        => $survey_key ? $survey_key : NgSurvey_Utils::get_survey_key(), 
            'created_by'        => get_current_user_id(), 
            'created_date'      => current_time( 'mysql', false ),
            'created_date_gmt'  => current_time( 'mysql', true )
        );
        $wpdb->insert("{$wpdb->prefix}ngs_responses", $map, array( '%d', '%s', '%d', '%s', '%s' ) );
        $response_id            = $wpdb->insert_id;
        $cookie_name            = wp_hash( 'ngsurvey_response_id_' . $survey_id, 'nonce' );
        
        if( !$survey_key ) {
            // Cookie can only set when the response is being created in an ajax request.
            // The survey is loaded using the_content filter which has already sent response headers
            setcookie( $cookie_name, $response_id, time() + 60 * 60 * 24 * 365, COOKIEPATH, COOKIE_DOMAIN );
        }
        
        // capture respondent details.
        $ip_address             = NgSurvey_Utils::get_user_ip_address();
        $geographic             = array( 'ip_address' => $ip_address, 'country' => '', 'state' => '', 'city' => '');
        
        /*
         * Gets the demographic data of the user who is responding to this survey.
         * This filter can be used to populate the user city, state and country details by the implementing plugins.
         * 
         * An array argument will be passed to this filter which contains the following default values. 
         * The plugins should check if there are any values already populated by other plugins.
         * 
         * ip_address - IP address of the user
         * country - empty value
         * state - empty value
         * city - empty value
         */
        $geographic = apply_filters( 'ngsurvey_response_geographic_data', $geographic );
        
        $userAgent              = $_SERVER['HTTP_USER_AGENT'];
        $parser                 = new DeviceDetector( $userAgent );
        $parser->parse();

        $tracking   = array(
            'post_id'           => $response_id,
            'post_type'         => 2,
            'ip_address'        => $geographic['ip_address'],
            'created_date'      => current_time( 'mysql', true ),
            'country'           => $geographic['country'],
            'state'             => $geographic['state'],
            'city'              => $geographic['city'],
            'browser_name'      => $parser->getClient()[ 'name' ],
            'browser_version'   => $parser->getClient()[ 'version' ],
            'browser_engine'    => $parser->getClient()[ 'engine' ],
            'platform_name'     => $parser->getOs()[ 'name' ],
            'platform_version'  => $parser->getOs()[ 'version' ],
            'device_type'       => $parser->getDeviceName(),
            'brand_name'        => $parser->getBrandName(),
            'model_name'        => $parser->getModel()
        );

        $wpdb->insert("{$wpdb->prefix}ngs_tracking", $tracking, array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) );

        return $response_id;
    }

    /**
     * Checks if the response is already finialized or not.
     * 
     * @param integer $response_id the response id to check
     * 
     * @return boolean true if the response is finalized, false otherwise
     */
    public function is_response_finished( $response_id ) {
        global $wpdb;

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT count(*) " .
            "FROM {$wpdb->prefix}ngs_responses " .
            "WHERE id = %d AND finished_date > created_date", $response_id
        ) );
        
        return $count > 0;
    }

    /**
     * Saves the user answers for the given response id.
     * 
     * @param integer $survey_id the survey id
     * @param integer $page_id the current page id of the answers
     * @param integer $response_id the id of the user response
     * @param array $filtered_data list of user responses mapped to corresponding question id
     */
    public function save_response_data ( $survey_id, $page_id, $response_id, $filtered_data ) {
        global $wpdb;
        
        // First clear the data of the existing response of the page
        $wpdb->delete(
            "{$wpdb->prefix}ngs_response_details",
            array( 'response_id' => $response_id, 'page_id' => $page_id ),
            array( '%d', '%d' ) );

        // Now insert the new data
        $records = array();
        foreach ( $filtered_data as $question_id => $responses ) {
            foreach ( $responses as $response ) {
                // response_id, page_id, question_id, answer_id, column_id, answer_data
                $records[] = $wpdb->prepare( 
                    "(%d,%d,%d,%d,%d,%s)", 
                    $response_id, $page_id, $question_id, $response[ 'answer_id' ], $response[ 'column_id' ], $response[ 'answer_data' ] 
                );
            }
        }
        
        if( empty( $records ) ) {
            return true;
        }
        
        $query = "INSERT INTO {$wpdb->prefix}ngs_response_details (response_id, page_id, question_id, answer_id, column_id, answer_data) VALUES ";
        $query .= implode( ",", $records );
        $wpdb->query($query);
        
        return true;
    }

    /**
     * Finalizes the response and update all response details.
     *
     * @param integer $survey_id the survey id
     * @param integer $response_id the id of the user response
     */
    public function finalize_response ( $survey_id, $response_id ) {
        global $wpdb;
        
        // Finalize the response
        $wpdb->update(
            "{$wpdb->prefix}ngs_responses",
            array( 
                'finished_date' => current_time( 'mysql', false ), 
                'finished_date_gmt' => current_time( 'mysql', true )
            ),
            array( 
                'id' => $response_id, 
                'survey_id' => $survey_id
            ),
            array( '%s', '%s' ),
            array( '%d', '%d' )
        );
        
        // Finalize invitation if any
        $survey_key = $wpdb->get_var( $wpdb->prepare(
            "SELECT a.survey_key " .
            "FROM {$wpdb->prefix}ngs_responses AS a " .
            "WHERE a.id = %d",
            $response_id
        ) );
        
        if( !empty( $survey_key ) ) {
            $invitation_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT a.id " .
                "FROM {$wpdb->prefix}ngs_invitations AS a " .
                "WHERE a.survey_id = %d AND a.survey_key = %s AND a.workflow_status = 2",
                $survey_id, $survey_key
            ) );
            
            if( $invitation_id ) {
                $wpdb->update(
                    "{$wpdb->prefix}ngs_invitations",
                    array(
                        'workflow_status' => '3',
                        'modified_by' => get_current_user_id(),
                        'last_modified_date' => current_time( 'mysql', false ),
                        'last_modified_date_gmt' => current_time( 'mysql', true )
                    ),
                    array( 'ID' => $invitation_id ),
                    array(
                        '%d',
                        '%s',
                        '%s'
                    ),
                    array( '%d' )
                );
                
                return true;
            }
        }
        
        return false;
    }
}
