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
 * The class responsible for orchestrating the actions and filters of the core plugin.
 */
require_once plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/init/class-ngsurvey-constants.php';

/**
 * The class responsible for including all third-party libraries used by the plugin.
 */
require_once NGSURVEY_PATH . 'includes/lib/vendor/autoload.php';

/**
 * The class responsible for defining logger functions used across the core plugin.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-logging.php';

/**
 * The class responsible for defining all utility functions used across the core plugin.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-utils.php';

/**
 * The class responsible for defining template loading functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-template-loader.php';

/**
 * The class responsible for defining registry object used for loading params the surveys and questions.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-registry.php';

/**
 * The class responsible for defining extension base controller functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/abstracts/class-ngsurvey-base.php';

/**
 * The class responsible for defining survey base controller functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/abstracts/class-ngsurvey-controller.php';

/**
 * The class responsible for defining survey questions base type functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/abstracts/class-ngsurvey-question.php';

/**
 * The class responsible for defining survey reports base type functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/abstracts/class-ngsurvey-extension.php';

/**
 * The class responsible for defining survey model base type functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/abstracts/class-ngsurvey-model.php';

/**
 * The class responsible for defining settings api functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/abstracts/class-ngsurvey-settings-api.php';

/**
 * The class responsible for defining the auto-updates for the ngsurvey extensions and addons.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-autoupdate.php';

/**
 * The class responsible for orchestrating the actions and filters of the core plugin.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-loader.php';

/**
 * The class responsible for defining internationalization functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-i18n.php';

/**
 * The class responsible for defining survey custom post type functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-cpt.php';

/**
 * The class responsible for defining survey settings metabox functionality of the plugin.
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-options.php';

/**
 * The class responsible for defining parser to parse conditional rules and return the queries compatible to validate results
 */
require_once NGSURVEY_PATH . 'includes/init/class-ngsurvey-rulesparser.php';

/**
 * The class responsible for defining all actions that occur in the admin area.
 */
require_once NGSURVEY_PATH . 'admin/class-ngsurvey-admin.php';

/**
 * The class responsible for defining all actions that occur in the public-facing side of the site.
 */
require_once NGSURVEY_PATH . 'public/class-ngsurvey-public.php';
