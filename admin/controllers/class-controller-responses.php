<?php
/**
 * The file that defines the questions controller class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/controllers
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The questions controller class.
 *
 * This is used to define questions controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Responses extends NgSurvey_Controller {

	/**
	 * Define the questions controller of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($config = array()) {
	    parent::__construct($config);
	}
	
	/**
	 * Renders the survey report
	 *
	 * @return void nothing
	 */
	public function display () {
        $draw          = (int) $_POST[ 'draw' ];
        $start         = (int) $_POST[ 'start' ];
        $length        = (int) $_POST[ 'length' ];
        $survey_id     = (int) $_POST[ 'ngform' ][ 'sid' ];
        
        if( !$survey_id ) {
            $this->raise_error();
        }
        
        // Check user authorization
        $this->authorise( $survey_id );
        
        if( $length > 1000 || $length < 0 ) {
            $length = 15;
        }

        $searchTerm = isset( $_POST[ 'search' ][ 'value' ] ) ? sanitize_text_field( $_POST[ 'search' ][ 'value' ] ) : null;
        $ordering = '';
        $direction = 'desc';

        if( is_array( $_POST[ 'order' ] ) ) {
            foreach ( $_POST[ 'order' ] as $order ) {
                if( isset( $order[ 'column' ] ) && isset( $_POST[ 'columns' ][ $order['column'] ][ 'data' ] ) ) {

                    $ordering_column = sanitize_key( $_POST[ 'columns' ][ $order['column'] ][ 'data' ] );
                    $direction = isset( $order[ 'dir' ] ) && strtolower( $order[ 'dir' ] ) == 'desc' ? 'desc' : 'asc';

                    if( in_array( $ordering_column, array( 'id', 'display_name', 'created_date_gmt', 'finished_date_gmt', 'finished' ) ) ) {
                        $ordering = $ordering . ( !empty( $ordering ) ? ',' : '' ) . $ordering_column . ' ' . $direction; 
                    }
                }
            }
        }
        
        $ordering = sanitize_sql_orderby( $ordering );

        $reports_model = $this->get_model( 'responses' );
        $data = $reports_model->get_list( $survey_id, $searchTerm, $ordering, $length, $start );
        
        foreach ( $data->responses as &$response ) {
            if( $response->finished ) {
                $response->status = __( 'Response completed', 'ngsurvey' );
            } else {
                $response->status = __( 'Response pending', 'ngsurvey' );
            }
            
            if( empty( $response->display_name ) ) {
                $response->display_name = __( 'Guest', 'ngsurvey' );
            }
            
            $response->result = 
            '<a href="javascript:void(0);" class="btn-view-response" data-id="'.$response->id.'" data-bs-toggle="modal" data-bs-target="#response-details-modal">' 
                . __( 'View', 'ngsurvey' ) . 
            '</a>';
        }

        $return = array( 
            'success' => true,
            'draw' => $draw,
            'recordsTotal' => $data->total, 
            'recordsFiltered' => $data->filtered,
            'data' => $data->responses
        );

        wp_send_json( $return, 200 );
    }
    
    /**
     * Function to delete the responses
     */
    public function delete() {
        $survey_id     = (int) $_POST[ 'ngform' ][ 'sid' ];
        $response_ids  = array_map('intval', $_POST[ 'rid' ] );
        
        if( !$survey_id || empty( $response_ids ) ) {
            $this->raise_error();
        }
        
        // Check user authorization
        $this->authorise( $survey_id );
        
        $responses_model   = $this->get_model( 'responses' );
        if( $responses_model->delete_responses( $survey_id, $response_ids ) ) {
            wp_send_json_success( $response_ids );
        } else {
            $this->raise_error( 500, __( 'Error occurred while deleting the responses', 'ngsurvey' ) );
        }
    }
    
    /**
     * Custom button actions are defined in this function
     */
    public function custom() {
        wp_send_json_success( $data );
    }
}
