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
	<div class="alert alert-info"><span class="dashicons dashicons-info"></span> <?php echo wp_kses_post( $data->error );?></div>
</div>
