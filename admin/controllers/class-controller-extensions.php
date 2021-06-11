<?php
/**
 * The file that defines the extensions controller class
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
 * The extensions controller class.
 *
 * This is used to define extensions controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Extensions extends NgSurvey_Controller {

	/**
	 * Define the extensions controller of the plugin.
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
	    $data = array();
	    $data[ 'template' ]    = $this->template;
	    $data[ 'extensions' ]  = json_decode( file_get_contents( NGSURVEY_PATH . 'admin/ngsurvey_extensions.json' ), true );
	    if( empty( $data[ 'extensions' ] ) ) {
	        echo esc_html__( 'An error occurred while fetching data. Please try again.', 'ngsurvey' );
	        return;
	    }
	    
	    $plugins = get_plugins();
	    $data[ 'plugins' ] = array();
	    
	    foreach ( $plugins as $plugin ) {
	        if( isset( $plugin[ 'NgSurvey ID' ] ) && isset( $plugin[ 'NgSurvey Type' ] ) && in_array( $plugin[ 'NgSurvey Type' ], array( 'Extension' ) ) ) {
	            $data[ 'plugins' ][] = $plugin;
	        }
	    }
	    
	    $data[ 'licenses' ] = get_option( NGSURVEY_LICENSES, array() );
	    
	    $this->template->set_template_data( $data )->get_template_part( 'admin/survey_extensions' );
	}
	
	public function activate() {
	    $product_id        = sanitize_key( $_POST[ 'product_id' ] );
	    $license_email     = sanitize_email( $_POST[ 'license_email' ] );
	    $license_key       = sanitize_text_field( $_POST[ 'license_key' ] );
	    $protocol 	       = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    
	    if( empty( $product_id ) || empty( $license_email ) || empty( $license_key ) ) {
	        $this->raise_error(403, __( 'Missing required fields, please try again.', 'ngsurvey' ) );
	    }
	    
	    // Build the arguments
	    $args = array(
	        'request'     => 'activation',
	        'email'       => $license_email,
	        'license_key' => $license_key,
	        'product_id'  => $product_id,
	    	'instance' 	  => str_replace( $protocol, "", get_bloginfo( 'wpurl' ) ),
	        'secret_key'  => '',
	    );
	    
	    $base_url = add_query_arg( 'wc-api', 'software-api', NGSURVEY_ACTIVATION_URI );
	    $base_url = $base_url . '&' . http_build_query( $args );
	    
	    $data = wp_remote_get( $base_url );
	    
    	if( is_array( $data ) && ! is_wp_error( $data ) && !empty( $data[ 'body' ] ) ) {
	        $response = json_decode( $data[ 'body' ] );
	        NGLOG::info( "Successfully sent the activation request", array( 'args' => $args, 'response' => $response ) );
	        
	        // If the license is successfully activated, we need to add it to local database
	        if( $response->activated ) {
	            $licenses  = get_option( NGSURVEY_LICENSES, array() );
	            $license   = array_search( $product_id, array_column( $licenses, 'product_id' ) );
	            
	            // If the license is not found, add it else update it
	            $args[ 'active' ] = true;
	            unset( $args[ 'request' ] );
	            
	            if( $license !== false ) {
	                array_push( $licenses, $args );
	            } else {
	                $licenses[] = $args;
	            }
	            
	            // Update the option
	            update_option( NGSURVEY_LICENSES, $licenses );
	            
	            // Now send the response back to the user with updated html
	            ob_start();
	            $this->display();
	            $output = ob_get_clean();
	            
	            wp_send_json_success( $output );
	        }
    	} else {
    		NGLOG::error( 'Error while activating license key',  array( 'return' => $data ) );
    	}
	    
        $this->raise_error(500, __( 'We are unable to validate the license. Please try again.', 'ngsurvey' ) );
	}
	
	public function deactivate() {
	    $product_id        = sanitize_key( $_POST[ 'product_id' ] );
	    if( empty( $product_id ) ) {
	        $this->raise_error(403, __( 'Missing required fields, please try again.', 'ngsurvey' ) );
	    }
	    
	    $licenses = get_option( NGSURVEY_LICENSES, array() );
	    $license = array_search( $product_id, array_column( $licenses, 'product_id' ) );
	    
	    // If the license is not found, add it else update it
	    if( $license !== false ) {
	        
	        $args = array(
	            'request'       => 'deactivation',
	            'email'         => $licenses[ $license ][ 'email' ],
	            'license_key'   => $licenses[ $license ][ 'license_key' ],
	            'instance'      => $licenses[ $license ][ 'instance' ],
	            'product_id'  	=> $product_id
	        );
	        
	        $base_url  = add_query_arg( 'wc-api', 'software-api', NGSURVEY_ACTIVATION_URI );
	        $base_url  = $base_url . '&' . http_build_query( $args );
	        $data      = wp_remote_get( $base_url );
	        
	        if( !empty( $data[ 'body' ] ) ) {
	            $response = json_decode( $data[ 'body' ] );
	            NGLOG::info( "Successfully sent the deactivation request", array( 'args' => $args, 'response' => $response ) );
	            
	            // If the license is successfully activated, we need to add it to local database
	            if( isset( $response->reset ) && $response->reset  ) {
	                
	                array_splice( $licenses, $license, 1 );
	                
	                // Update the option
	                update_option( NGSURVEY_LICENSES, $licenses );
	                
	                // Now send the response back to the user with updated html
	                ob_start();
	                $this->display();
	                $output = ob_get_clean();
	                
	                wp_send_json_success( $output );
	            }
	        }
	    }
	    
	    $this->raise_error(500, __( 'An error occurred while deactivating license. Please try again.', 'ngsurvey' ) );
	}
}
