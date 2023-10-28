<?php
/**
 * The custom post type metabox to store custom metabox settings.
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
	 * The title of the metabox.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The title of this metabox.
	 */
	private $title;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The custom post type name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $cpt_name The name of this custom post type.
	 */
	private $cpt_name;

	/**
	 * The options.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $options The options list
	 */
	private $options;

	/**
	 * The option defaults.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      string $options The options list
	 */
	private $defaults;

	/**
	 * The template loader object.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $template The template loader object.
	 */
	public $template;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $title, $cpt_name, $plugin_name, $options, $defaults ) {
		$this->title       = $title;
		$this->cpt_name    = $cpt_name;
		$this->plugin_name = $plugin_name;
		$this->options     = $options;
		$this->defaults    = $defaults;
		$this->template    = new NgSurvey_Template_Loader();
	}

	/**
	 * Adds survey options metabox to edit screen
	 *
	 * @since 1.0.0
	 */
	function register_options() {

		global $post; // Get the current post data
		if ( $post->post_type == $this->cpt_name && current_user_can( 'edit_survey', $post->ID ) ) {
			add_meta_box(
				$this->cpt_name . '_metabox', // Metabox ID
				__( $this->title, 'ngsurvey' ),
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
	 *
	 * @since 1.0.0
	 */
	function ngs_metabox_defaults() {
		/**
		 * Apply filters to allow the third party plugins add their own options.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( $this->cpt_name . '_metabox_defaults', $this->defaults );
	}

	/**
	 * Render the metabox markup
	 * This is the function called in `register_survey_meta_box()`
	 *
	 * @since 1.0.0
	 */
	public function render_metabox( $post = 0 ) {
		$saved    = get_post_meta( is_a( $post, 'WP_Post' ) ? $post->ID : $post, $this->cpt_name . '_settings', true ); // Get the saved values
		$defaults = $this->ngs_metabox_defaults(); // Get the default values

		$params = wp_parse_args( $saved, $defaults ); // Merge the two in case any fields don't exist in the saved data

		/**
		 * Apply filters to allow plugins add their own options
		 *
		 * @since 1.0.0
		 */
		$options = apply_filters( $this->cpt_name . '_metabox_options', $this->options );

		$this->template->set_template_data( array(
			'options'  => $options,
			'params'   => $params,
			'cpt_name' => $this->cpt_name
		) )->get_template_part( 'admin/metabox_options' );

		// Security field: This validates that submission came from the actual dashboard and not the front end or a remote server.
		wp_nonce_field( $this->cpt_name . '_form_metabox_nonce', $this->cpt_name . '_form_metabox_process' );
	}

	/**
	 * Handles saving the survey options.
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 *
	 * @return numeric | void
	 *
	 * @since 1.0.0
	 */
	public function save_options( $post_id ) {

		// Verify that our security field exists. If not, bail.
		if ( ! isset( $_POST[ $this->cpt_name . '_form_metabox_process' ] ) ) {
			return;
		}

		// Verify data came from edit/dashboard screen
		if ( ! wp_verify_nonce( $_POST[ $this->cpt_name . '_form_metabox_process' ], $this->cpt_name . '_form_metabox_nonce' ) ) {
			return $post_id;
		}

		// Verify user has permission to edit post
		if ( ! current_user_can( 'edit_others_posts' ) && ! current_user_can( 'edit_surveys' ) ) {
			return $post_id;
		}

		// Check that our custom fields are being passed along
		// This is the `name` value array. We can grab all of the fields and their values at once.
		if ( ! isset( $_POST[ $this->cpt_name . '_settings' ] ) ) {
			throw new Exception( 'Error' );
		}

		/**
		 * Sanitize all data
		 * This keeps malicious code out of our database.
		 */

		// Set up an empty array
		$sanitized = array();

		// Loop through each of our fields
		foreach ( $_POST[ $this->cpt_name . '_settings' ] as $key => $detail ) {
			// Sanitize the data and push it to our new array
			// `wp_filter_post_kses` strips our dangerous server values
			// and allows through anything you can include a post.
			$sanitized[ sanitize_key( $key ) ] = is_array( $detail ) ? array_map( 'wp_filter_post_kses', $detail ) : wp_filter_post_kses( $detail );
		}

		// Save our submissions to the database
		update_post_meta( $post_id, $this->cpt_name . '_settings', $sanitized );
	}

	/**
	 * Save events data to revisions
	 *
	 * @param Number $post_id The post ID
	 *
	 * @since 1.0.0
	 */
	public function save_revisions( $post_id ) {

		// Check if it's a revision
		$parent_id = wp_is_post_revision( $post_id );

		// If is revision
		if ( $parent_id ) {

			// Get the saved data
			$parent  = get_post( $parent_id );
			$details = get_post_meta( $parent->ID, $this->cpt_name . '_settings', true );

			// If data exists and is an array, add to revision
			if ( ! empty( $details ) && is_array( $details ) ) {
				// Get the defaults
				$defaults = $this->ngs_metabox_defaults();

				// For each default item
				foreach ( $defaults as $key => $value ) {
					// If there's a saved value for the field, save it to the version history
					if ( array_key_exists( $key, $details ) ) {
						add_metadata( 'post', $post_id, $this->cpt_name . '_settings_' . $key, $details[ $key ] );
					}
				}
			}

		}
	}

	/**
	 * Restore events data with post revisions
	 *
	 * @param Number $post_id The post ID
	 * @param Number $revision_id The revision ID
	 *
	 * @since 1.0.0
	 */
	public function restore_revisions( $post_id, $revision_id = 0 ) {
		// Fix for unknown cases where the revision is not sent
		if ( ! $revision_id ) {
			return;
		}

		// Variables
		$revision = get_post( $revision_id ); // The revision
		$defaults = $this->ngs_metabox_defaults(); // The default values
		$details  = array(); // An empty array for our new metadata values

		// Update content for each field
		foreach ( $defaults as $key => $value ) {

			// Get the revision history version
			$detail_revision = get_metadata( 'post', $revision->ID, $this->cpt_name . '_settings_' . $key, true );

			// If a historic version exists, add it to our new data
			if ( isset( $detail_revision ) ) {
				$details[ $key ] = $detail_revision;
			}
		}

		// Replace our saved data with the old version
		update_post_meta( $post_id, $this->cpt_name . '_settings', $details );
	}

	/**
	 * Get the data to display on the revisions page
	 *
	 * @param Array $fields The fields
	 *
	 * @return Array The fields
	 *
	 * @since 1.0.0
	 */
	public function get_revisions_fields( $fields ) {

		// Get our default values
		$defaults = $this->ngs_metabox_defaults();

		// For each field, use the key as the title
		foreach ( $defaults as $key => $value ) {
			$fields[ $this->cpt_name . '_settings_' . $key ] = ucfirst( $key );
		}

		return $fields;
	}

	/**
	 * Display the data on the revisions page
	 *
	 * @param String|Array $value The field value
	 * @param Array $field The field
	 *
	 * @since 1.0.0
	 */
	public function _display_revisions_fields( $value, $field ) {
		global $revision;

		return get_metadata( 'post', $revision->ID, $field, true );
	}
}
