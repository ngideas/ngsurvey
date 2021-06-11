<?php
/**
 * The file that defines the settings controller class
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
 * The settings controller class.
 *
 * This is used to define settings controller class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
class NgSurvey_Controller_Settings extends NgSurvey_Controller {
    
    /**
     * Define the settings controller of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }
    
    /**
     * Renders the settings page
     *
     * @return void nothing
     */
    public function display () {
        $settings = array();
        $settings[] = include plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/init/class-ngsurvey-settings.php';
        
        /**
         * Apply filters to get the settings for display
         */
        $settings = apply_filters( 'ngsurvey_settings_classes', $settings );
        
        $this->template->set_template_data( $settings )->get_template_part( 'admin/settings' );
    }
    
    /**
     * Saves the settings to options table.
     */
    public function save() {
        
        $settings = array();
        $settings[] = include plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/init/class-ngsurvey-settings.php';
        
        /**
         * Apply filters to get the settings for saving
         */
        $settings = apply_filters( 'ngsurvey_settings_classes', $settings );
        
        foreach ( $settings as $setting ) {
            $setting->save_settings();
        }
        
        wp_send_json_success();
    }
}
