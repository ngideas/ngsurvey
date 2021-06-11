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

$question = $data;
$custom   = '';

foreach ( $question->responses as $response ) {
    if( !empty( $response[ 'answer_data' ] ) ) {
        $custom = $response[ 'answer_data' ];
    }
}

switch ( $question->params->get('choice_type') ) {
    case 'radio':
        foreach ( $question->answers as $answer ) {
            ?>
            <div class="mb-1 form-check custom-radio<?php echo $question->params->get('show_answers_inline') ? ' custom-control-inline' : '';?>">
            	<input 
            		type="radio" 
            		id="answer-<?php echo $question->id;?>-<?php echo (int) $answer->id;?>" 
            		name="ngform[answers][<?php echo (int) $question->id?>][response][]"
            		value="<?php echo (int) $answer->id;?>"
            		class="form-check-input"
                    <?php echo $question->params->get('required') ? 'required="required"' : '';?>
                    <?php echo in_array( $answer->id, array_column( $question->responses, 'answer_id' ) ) ? 'checked="checked"' : '';?>>

            	<label class="form-check-label" for="answer-<?php echo (int) $question->id;?>-<?php echo (int) $answer->id;?>">
            		<?php echo wp_kses_post( $answer->title );?>
            	</label>
            </div>
            <?php
        }
        break;
        
    case 'checkbox':
        foreach ( $question->answers as $answer ) {
            ?>
            <div class="mb-1 form-check custom-checkbox<?php echo $question->params->get('show_answers_inline') ? ' custom-control-inline' : '';?>">
            	<input 
            		type="checkbox" 
            		id="answer-<?php echo (int) $question->id;?>-<?php echo (int) $answer->id;?>" 
            		name="ngform[answers][<?php echo (int) $question->id?>][response][]"
            		value="<?php echo (int) $answer->id;?>"
            		class="form-check-input"
            		<?php echo $question->params->get('minimum_selections') ? 'minlength="'.esc_attr( $question->params->get('minimum_selections') ).'"' : '';?>
            		<?php echo $question->params->get('maximum_selections') ? 'maxlength="'.esc_attr( $question->params->get('maximum_selections') ).'"' : '';?>
            		<?php echo $question->params->get('required') ? 'required="required"' : '';?>
            		<?php echo in_array( $answer->id, array_column( $question->responses, 'answer_id' ) ) ? 'checked="checked"' : '';?>>
            	<label class="form-check-label" for="answer-<?php echo (int) $question->id;?>-<?php echo (int) $answer->id;?>"><?php echo wp_kses_post( $answer->title );?></label>
            </div>
            <?php
        }
        break;
        
    case 'select':
        ?>
    	<select class="form-select" name="ngform[answers][<?php echo (int) $question->id?>][response][]" <?php echo $question->params->get('required') ? 'required="required"' : '';?>>
    		<option value=""><?php echo esc_html__( '- Select an option -', 'ngsurvey' );?></option>
    		<?php 
    		foreach ( $question->answers as $answer ) {
    		    ?>
    		    <option 
    		    	value="<?php echo (int) $answer->id;?>"
    		    	<?php echo in_array( $answer->id, array_column( $question->responses, 'answer_id' ) ) ? 'selected="selected"' : '';?>>
    		    	<?php echo wp_kses_post( $answer->title );?>
    		    </option>
    		    <?php
    		}
    		?>
    	</select>
        <?php
        break;
}

if( $question->params->get('show_custom_answer') ) {
    ?>
    <div class="form-inline">
        <div class="form-group">
        	<input type="text" 
        		class="form-control" 
        		name="ngform[answers][<?php echo (int) $question->id?>][custom]"
        		id="custom-answer-<?php echo (int) $question->id;?>" 
        		<?php echo $question->params->get('custom_answer_maxlength') > 0 ? ' maxlength="'.esc_attr( $question->params->get('custom_answer_maxlength') ).'"' : '';?>
        		placeholder="<?php echo esc_attr__( $question->params->get('custom_answer_placeholder', 'Other'), 'ngsurvey' );?>"
        		value="<?php echo esc_attr( $custom );?>">
        </div>
    </div>
    <?php
}
