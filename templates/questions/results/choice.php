<?php
/**
 * The template for displaying Choice question type on front-end.
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

foreach ( $data->answers as $answer ) {
    ?>
    <div class="mb-2">
    	<div class="text-inline">
        	<?php if(in_array( $answer->id, array_column( $data->responses, 'answer_id' ) ) ):?>
        	<span class="dashicons dashicons-yes-alt text-success"></span>
        	<?php else :?>
        	<span class="dashicons dashicons-marker text-muted"></span>
        	<?php endif;?>
        	<?php echo wp_kses_post( $answer->title );?>
    	</div>
    </div>
    <?php
}
