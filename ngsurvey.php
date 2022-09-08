<?php
/**
 * The plugin bootstrap file of NgSurvey plugin.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                https://ngideas.com
 * @since               1.0.0
 * @package             NgSurvey
 *
 * @wordpress-plugin
 * Plugin Name:         NgSurvey
 * Plugin URI:          https://ngideas.com/
 * Description:         Next generation open source surveys for Wordpress is here. Create feature rich surveys on your wordpress site and get feedback from your users.  
 * Version:             1.1.3
 * Author:              NgIdeas
 * Author URI:          https://ngideas.com/
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         ngsurvey
 * Requires at least:   4.9
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'NGSURVEY_VERSION', '1.1.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ngsurvey-activator.php
 */
function activate_ngsurvey() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/init/class-ngsurvey-activator.php';
	NgSurvey_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ngsurvey-deactivator.php
 */
function deactivate_ngsurvey() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/init/class-ngsurvey-deactivator.php';
	NgSurvey_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ngsurvey' );
register_deactivation_hook( __FILE__, 'deactivate_ngsurvey' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ngsurvey.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ngsurvey() {

	$plugin = new NgSurvey();
	$plugin->run();

}
run_ngsurvey();
