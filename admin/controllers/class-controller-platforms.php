<?php
/**
 * The file that defines the platforms controller class
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
 * The platforms controller class.
 *
 * This is used to define platforms controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Platforms extends NgSurvey_Controller {

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

                    switch ( $ordering_column ) {
                        case 'platform_name':
                        case 'platform_version':
                        case 'responses':
                            $ordering = $ordering . ( !empty( $ordering ) ? ',' : '' ) . $ordering_column . ' ' . $direction;
                            break;
                    }
                }
            }
        }

        $ordering = sanitize_sql_orderby( $ordering );
        
        $reports_model = $this->get_model( 'platforms' );
        $data = $reports_model->get_list( $survey_id, $searchTerm, $ordering, $start, $length );
        
        $return = array( 
            'success' => true,
            'draw' => $draw,
            'recordsTotal' => $data->total, 
            'recordsFiltered' => $data->filtered,
            'data' => $data->responses
        );

        wp_send_json( $return, 200 );
    }
}
