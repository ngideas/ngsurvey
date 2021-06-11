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
<div class="questions">
	<?php foreach ( $data->questions as $question ):?>
	<div id="question-<?php echo (int) $question->id?>" 
		class="question qtype-<?php echo esc_attr( $question->qtype );?> <?php echo esc_attr( $question->params->get( 'question_class' ) );?><?php echo $question->validate ? ' validate' : '';?>"
		<?php echo $question->hidden ? ' style="display: none"' : '';?>
		data-type="<?php echo esc_attr( $question->qtype );?>">

		<div class="question-title <?php echo esc_attr( $question->params->get( 'title_class' ) );?>">
			<?php echo wp_kses_post( $question->title ); ?>
		</div>

		<?php if( !empty( $question->description ) ):?>
		<div class="question-description <?php echo esc_attr( $question->params->get( 'description_class' ) );?>">
			<?php echo wp_kses_post( $question->description ); ?>
		</div>
		<?php endif;?>
		
		<div class="question-body <?php echo esc_attr( $question->params->get( 'body_class' ) );?>">
			<div class="validation-messages"></div>
			<?php echo $question->response_form; //WPCS: Sanitized and escaped data from the layout files?>
		</div>
	</div>
	<?php endforeach;?>
</div>