<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing edit question aspects of the plugin.
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

$options    = $data->options;
$params     = $data->params;
?>
<div id="ngs">
	<?php foreach ( $options as $option ):?>
	<div class="mb-4">
		<label for="<?php echo esc_attr( $option->name );?>" class="d-block mb-2"><?php echo wp_kses_post( $option->title );?></label>
		<?php 
		switch ( $option->type ) {
		    case 'checkbox':
		    case 'radio':
		        
		        foreach ( $option->options as $value => $label ) {
		            ?>
    		        <div class="form-check custom-<?php echo esc_attr( $option->type );?> custom-control-inline">
    		        	<input type="<?php echo esc_attr( $option->type );?>" 
    		        		id="<?php echo esc_attr( $option->name . $value );?>" 
    		        		name="ngsurvey_settings[<?php echo esc_attr( $option->name );?>][]"
    		        		value="<?php echo esc_attr( $value )?>"
    		        		<?php echo in_array( $value, $params[ $option->name ] ) ? 'checked="checked"' : '';?> 
    		        		class="form-check-input">
    		        	<label class="form-check-label" for="<?php echo esc_attr( $option->name . $value );?>"><?php echo wp_kses_post( $label );?></label>
    		        </div>
		            <?php
		        }
		        break;
		        
		    case 'select':
		        ?>
		        <select id="<?php echo esc_attr( $option->name );?>" name="ngsurvey_settings[<?php echo esc_attr( $option->name );?>]" class="form-select">
		        	<?php foreach ( $option->options as $value => $label ):?>
		        	<option value="<?php echo esc_attr( $value )?>"<?php echo $params[ $option->name ] == $value ? 'selected="selected"' : '';?>>
		        		<?php echo esc_html( $label );?>
		        	</option>
		        	<?php endforeach;?>
		        </select>
		        <?php
		        break;
		        
            case 'textbox':
                ?>
            	<input type="text" 
            		class="form-control" 
            		name="ngsurvey_settings[<?php echo esc_attr( $option->name );?>]" 
            		id="<?php echo esc_attr( $option->name );?>" 
            		aria-describedby="help_<?php echo esc_attr( $option->name )?>"
            		value="<?php echo esc_attr( $params[ $option->name ] );?>">
            	<small id="help_<?php echo esc_attr( $option->name )?>" class="form-text text-muted"></small>
				<?php
				break;
				
            case 'textarea':
                ?>
                <textarea
                     class="form-control"
                     name="ngsurvey_settings[<?php echo esc_attr( $option->name );?>]"
                     id="<?php echo esc_attr( $option->name );?>"
                     aria-describedby="help_<?php echo esc_attr( $option->name )?>"
                     rows="3"><?php echo esc_html( $params[ $option->name ] );?></textarea>
				<small id="help_<?php echo esc_attr( $option->name )?>" class="form-text text-muted"></small>
				<?php
                break;
		}
		?>
	</div>
	<?php endforeach;?>
</div>
