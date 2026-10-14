<?php
/**
 * Runs a fresh WordPress startup against a disposable installation with both
 * ngsurvey and ngsurvey-extensions already installed and active.
 *
 * NGSURVEY_WP_ROOT=/path/to/wordpress php tests/bootstrap.test.php
 *
 * Requires the PHP extensions/database configured by the WordPress installation.
 * Temporary catalogs are removed on shutdown; the test does not change settings.
 */
if( PHP_SAPI !== 'cli' ) {
    exit;
}

$wp_root = realpath( getenv( 'NGSURVEY_WP_ROOT' ) ?: '' );
if( !$wp_root || !is_file( $wp_root . '/wp-load.php' ) ) {
    fwrite( STDERR, "Set NGSURVEY_WP_ROOT to a disposable WordPress installation.\n" );
    exit( 1 );
}
$wp_root = str_replace( '\\', '/', $wp_root );

function ngsurvey_bootstrap_check( $condition, $message ) {
    if( !$condition ) {
        throw new RuntimeException( $message );
    }
}

function ngsurvey_bootstrap_callbacks( $hook_name, $class_name, $method ) {
    global $wp_filter;
    $objects = array();
    if( isset( $wp_filter[$hook_name] ) ) {
        foreach( $wp_filter[$hook_name]->callbacks as $callbacks ) {
            foreach( $callbacks as $callback ) {
                $function = $callback['function'];
                if( is_array( $function ) && $function[0] instanceof $class_name && $function[1] === $method ) {
                    $objects[] = $function[0];
                }
            }
        }
    }
    return $objects;
}

// A process-local language directory prevents interference with installed packs.
$fixture_dir = $wp_root . '/wp-content/ngsurvey-bootstrap-' . bin2hex( random_bytes( 8 ) );
$fixture_files = array();
register_shutdown_function( function () use ( &$fixture_files, $fixture_dir ) {
    foreach( $fixture_files as $file ) {
        if( is_file( $file ) ) {
            unlink( $file );
        }
    }
    if( is_dir( $fixture_dir . '/plugins' ) ) {
        rmdir( $fixture_dir . '/plugins' );
    }
    if( is_dir( $fixture_dir ) ) {
        rmdir( $fixture_dir );
    }
} );
ngsurvey_bootstrap_check( mkdir( $fixture_dir . '/plugins', 0777, true ), 'Cannot create temporary catalogs.' );
define( 'WP_LANG_DIR', $fixture_dir );
define( 'WP_USE_THEMES', false );

require_once $wp_root . '/wp-includes/plugin.php';
require_once $wp_root . '/wp-includes/pomo/mo.php';

$locale = 'zz_ZZ';
$translations = array(
    'ngsurvey' => array(
        'Single line Textbox' => 'Translated Textbox café',
        'Survey Settings' => 'Translated Survey Settings',
        'Preset Options' => 'Translated Preset Options',
    ),
    'ngsurvey-extensions' => array( 'Grid Layout' => 'Translated Grid 東京' ),
);
foreach( $translations as $domain => $entries ) {
    $catalog = new MO();
    $catalog->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
    $catalog->set_header( 'Language', $locale );
    foreach( $entries as $original => $translated ) {
        $catalog->add_entry( new Translation_Entry( array( 'singular' => $original, 'translations' => array( $translated ) ) ) );
    }
    $file = $fixture_dir . '/plugins/' . $domain . '-' . $locale . '.mo';
    $fixture_files[] = $file;
    ngsurvey_bootstrap_check( $catalog->export_to_file( $file ), 'Cannot write temporary catalog for ' . $domain );
}

add_filter( 'locale', function () use ( $locale ) { return $locale; } );
add_filter( 'pre_determine_locale', function () use ( $locale ) { return $locale; } );

$translation_notices = array();
$first_translations = array();
$catalog_loads = array();
$before_init = array();
add_action( 'doing_it_wrong_run', function ( $function, $message ) use ( &$translation_notices ) {
    if( $function === '_load_textdomain_just_in_time' && strpos( $message, 'ngsurvey' ) !== false ) {
        $translation_notices[] = $message;
    }
}, 10, 2 );
add_filter( 'gettext', function ( $translation, $text, $domain ) use ( &$first_translations, $translations ) {
    if( isset( $translations[$domain] ) && !isset( $first_translations[$domain] ) ) {
        $first_translations[$domain] = array( 'text' => $text, 'init_started' => did_action( 'init' ) > 0 );
    }
    return $translation;
}, 10, 3 );
add_action( 'load_textdomain', function ( $domain, $file ) use ( &$catalog_loads, $translations ) {
    if( isset( $translations[$domain] ) ) {
        $catalog_loads[$domain] = array( 'file' => $file, 'init_started' => did_action( 'init' ) > 0 );
    }
}, 10, 2 );
add_action( 'plugins_loaded', function () use ( &$before_init ) {
    $before_init = array(
        'question_types_registered' => has_filter( 'ngsurvey_fetch_question_types' ) !== false,
        'metabox_count' => count( ngsurvey_bootstrap_callbacks( 'add_meta_boxes', 'NgSurvey_Options', 'register_options' ) ),
    );
}, PHP_INT_MAX );

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require $wp_root . '/wp-load.php';

ngsurvey_bootstrap_check( defined( 'NGSURVEY_PATH' ) && defined( 'NGSURVEY_EXTENSIONS_DIR' ), 'Both plugins must already be installed and active.' );
ngsurvey_bootstrap_check( $translation_notices === array(), 'Premature translation notices: ' . implode( '; ', $translation_notices ) );
ngsurvey_bootstrap_check( $before_init === array( 'question_types_registered' => false, 'metabox_count' => 0 ), 'Translated objects must not initialize before init.' );

foreach( array_keys( $translations ) as $domain ) {
    ngsurvey_bootstrap_check( !empty( $first_translations[$domain]['init_started'] ), 'First translation was too early for ' . $domain );
    ngsurvey_bootstrap_check( !empty( $catalog_loads[$domain]['init_started'] ), 'Catalog did not load during/after init for ' . $domain );
    ngsurvey_bootstrap_check( is_textdomain_loaded( $domain ), 'Catalog was not loaded for ' . $domain );
}

$types = array();
foreach( apply_filters( 'ngsurvey_fetch_question_types', array() ) as $type ) {
    $types[$type->name] = $type;
}
$expected_types = array( 'header', 'choice', 'textbox', 'address', 'calendar', 'grid', 'images', 'location', 'matching', 'name', 'nps', 'parameter', 'ranking', 'rating', 'signature', 'sliders', 'textarea' );
foreach( $expected_types as $name ) {
    ngsurvey_bootstrap_check( isset( $types[$name] ), 'Question type missing after init: ' . $name );
}
ngsurvey_bootstrap_check( $types['textbox']->title === $translations['ngsurvey']['Single line Textbox'], 'Core question constructor did not use the translation.' );
ngsurvey_bootstrap_check( $types['grid']->title === $translations['ngsurvey-extensions']['Grid Layout'], 'Extension question constructor did not use the translation.' );

$metaboxes = ngsurvey_bootstrap_callbacks( 'add_meta_boxes', 'NgSurvey_Options', 'register_options' );
ngsurvey_bootstrap_check( count( $metaboxes ) === 2, 'Survey and preset metabox callbacks must both register after init.' );
$metabox_titles = array();
foreach( $metaboxes as $metabox ) {
    $reflection = new ReflectionObject( $metabox );
    $cpt = $reflection->getProperty( 'cpt_name' )->getValue( $metabox );
    $metabox_titles[$cpt] = $reflection->getProperty( 'title' )->getValue( $metabox );
    foreach( array(
        $cpt . '_render_metabox' => 'render_metabox',
        'wp_restore_post_revision' => 'restore_revisions',
        '_wp_post_revision_fields' => 'get_revisions_fields',
        '_wp_post_revision_field_my_meta' => 'display_revisions_fields',
    ) as $hook => $method ) {
        ngsurvey_bootstrap_check( has_filter( $hook, array( $metabox, $method ) ) !== false, 'Missing metabox hook: ' . $hook );
    }
    foreach( array( 'save_options', 'save_revisions' ) as $method ) {
        ngsurvey_bootstrap_check( has_action( 'save_post', array( $metabox, $method ) ) !== false, 'Missing metabox save hook: ' . $method );
    }
}
ngsurvey_bootstrap_check( $metabox_titles[NGSURVEY_CPT_NAME] === $translations['ngsurvey']['Survey Settings'], 'Survey metabox title was not translated.' );
ngsurvey_bootstrap_check( $metabox_titles[NGPRESET_CPT_NAME] === $translations['ngsurvey']['Preset Options'], 'Preset metabox title was not translated.' );
ngsurvey_bootstrap_check( has_filter( 'ngsurvey_metabox_options' ) !== false && has_filter( 'ngsurvey_metabox_defaults' ) !== false,
    'Extension metabox options/defaults must register before use.' );

echo json_encode( array(
    'status' => 'PASS',
    'wordpress' => get_bloginfo( 'version' ),
    'premature_translation_notices' => $translation_notices,
    'first_translations' => $first_translations,
    'core_title' => $types['textbox']->title,
    'extension_title' => $types['grid']->title,
    'question_type_count' => count( $types ),
    'metabox_titles' => $metabox_titles,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
