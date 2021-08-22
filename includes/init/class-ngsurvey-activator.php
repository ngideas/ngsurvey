<?php

/**
 * Fired during plugin activation
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
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    NgSurvey
 * @subpackage NgSurvey/includes
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey_Activator {
    /**
     * Plugin version option name.
     */
    const NGSURVEY_VERSION = 'ngsurvey_version';
    
    /**
     * DB updates and callbacks that need to be run per version.
     *
     * @var array
     */
    protected static $db_updates = array();
    
    /**
     * Get database schema.
     *
     * @return string
     */
    protected static function get_schema() {
        global $wpdb;
        
        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }
        
        // Max DB index length. See wp_get_db_schema().
        $max_index_length = 191;
        
        $tables = "
        CREATE TABLE {$wpdb->prefix}ngs_answers (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `answer_type` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `question_id` mediumint(8) unsigned DEFAULT NULL,
          `title` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `image` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_ngs_options_question_id` (`question_id`)
        ) $collate;

        CREATE TABLE {$wpdb->prefix}ngs_countries (
          `country_code` varchar(2) NOT NULL,
          `language` varchar(8) NOT NULL DEFAULT '*',
          `country_name` varchar(256) NOT NULL,
          PRIMARY KEY (`country_code`,`language`)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}ngs_invitations (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `survey_id` int(10) unsigned NOT NULL,
          `survey_key` varchar(16) NOT NULL DEFAULT '',
          `campaign_id` int(10) unsigned NOT NULL DEFAULT 0,
          `contact_id` int(10) unsigned NOT NULL,
          `responder_name` varchar(256) NOT NULL,
          `email_address` varchar(1024) NOT NULL,
          `workflow_status` tinyint(4) DEFAULT NULL COMMENT '0 - Initiated, 1 - Sent, 2 - Response started, 3 - Response completed',
          `retries` int(11) DEFAULT 0,
          `created_by` int(10) unsigned NOT NULL,
          `created_date` datetime NOT NULL,
          `created_date_gmt` datetime NOT NULL,
          `modified_by` int(10) unsigned DEFAULT 0,
          `last_modified_date` datetime DEFAULT NULL,
          `last_modified_date_gmt` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_ngs_invitations_campaign_id_contact_id` (`campaign_id`,`contact_id`),
          KEY `idx_ngs_campaigns_invitations_survey_key` (`survey_key`),
          KEY `idx_ngs_campaigns_invitations_workflow_status` (`workflow_status`)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}ngs_pages (
          `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
          `survey_id` bigint(20) unsigned DEFAULT NULL,
          `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `sort_order` tinyint(3) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `idx_ngs_pages_survey_id_sort_order` (`survey_id`,`sort_order`)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}ngs_pages_questions_map (
          `page_id` mediumint(8) unsigned DEFAULT NULL,
          `question_id` mediumint(8) unsigned DEFAULT NULL,
          `sort_order` tinyint(3) unsigned DEFAULT NULL,
          UNIQUE KEY `page_id_question_id` (`page_id`,`question_id`),
          KEY `idx_ngs_pages_question_map_sort_order` (`sort_order`)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}ngs_questions (
          `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
          `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
          `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
          `qtype` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
          `params` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_ngs_questions_qtype` (`qtype`)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}ngs_responses (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `survey_id` bigint(20) unsigned NOT NULL,
          `survey_key` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `created_by` bigint(20) unsigned DEFAULT NULL,
          `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          `created_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          `finished_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          `finished_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`id`)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}ngs_response_details (
          `response_id` int(10) unsigned NOT NULL DEFAULT 0,
          `page_id` int(10) unsigned NOT NULL DEFAULT 0,
          `question_id` int(10) unsigned NOT NULL DEFAULT 0,
          `answer_id` int(10) unsigned NOT NULL DEFAULT 0,
          `column_id` int(10) unsigned NOT NULL DEFAULT 0,
          `answer_data` mediumtext DEFAULT NULL
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}ngs_rules (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `survey_id` bigint(20) unsigned DEFAULT NULL,
          `page_id` mediumint(8) unsigned DEFAULT NULL,
          `title` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `rule_content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `rule_actions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `sort_order` tinyint(3) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_bgs_rules_page_id_sort_order` (`page_id`,`sort_order`)
        ) $collate;
        
        CREATE TABLE {$wpdb->prefix}ngs_tracking (
          `post_id` int(10) unsigned NOT NULL DEFAULT 0,
          `post_type` tinyint(4) NOT NULL DEFAULT 0,
          `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          `ip_address` varchar(39) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `country` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `state` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `city` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `browser_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `browser_version` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `browser_engine` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `platform_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `platform_version` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `device_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `brand_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `model_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          PRIMARY KEY (`post_id`,`post_type`)
        ) $collate;
        ";
        
        return $tables;
    }

	/**
	 * NgSurvey activation routines.
	 *
	 * Installs the database and initializes the file system for NgSurvey.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	    if ( ! is_blog_installed() ) {
	        return;
	    }
	    
	    // Check if installation is already in progress
	    if ( 'yes' === get_transient( 'ngsurvey_installing' ) ) {
	        return;
	    }
	    
	    // Set transient, to detect installation
	    set_transient( 'ngsurvey_installing', 'yes', MINUTE_IN_SECONDS * 10 );
	    
	    add_option( NGSURVEY_OPTIONS, array() );
	    add_option( NGSURVEY_LICENSES, array() );
	    add_option( 'ngsurvey_version', '1.0.0' );
	    
	    self::create_tables();
	    
	    require_once ABSPATH . 'wp-admin/includes/file.php';
	    WP_Filesystem();
	    global $wp_filesystem;
	    
	    // Create uploads directory and move the templates and images if they does not exist
	    add_filter( 'upload_dir', array('NgSurvey_Activator', 'ngsurvey_custom_logger_dir'), 999 );
	    $upload_dir = wp_upload_dir();
	    remove_filter( 'upload_dir', array('NgSurvey_Activator', 'ngsurvey_custom_logger_dir'), 999 );
	    
	    $basedir = trailingslashit( $upload_dir['path'] ) . 'logs';
	    
	    if( !$wp_filesystem->is_dir( $basedir ) ) {
	    	$chmodir = 0755 & ~ umask();
	        $wp_filesystem->mkdir( $basedir );
	        $wp_filesystem->put_contents( trailingslashit( dirname( $basedir ) ) . '.htaccess', 'deny from all', $chmodir );
	    }
	    
	    global $wp_rewrite;
	    $wp_rewrite->flush_rules();
	    
	    delete_transient( 'ngsurvey_installing' );
	}
	
	/**
	 * Create database tables.
	 */
	public static function create_tables() {
	    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	    
	    dbDelta( self::get_schema() );
	}
	
	/**
	 * Sets the temporary custom directory for file uploads.
	 *
	 * @param array $dirs upload directory information from @wp_upload_dir
	 * @return array $dirs updated directory information
	 */
	public static function ngsurvey_custom_logger_dir( $dirs ) {
	    $dirs['subdir'] = '/' . NGSURVEY_MAIN_NAME;
	    $dirs['path'] = $dirs['basedir'] . $dirs['subdir'];
	    $dirs['url'] = $dirs['baseurl'] . $dirs['subdir'];
	    
	    return $dirs;
	}

	public static function updateProcessComplete( \WP_Upgrader $upgrader, array $hook_extra ) {

	    if (is_array($hook_extra) && array_key_exists('action', $hook_extra) && array_key_exists('type', $hook_extra) && array_key_exists('plugins', $hook_extra)) {
	        if ($hook_extra['action'] == 'update' && $hook_extra['type'] == 'plugin' && is_array($hook_extra['plugins']) && !empty($hook_extra['plugins'])) {

	            $this_plugin = plugin_basename( __FILE__ );
	            foreach ($hook_extra['plugins'] as $key => $plugin) {
	                if ($this_plugin == $plugin) {
	                    set_transient('ngsurvey_updated', 1);
	                    break;
	                }
	            }
	            unset($key, $plugin, $this_plugin);
	        }
	    }
	}
}
