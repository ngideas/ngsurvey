<?php
/**
 * Defines the plugin constants.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/init
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * NGSURVEY_PATH constant.
 * It is used to specify plugin path
 */
if ( ! defined( 'NGSURVEY_PATH' ) ) {
    define( 'NGSURVEY_PATH', trailingslashit( plugin_dir_path( dirname( dirname( __FILE__ ) ) ) ) );
}

/**
 * NGSURVEY_URL constant.
 * It is used to specify plugin urls
 */
if ( ! defined( 'NGSURVEY_URL' ) ) {
    define( 'NGSURVEY_URL', trailingslashit( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) ) );
}

/**
 * NGSURVEY_IMG constant.
 * It is used to specify image urls inside assets directory. It's used in front end and
 * using to load related image files for front end user.
 */
if ( ! defined( 'NGSURVEY_IMG' ) ) {
    define( 'NGSURVEY_IMG', trailingslashit( NGSURVEY_URL ) . 'public/media/images/' );
}

/**
 * NGSURVEY_ASSETS constant.
 * It is used to specify assets urls inside media directory. It's used in front end and
 * using to  load related asset files for front end user.
 */
if ( ! defined( 'NGSURVEY_ASSETS' ) ) {
    define( 'NGSURVEY_ASSETS', trailingslashit( NGSURVEY_URL ) . 'public/media/assets/' );
}

/**
 * NGSURVEY_ADMIN_CSS constant.
 * It is used to specify css urls inside assets/admin directory. It's used in WordPress
 *  admin panel and using to  load related CSS files for admin user.
 */
if ( ! defined( 'NGSURVEY_ADMIN_CSS' ) ) {
    define( 'NGSURVEY_ADMIN_CSS', trailingslashit( NGSURVEY_URL ) . 'admin/media/css/' );
}

/**
 * NGSURVEY_ADMIN_JS constant.
 * It is used to specify JS urls inside assets/admin directory. It's used in WordPress
 *  admin panel and using to  load related JS files for admin user.
 */
if ( ! defined( 'NGSURVEY_ADMIN_JS' ) ) {
    define( 'NGSURVEY_ADMIN_JS', trailingslashit( NGSURVEY_URL ) . 'admin/media/js/' );
}

/**
 * NGSURVEY_ADMIN_IMG constant.
 * It is used to specify image urls inside assets/admin directory. It's used in WordPress
 *  admin panel and using to  load related JS files for admin user.
 */
if ( ! defined( 'NGSURVEY_ADMIN_IMG' ) ) {
    define( 'NGSURVEY_ADMIN_IMG', trailingslashit( NGSURVEY_URL ) . 'admin/media/images/' );
}

/**
 * NGSURVEY_INC constant.
 * It is used to specify include path inside includes directory.
 */
if ( ! defined( 'NGSURVEY_INC' ) ) {
    define( 'NGSURVEY_INC', trailingslashit( NGSURVEY_PATH . 'includes' ) );
}

/**
 * NGSURVEY_LANG constant.
 * It is used to specify language path inside languages directory.
 */
if ( ! defined( 'NGSURVEY_LANG' ) ) {
    define( 'NGSURVEY_LANG', trailingslashit( NGSURVEY_PATH . 'languages' ) );
}

/**
 * NGSURVEY_TPL constant.
 * It is used to specify template urls inside templates directory.
 */
if ( ! defined( 'NGSURVEY_TPL' ) ) {
    define( 'NGSURVEY_TPL', trailingslashit( NGSURVEY_PATH . 'templates' ) );
}

/**
 * NGSURVEY_TPL_ADMIN constant.
 * It is used to specify template urls inside templates/admin directory. If you want to
 * create a template for admin panel or administration purpose, you will use from it.
 */
if ( ! defined( 'NGSURVEY_TPL_ADMIN' ) ) {
    define( 'NGSURVEY_TPL_ADMIN', trailingslashit( NGSURVEY_TPL . 'admin' ) );
}

/**
 * NGSURVEY_TPL_FRONT constant.
 * It is used to specify template urls inside templates/front directory. If you want to
 * create a template for front end or end user purposes, you will use from it.
 */
if ( ! defined( 'NGSURVEY_TPL_FRONT' ) ) {
    define( 'NGSURVEY_TPL_FRONT', trailingslashit( NGSURVEY_TPL . 'public' ) );
}

/**
 * NGSURVEY_LOGS constant.
 * It is used to specify logs directory.
 */
if ( ! defined( 'NGSURVEY_LOGS' ) ) {
    define( 'NGSURVEY_LOGS', trailingslashit( NGSURVEY_PATH . 'logs' ) );
}

/**
 * NGSURVEY_MAIN_NAME constant.
 * It defines name of plugin for management tasks in your plugin
 */
if ( ! defined( 'NGSURVEY_MAIN_NAME') ) {
    define( 'NGSURVEY_MAIN_NAME', 'ngsurvey' );
}

/**
 * NGSURVEY_CPT_NAME constant.
 * It defines name of the custom post type of surveys
 */
if ( ! defined( 'NGSURVEY_CPT_NAME') ) {
    define( 'NGSURVEY_CPT_NAME', 'ngsurvey' );
}

/**
 * NGSURVEY_OPTIONS constant.
 * It defines name using which all plugin options are saved
 */
if ( ! defined( 'NGSURVEY_OPTIONS') ) {
    define( 'NGSURVEY_OPTIONS', 'ngsurvey_general_settings' );
}

/**
 * NGSURVEY_LICENSES constant.
 * It defines name using which all plugin licenses are saved
 */
if ( ! defined( 'NGSURVEY_LICENSES') ) {
    define( 'NGSURVEY_LICENSES', 'ngsurvey_licenses' );
}

/**
 * NGSURVEY_DB_VERSION constant
 *
 * It defines database version
 * You can use from this constant to apply your changes in updates or
 * activate plugin again
 */
if ( ! defined( 'NGSURVEY_DB_VERSION') ) {
    define( 'NGSURVEY_DB_VERSION', 1 );
}

/**
 * NGSURVEY_ACTIVATION_URI constant
 *
 * It defines ngideas plugin extensions license activation/deactivation URI
 */
if( !defined( 'NGSURVEY_ACTIVATION_URI' ) ) {
	define( 'NGSURVEY_ACTIVATION_URI', 'https://ngideas.com/' );
}

/**
 * NGSURVEY_UPDATES_URI constant
 *
 * It defines ngideas plugin updates URI
 */
if( !defined( 'NGSURVEY_UPDATES_URI' ) ) {
	define( 'NGSURVEY_UPDATES_URI', 'https://ngideas.com/wp-json/ng/v1/products/' );
}
