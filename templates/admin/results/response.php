<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing survey reports aspects of the plugin.
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
?>
<div class="container-fluid pl-0 survey-response">
	<div class="row">
		<div class="col questions">
			<?php foreach ( $data->questions as $question ):?>
        	<div id="question-<?php echo (int) $question->id?>" 
        		class="question qtype-<?php echo esc_attr( $question->qtype );?> <?php echo !empty( $question->params->get( 'question_class' ) ) ? esc_attr( $question->params->get( 'question_class' ) ) : 'mb-4';?>">
        
        		<div class="question-title <?php echo !empty( $question->params->get( 'title_class' ) ) ? esc_attr( $question->params->get( 'title_class' ) ) : 'mb-3';?>">
        			<span class="<?php echo esc_attr( $question->question_type->icon ); ?> align-middle"></span> <?php echo wp_kses_post( $question->title ); ?>
        		</div>
        
        		<?php if( !empty( $question->description ) ):?>
        		<div class="question-description <?php echo !empty( $question->params->get( 'description_class' ) ) ? esc_attr( $question->params->get( 'description_class' ) ) : 'mb-3';?>">
        			<?php echo wp_kses_post( $question->description ); ?>
        		</div>
        		<?php endif;?>
        		
        		<div class="question-body <?php echo esc_attr( $question->params->get( 'body_class', 'card-body pt-0' ) );?>">
        			<div class="question-response">
        				<?php echo $question->results_html; //WPCS: Sanitized HTML from the layout files?>
        			</div>
        		
    				<?php 
    				$custom = null;
    				foreach ( $question->responses as $response ) {
    				    if( $response[ 'answer_id' ] == 1 && !empty( $response[ 'answer_data' ] ) ) {
    				        $custom = $response[ 'answer_data' ];
    				        break;
    				    }
    				}
    				
    				if( $custom ) {
    				    ?>
    				    <div class="question-comments mt-3">
        				    <div class="lead mb-2"><?php echo esc_html__( 'Additional comments', 'ngsurvey' );?>:</div>
        				    <?php echo wp_kses_post( $custom );?>
    				    </div>
    				    <?php
    				}
    				?>
        		</div>
        		<input type="hidden" name="ngform[qtype]" value="<?php echo esc_attr( $question->qtype );?>">
        	</div>
        	<?php endforeach;?>
		</div>
	</div>
</div>