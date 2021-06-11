<?php
/**
 * The custom post type metabox to store survey settings. 
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/admin/init
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The survey metabox functionality of the plugin.
 *
 * Defines the metabox and list of settings available for the survey and
 * adds the functions to save and retrieve metabox values.
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/admin
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey_Options {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	
	/**
	 * The custom post type name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cpt_name    The name of this custom post type.
	 */
	private $cpt_name;
	
	/**
	 * The survey options.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $options    The survey options list
	 */
	private $options;
	
	/**
	 * The template loader object.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $template    The template loader object.
	 */
	public $template;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $cpt_name, $plugin_name ) {

	    $this->cpt_name = $cpt_name;
	    $this->plugin_name = $plugin_name;

		$this->options = array(
		    (object) [
		        'name' => 'tracking_method',
		        'title' => __( 'Tracking Method', 'ngsurvey' ),
		        'type' => 'checkbox',
		    	'options'  => [ 'cookie' => __( 'Cookie', 'ngsurvey' ), 'ip' => __( 'IP Address', 'ngsurvey' ), 'username' => __( 'Username', 'ngsurvey' ) ],
		    ],
		    (object) [
		        'name' => 'end_of_survey_title',
		        'title' => __( 'End Message Title', 'ngsurvey' ),
		        'type' => 'textbox',
		        'placeholder' => __( 'Enter end of survey message title', 'ngsurvey' )
		    ],
		    (object) [
		        'name' => 'end_of_survey_message',
		        'title' => __( 'End Message Description', 'ngsurvey' ),
		        'type' => 'textarea',
		        'placeholder' => __( 'Enter end of survey message', 'ngsurvey' )
		    ],
		);
		
	    $this->template = new NgSurvey_Template_Loader();
	}

	/**
	 * Adds survey options metabox to edit screen
	 */
	function register_survey_options() {
	    
	    global $post; // Get the current post data
	    if ( $post->post_type == $this->cpt_name && current_user_can( 'edit_survey', $post->ID ) ) {
    	    add_meta_box(
    	        'ngsurvey_metabox', // Metabox ID
    	        __( 'Survey Settings', 'ngsurvey' ),
    	        array( $this, 'render_metabox' ),
    	        $this->cpt_name,
    	        'normal',
    	        'default'
            );
	    }
	}

	/**
	 * Create the metabox default values
	 * This allows us to save multiple values in an array, reducing the size of our database.
	 * Setting defaults helps avoid "array key doesn't exit" issues.
	 */
	function ngs_metabox_defaults() {
	    $defaults = array(
	        'tracking_method' => array(),
	        'end_of_survey_title' => '',
	        'end_of_survey_message' => '',
	    );
	    
	    /**
	     * Apply filters to allow the third party plugins add their own options.
	     * 
	     * @since 1.0.0
	     */
	    return apply_filters( 'ngsurvey_metabox_defaults', $defaults);
	}

	/**
	 * Render the metabox markup
	 * This is the function called in `register_survey_meta_box()`
	 */
	public function render_metabox( $post = 0 ) {
	    $saved = get_post_meta( is_a( $post, 'WP_Post' ) ? $post->ID : $post, 'ngsurvey_settings', true ); // Get the saved values
	    $defaults = $this->ngs_metabox_defaults(); // Get the default values

	    $params = wp_parse_args( $saved, $defaults ); // Merge the two in case any fields don't exist in the saved data
	    
	    /**
	     * Apply filters to allow plugins add their own options
	     * 
	     * @since 1.0.0
	     */
	    $options = apply_filters( 'ngsurvey_metabox_options', $this->options );
	    
	    $this->template->set_template_data( array( 'options' => $options, 'params' => $params ) )->get_template_part( 'admin/survey_options' );

		// Security field: This validates that submission came from the actual dashboard and not the front end or a remote server.
		wp_nonce_field( 'ngsurvey_form_metabox_nonce', 'ngsurvey_form_metabox_process' );
	}
	
	/**
	 * Handles saving the survey options.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return null
	 */
	public function save_survey_options( $post_id ) {
	    
	    // Verify that our security field exists. If not, bail.
	    if ( !isset( $_POST['ngsurvey_form_metabox_process'] ) ) return;
	    
	    // Verify data came from edit/dashboard screen
	    if ( !wp_verify_nonce( $_POST['ngsurvey_form_metabox_process'], 'ngsurvey_form_metabox_nonce' ) ) {
	        return $post_id;
	    }
	    
	    // Verify user has permission to edit post
	    if( !current_user_can( 'edit_others_posts' ) && !current_user_can( 'edit_surveys' ) ) {
	        return $post_id;
	    }
	    
	    // Check that our custom fields are being passed along
	    // This is the `name` value array. We can grab all
	    // of the fields and their values at once.
	    if ( !isset( $_POST['ngsurvey_settings'] ) ) {
	        throw new Exception('Error');
	        return $post_id;
	    }

	    /**
	     * Sanitize all data
	     * This keeps malicious code out of our database.
	     */
	    
	    // Set up an empty array
	    $sanitized = array();
	    
	    // Loop through each of our fields
	    foreach ( $_POST['ngsurvey_settings'] as $key => $detail ) {
	        // Sanitize the data and push it to our new array
	        // `wp_filter_post_kses` strips our dangerous server values
	        // and allows through anything you can include a post.
	        $sanitized[ sanitize_key( $key ) ] = is_array( $detail ) ? array_map( 'wp_filter_post_kses', $detail ) : wp_filter_post_kses( $detail );
	    }
	    
	    // Save our submissions to the database
	    update_post_meta( $post_id, 'ngsurvey_settings', $sanitized );
	}
	
	/**
	 * Save events data to revisions
	 * @param  Number $post_id The post ID
	 */
	public function save_revisions( $post_id ) {
	    
	    // Check if it's a revision
	    $parent_id = wp_is_post_revision( $post_id );
	    
	    // If is revision
	    if ( $parent_id ) {
	        
	        // Get the saved data
	        $parent = get_post( $parent_id );
	        $details = get_post_meta( $parent->ID, 'ngsurvey_settings', true );
	        
	        // If data exists and is an array, add to revision
	        if ( !empty( $details ) && is_array( $details ) ) {
	            // Get the defaults
	            $defaults = $this->ngs_metabox_defaults();
	            
	            // For each default item
	            foreach ( $defaults as $key => $value ) {
	                // If there's a saved value for the field, save it to the version history
	                if ( array_key_exists( $key, $details ) ) {
	                    add_metadata( 'post', $post_id, 'ngsurvey_settings_' . $key, $details[$key] );
	                }
	            }
	        }
	        
	    }
	}
	
	/**
	 * Restore events data with post revisions
	 * @param  Number $post_id     The post ID
	 * @param  Number $revision_id The revision ID
	 */
	public function restore_revisions( $post_id, $revision_id ) {
	    
	    // Variables
	    $revision = get_post( $revision_id ); // The revision
	    $defaults = $this->ngs_metabox_defaults(); // The default values
	    $details = array(); // An empty array for our new metadata values
	    
	    // Update content for each field
	    foreach ( $defaults as $key => $value ) {
	        
	        // Get the revision history version
	        $detail_revision = get_metadata( 'post', $revision->ID, 'ngsurvey_settings_' . $key, true );
	        
	        // If a historic version exists, add it to our new data
	        if ( isset( $detail_revision ) ) {
	            $details[$key] = $detail_revision;
	        }
	    }
	    
	    // Replace our saved data with the old version
	    update_post_meta( $post_id, 'ngsurvey_settings', $details );
	}

	/**
	 * Get the data to display on the revisions page
	 * @param  Array $fields The fields
	 * @return Array The fields
	 */
	public function get_revisions_fields( $fields ) {
	    
	    // Get our default values
	    $defaults = $this->ngs_metabox_defaults();
	    
	    // For each field, use the key as the title
	    foreach ( $defaults as $key => $value ) {
	        $fields['ngsurvey_settings_' . $key] = ucfirst( $key );
	    }
	    
	    return $fields;
	}
	
	/**
	 * Display the data on the revisions page
	 * @param  String|Array $value The field value
	 * @param  Array        $field The field
	 */
	public function _display_revisions_fields( $value, $field ) {
	    global $revision;
	    return get_metadata( 'post', $revision->ID, $field, true );
	}
}
