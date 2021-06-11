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

$survey   = $data->item;
$pages    = $data->pages;
$template = $data->template;
?>
<div id="ngs">
	<div class="container-fluid p-0">
		<form name="survey-form" id="survey-form" class="needs-validation survey-form" method="post" enctype="application/x-www-form-urlencoded">
			<?php $template->set_template_data( $data )->get_template_part( 'public/survey/questions' );?>
			
			<div class="survey-response-preform">
    			<?php
            	/**
            	 * Allow third party plugins to add their own content before the action buttons.
            	 * 
            	 * Example usage: show captcha
            	 * 
            	 * @param object $data the current data object which contains survey item, pages, response id and survey key
            	 */
    			do_action( 'ngsurvey_response_before_action_buttons', $data );
            	?>
        	</div>
        	
        	<div class="alert alert-danger validation-error-message" style="display: none;">
        		<span class="dashicons dashicons-info"></span> 
        		<span class="error-message"><?php echo esc_html__( 'Your response has errors. Please correct them and try again.', 'ngsurvey' );?></span>
        	</div>
        	
        	<div class="actions-wrapper my-3">
    			<?php if( !empty( $data->is_last ) ): ?>
    			<button class="btn btn-primary btn-finish-response btn-save-response" type="button">
    				<span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Finish', 'ngsurvey' )?>
    			</button>
    			<?php else: ?>
    			<button class="btn btn-primary btn-continue-response btn-save-response" type="button">
    				<span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html__( 'Continue', 'ngsurvey' )?>
    			</button>
    			<?php endif;?>
        	</div>

        	<div id="conditional_rules" style="display: none;"><?php echo esc_html( json_encode( array_values( $data->rules ) ) );?></div>
        	<input type="hidden" name="ngform[sid]" value="<?php echo (int) $survey->ID;?>">
        	<input type="hidden" name="ngform[pid]" value="<?php echo (int) $data->pid; ?>">
        	<input type="hidden" name="ngform[rid]" value="<?php echo !empty($data->rid) ? (int) $data->rid : '';?>">
        	<input type="hidden" name="skey" value="<?php echo !empty($data->skey) ? esc_attr( $data->skey ) : '';?>">
		</form>
		
		<?php $template->get_template_part( 'public/common/loader' );?>
    </div>
</div>