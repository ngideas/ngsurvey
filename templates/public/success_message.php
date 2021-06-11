<?php
/**
 * The template for displaying single survey page.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/public/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="ngs">
	<div class="alert alert-success"><span class="dashicons dashicons-yes-alt"></span> <?php echo wp_kses_post( $data->title );?></div>
	<div class="end-message"><?php echo wp_kses_post( $data->message );?></div>
</div>
