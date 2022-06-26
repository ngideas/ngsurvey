<?php
/**
 * The file that defines the ajax controller class
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
 * The ajax controller class.
 *
 * This is used to define ajax controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Ajax extends NgSurvey_Controller {

	/**
	 * Define the questions controller of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($config = array()) {
	    parent::__construct($config);
	}

	/**
	 * Executes the ajax task by calling the dependent extensions and sends the data out.
	 * 
	 * @since 1.0.0
	 */
    public function execute() {
        $data      = null;
        $operation = isset( $_POST[ 'operation' ] ) ? sanitize_text_field( $_POST[ 'operation' ] ) : '';
        $survey_id = isset( $_POST[ 'ngform' ][ 'sid' ] ) ? (int) $_POST[ 'ngform' ][ 'sid' ] : 0;
        
        if( empty( $operation ) ) {
            $this->raise_error();
        }
        
        // Check user authorization
        $this->authorise( $survey_id );

        /*
         * Apply the filter to get the data from the implementing extensions and push it to the caller.
         * By this filter, extensions can execute their custom actions and get the data they need.
         * By reaching this method means all security is passed by using NgSurvey nounce. 
         * 
         * The implementing functions make sure to exectute only intended operation. 
         * Send a operation request parameter and validate it before applying operation on the data. 
         * 
         * @since 1.0.0
         */
        $data = apply_filters( 'ngsurvey_ajax', $data, $operation );
        
        wp_send_json_success( $data );
    }

	/**
	 * Renders the block to show post types and their counts.
	 * Also shows 5 posts with category baz and tag foo.
	 *
	 * @param array $attributes The attributes for the block.
	 * @param string $content The block content, if any.
	 * @param WP_Block $block The instance of this block.
	 *
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block ) {
		$current_post_id      = get_the_ID();
		$post_types           = get_post_types( [ 'public' => true ] );
		$wrapper_markup       = '<div %1$s><ul>%2$s</ul><p>%3$s</p>%4$s</div>';
		$post_types_markup    = '';
		$foo_baz_posts_markup = '';

		foreach ( $post_types as $post_type_slug ) {
			$post_type_object  = get_post_type_object( $post_type_slug );
			$post_count        = wp_count_posts( $post_type_slug );
			$post_type_content = sprintf(
			/* translators: %d: post count, %s: post name */
				esc_html__( 'There are %d %s.', 'site-counts' ),
				absint( $post_count ),
				esc_html( $post_type_object->labels->name )
			);
			$post_types_markup = $post_types_markup . '<li>' . $post_type_content . '</li>';
		}

		$query = new WP_Query( array(
			'posts_per_page' => 6,
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'date_query'     => array(
				array(
					'hour'    => 9,
					'compare' => '>=',
				),
				array(
					'hour'    => 17,
					'compare' => '<=',
				),
			),
			'tag'            => 'foo',
			'category_name'  => 'baz',
		) );

		if ( $query->have_posts() ) {
			$foo_baz_posts_content = '';

			while ( $query->have_posts() ) {
				$query->the_post();
				if ( $current_post_id && get_the_ID() == $current_post_id ) {
					continue;
				}

				$foo_baz_posts_content = $foo_baz_posts_content . '<li> ' . esc_html( $query->post->post_title ) . ' </li>';
			}

			$foo_baz_heading      = esc_html__( '5 posts with the tag of foo and the category of baz', 'site-counts' );
			$foo_baz_posts_markup = sprintf(
				'<h2>%1$s</h2><ul>%2$s</ul>',
				$foo_baz_heading,
				$foo_baz_posts_content
			);
		}

		$colors          = block_core_page_list_build_css_colors( $block->context );
		$font_sizes      = block_core_page_list_build_css_font_sizes( $block->context );
		$classes         = array_merge( $colors['css_classes'], $font_sizes['css_classes'] );
		$style_attribute = ( $colors['inline_styles'] . $font_sizes['inline_styles'] );
		$css_classes     = trim( implode( ' ', $classes ) );

		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $css_classes,
				'style' => $style_attribute,
			)
		);

		$current_post_markup = sprintf(
		/* translators: %d: current post ID */
			esc_html__( 'The current post ID is %d', 'site-counts' ),
			absint( $current_post_id )
		);

		$final_markup = sprintf(
			$wrapper_markup,
			$wrapper_attributes,
			$post_types_markup,
			$current_post_markup,
			$foo_baz_posts_markup
		);

		return apply_filters( 'sitecounts_block_content', $final_markup );
	}
}
