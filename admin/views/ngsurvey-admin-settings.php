<?php
/**
 * Provide a admin settings view for the plugin
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/views
 */

// check user capabilities
if (! current_user_can('manage_options')) {
    return;
}
?>
<div class="wrap">
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <form action="options.php" method="post">
        <?php
            settings_fields( NGSURVEY_OPTIONS );
            do_settings_sections( $this->plugin_name );
            submit_button();
        ?>
    </form>
</div>