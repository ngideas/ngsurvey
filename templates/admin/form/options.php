<?php
/**
 * Question options layout
 *
 * This file is used to render the question options.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$question = $data->question;

foreach ( $question->question_type->options as $option ) {

    switch ( $option->type ) {
        
        case 'select':
            ?>
            <div class="mb-4 row">
            	<label class="col-sm-3 control-label"><?php echo esc_html( $option->title ); ?>: </label>
            	<div class="col-sm-9">
            		<select name="ngform[options][<?php echo esc_attr($option->name); ?>]" size="1" class="form-control">
            			<?php foreach ( $option->options as $value => $label ): ?>
            			<option value="<?php echo esc_attr( $value ); ?>"<?php echo $question->params->get( $option->name, $option->default ) == $value ? ' selected="selected"' : '';?>>
            				<?php echo esc_html( $label );?>
            			</option>
            			<?php endforeach;?>
            		</select>
            		
            		<?php if( !empty( $option->help ) ): ?>
            		<small class="form-text text-muted"><?php echo esc_html( $option->help ); ?></small>
            		<?php endif; ?>
            	</div>
            </div>
            <?php
            break;
            
        case 'text': 
            ?>
            <div class="mb-4 row">
            	<label class="col-sm-3 control-label"><?php echo esc_html( $option->title ); ?>: </label>
            	<div class="col-sm-9">
            		<input type="text" name="ngform[options][<?php echo esc_attr($option->name); ?>]" 
            			value="<?php echo esc_attr( $question->params->get( $option->name, $option->default ) );?>" class="form-control">
            		
            		<?php if( !empty( $option->help ) ): ?>
            		<small class="form-text text-muted"><?php echo esc_html( $option->help ); ?></small>
            		<?php endif; ?>
            	</div>
            </div>
            <?php
            break;
            
        case 'textarea':
            ?>
            <div class="mb-4 row">
            	<label class="col-sm-3 control-label"><?php echo esc_html( $option->title ); ?>: </label>
            	<div class="col-sm-9">
            		<textarea type="text" name="ngform[options][<?php echo esc_attr($option->name); ?>]" 
            			class="form-control"><?php echo esc_textarea( $question->params->get( $option->name, $option->default ) );?></textarea>
            		
            		<?php if( !empty( $option->help ) ): ?>
            		<small class="form-text text-muted"><?php echo esc_html( $option->help ); ?></small>
            		<?php endif; ?>
            	</div>
            </div>
            <?php
            break;
            
        default:
            break;
    }
}
