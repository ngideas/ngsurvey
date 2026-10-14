<?php
/**
 * Public asset lifecycle regression checks using a disposable WordPress site.
 *
 * Activate NgSurvey and create a published survey with a page, then run:
 * wp eval-file /path/to/ngsurvey/tests/public-assets.php SURVEY_ID --path=/path/to/wordpress
 *
 * Uses the real shortcode, WordPress dependency registries and output hooks.
 * Does not create or change posts, settings, or survey responses.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NgSurvey_Public' ) ) {
	exit( "Run this file with wp eval-file on a site with NgSurvey activated.\n" );
}

$survey_id = isset( $args[0] ) ? absint( $args[0] ) : 0;
if ( ! $survey_id || get_post_type( $survey_id ) !== NGSURVEY_CPT_NAME || get_post_status( $survey_id ) !== 'publish' ) {
	throw new RuntimeException( 'Pass the ID of an existing published survey with a page.' );
}

// WordPress head output expects the web server variable that WP-CLI omits.
if ( ! isset( $_SERVER['SERVER_NAME'] ) ) {
	$_SERVER['SERVER_NAME'] = wp_parse_url( home_url(), PHP_URL_HOST );
}

$checks = array();
$check = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	$checks[] = $message;
};
$capture = static function ( $callback ) {
	ob_start();
	$callback();
	return ob_get_clean();
};
$reset_assets = static function () {
	$GLOBALS['wp_scripts'] = null;
	$GLOBALS['wp_styles'] = null;
	$GLOBALS['post'] = new WP_Post( (object) array( 'ID' => 0, 'post_type' => 'page', 'post_content' => '' ) );
};
$shortcode = '[ngsurvey id="' . $survey_id . '"]';

// A template or block can render a shortcode before wp_head, without it appearing
// in the queried post's raw content. Its assets must wait for the normal hooks.
$reset_assets();
do_action( 'wp_enqueue_scripts' );
$check( ! wp_style_is( 'ngsurvey', 'enqueued' ), 'Unrelated pages do not enqueue survey CSS.' );
$check( ! wp_script_is( 'ngsurvey-public', 'enqueued' ), 'Unrelated pages do not enqueue survey JavaScript.' );
$early = do_shortcode( $shortcode );
$check( str_contains( $early, 'id="survey-form"' ), 'The early shortcode renders its survey form.' );
$check( ! str_contains( $early, 'ngsurvey-public.css' ), 'Before wp_head the shortcode does not print styles into its markup.' );
$head = $capture( static function () { wp_head(); } );
$footer = $capture( static function () { wp_footer(); } );
$check( substr_count( $head, 'ngsurvey-public.css' ) === 1, 'Early-rendered survey CSS prints once in the head.' );
$check( ! str_contains( $head, 'ngsurvey-public.js' ), 'Survey JavaScript is deferred to the footer.' );
$check( substr_count( $footer, 'ngsurvey-public.js' ) === 1, 'Early-rendered survey JavaScript prints once in the footer.' );

// Classic templates and widgets can discover a shortcode after wp_head.
$reset_assets();
$head = $capture( static function () { wp_head(); } );
$check( ! str_contains( $head, 'ngsurvey-public.css' ), 'A page without a direct shortcode does not preload survey CSS.' );
ob_start();
$late = do_shortcode( $shortcode );
$leaked_output = ob_get_clean();
$check( $leaked_output === '', 'Late assets are returned with the shortcode instead of leaking output.' );
$check( substr_count( $late, 'ngsurvey-public.css' ) === 1, 'Late-rendered shortcode returns its CSS.' );
$check( substr_count( $late, 'dashicons-css' ) === 1, 'Late-rendered CSS includes its Dashicons dependency.' );
$again = do_shortcode( $shortcode );
$check( ! str_contains( $again, 'ngsurvey-public.css' ), 'Repeated rendering does not duplicate CSS tags.' );
$footer = $capture( static function () { wp_footer(); } );
$check( substr_count( $footer, 'ngsurvey-public.js' ) === 1, 'Late-rendered survey JavaScript prints once in the footer.' );
$check( substr_count( $footer, 'var ng_ajax =' ) === 1, 'Repeated rendering does not duplicate localized AJAX configuration.' );
$check( str_contains( $footer, 'jquery.min.js' ), 'Late JavaScript includes its jQuery dependency.' );

// Site owners can still suppress the bundled assets with the public filters.
$reset_assets();
add_filter( 'ngsurvey_render_styles', '__return_false' );
add_filter( 'ngsurvey_render_scripts', '__return_false' );
$disabled = do_shortcode( $shortcode );
$check( ! wp_style_is( 'ngsurvey', 'enqueued' ), 'The style opt-out filter is respected during rendering.' );
$check( ! wp_script_is( 'ngsurvey-public', 'enqueued' ), 'The script opt-out filter is respected during rendering.' );
$check( ! str_contains( $disabled, 'ngsurvey-public.css' ), 'The style opt-out filter suppresses late CSS output.' );
remove_filter( 'ngsurvey_render_styles', '__return_false' );
remove_filter( 'ngsurvey_render_scripts', '__return_false' );

echo wp_json_encode( array(
	'wordpress' => get_bloginfo( 'version' ),
	'php' => PHP_VERSION,
	'passed' => count( $checks ),
	'checks' => $checks,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
