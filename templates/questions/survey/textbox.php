<?php
/**
 * The template for displaying Textbox question type on front-end.
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

$question   = $data;
$custom     = '';

foreach ( $question->responses as $response ) {
    if( empty( $response->answer_id ) && !empty( $response[ 'answer_data' ] ) ) {
        $custom = $response[ 'answer_data' ];
    }
}
?>
<div class="form-group">
	<input type="<?php echo esc_attr( $question->params->get( 'textbox_type', 'text' ) )?>" 
      	class="form-control mb-2 me-sm-2" 
      	id="answer-<?php echo (int) $question->id;?>"
      	name="ngform[answers][<?php echo (int) $question->id?>][response][]" 
      	data-regex="<?php echo esc_attr( $question->params->get( 'regular_expression' ) )?>"
      	data-message="<?php echo esc_attr( $question->params->get( 'validation_message' ) )?>"
      	value="<?php echo esc_attr( $custom );?>"
      	placeholder="<?php echo esc_attr__( 'Enter your answer', 'ngsurvey' );?>"
      	<?php echo $question->params->get('required') ? 'required="required"' : '';?>>
</div>
