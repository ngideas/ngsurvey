<?php
/**
 * The class to handle NgSurvey extensions autoupdates. 
 * Based on from woosoftwarelicense docs.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/admin/init
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * The extensions autoupdates functionality of the plugin.
 *
 * Defines the class to handle autoupdate handling by extending the wordpress plugin updates calls.
 *
 * @package NgSurvey
 * @subpackage NgSurvey/admin
 * @author NgIdeas <support@ngideas.com>
 */
class NgSurvey_AutoUpdate {

	public $api_url;

	public $plugin;

	private $slug;
	
	private $version;
	
	private $licenses;
	
	private $email;

	private $API_VERSION;

	function __construct( $slug, $plugin, $version, $email, $licenses ) {
		$this->api_url 	= NGSURVEY_UPDATES_URI;
		$this->slug 	= $slug;
		$this->plugin 	= $plugin;
		$this->version 	= $version;
		$this->licenses	= $licenses;
		$this->email 	= $email;

		// use laets available API
		$this->API_VERSION = 1;
	}

	public function check_for_updates( $transient ) {
		if ( ! is_object( $transient ) || ! isset( $transient->response ) ) {
			return $transient;
		}

		$post_body = $this->prepare_request( 'check' );
		if ( $post_body === FALSE ) {
			return $transient;
		}

		global $wp_version;

		// check if cached
		$data = get_site_transient( 'ngsurvey_check_for_plugins_update' );
		if ( $data === FALSE ) {
			$return = wp_remote_post( $this->api_url, array(
				'body' 			=> $post_body,
				'timeout' 		=> 30, 
				'user-agent' 	=> 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) 
			) );
			
			if ( is_wp_error( $return ) || $return['response']['code'] != 200 ) {
				return $transient;
			}

			$data = $return['body'];
			set_site_transient( 'ngsurvey_check_for_plugins_update', $data, 60 * 60 * 4 );
		}
		
		$response_data = json_decode( $data );
		$noupdate = true;

		if( !empty( $response_data ) ) {
			foreach ( $response_data as $response ) {
				if ( 
					is_object( $response ) && 
					! empty( $response ) && 
					$response->slug == $this->slug && 
					version_compare( $this->version, $response->new_version ) < 0 && 
					version_compare( $response->new_version, get_bloginfo('version' ) ) < 0
					) {
					$response = $this->postprocess_response( $response );
					$transient->response[ $this->plugin ] = $response;
					$noupdate = false;
				}
			}
		}
		
		if( $noupdate ) {
			// No update is available.
			$item = (object) array(
				'id'            => $this->plugin,
				'slug'          => $this->slug,
				'plugin'        => $this->plugin,
				'new_version'   => $this->version,
				'url'           => '',
				'package'       => '',
				'icons'         => array(),
				'banners'       => array(),
				'banners_rtl'   => array(),
				'tested'        => '',
				'requires_php'  => '',
				'compatibility' => new stdClass(),
			);
			
			// Adding the "mock" item to the `no_update` property is required
			// for the enable/disable auto-updates links to correctly appear in UI.
			$transient->no_update[ $this->plugin ] = $item;
		}

		return $transient;
	}

	public function plugins_api_call( $def, $action, $args ) {
		if ( 'plugin_information' !== $action || ! is_object( $args ) || ! isset( $args->slug ) || $args->slug != $this->slug ) {
			return $def;
		}

		$request_string = $this->prepare_request( $action, $args );
		if ( $request_string === FALSE ) {
			return new WP_Error(
				'plugins_api_failed',
				__( 'An error occurred while checking plugin update.', 'ngsurvey' ) .
				'&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' .
				__( 'Try again', 'ngsurvey' ) . '&lt;/a>' );
		}

		global $wp_version;

		$request_uri = $this->api_url . '?' . http_build_query( $request_string, '', '&' );
		$data = wp_remote_get( $request_uri, array( 'timeout' => 30, 'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) ) );

		if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
			return new WP_Error( 
				'plugins_api_failed',
				__( 'An Unexpected HTTP Error occurred during the API request.', 'ngsurvey' ) .
				'&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' .
				__( 'Try again', 'ngsurvey' ) . '&lt;/a>',
				$data->get_error_message() );
		}

		$response_data = json_decode( $data['body'] );
		if( !empty( $response_data ) ) {
			foreach ( $response_data as $response ) {
				if ( is_object( $response ) && ! empty( $response ) && $response->slug == $this->slug && version_compare( $this->version, $response->new_version ) < 0) {
					return $this->postprocess_response( $response );
				}
			}
		}
		
		return $def;
	}

	public function prepare_request( $action, $args = array() ) {
		global $wp_version;
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

		return array( 
			'action' 		=> $action,
			'version' 		=> $this->version,
			'slug' 			=> $this->slug,
			'email' 		=> $this->email,
			'licenses' 		=> $this->licenses,
			'instance' 		=> str_replace( $protocol, "", get_bloginfo( 'wpurl' ) ),
			'wp-version' 	=> $wp_version,
			'api_version' 	=> $this->API_VERSION );
	}

	private function postprocess_response( $response ) {
		// include slug and plugin data
		$response->id = $this->plugin;
		$response->slug = $this->slug;
		$response->plugin = $this->plugin;
		
		// if sections are being set
		if ( isset( $response->sections ) ) {
			$response->sections = (array) $response->sections;
		}

		// if banners are being set
		if ( isset( $response->banners ) ) {
			$response->banners = (array) $response->banners;
		}

		// if icons being set, convert to array
		if ( isset( $response->icons ) ) {
			$response->icons = (array) $response->icons;
		}

		return $response;
	}
}
