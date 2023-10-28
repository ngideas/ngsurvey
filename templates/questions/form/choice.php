<?php
/**
 * Choice question form layout
 *
 * This file is used to render the choice type question form.
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

$question   = $data;
?>
<div class="answers-wrapper">
		
    <div class="answers mb-3">
    	<?php 
    	foreach ($question->answers as $i => $answer)
    	{
    		?>
    		<div class="row row-cols-lg-auto g-3 align-items-center mb-3 answer">
    			<div class="col-6">
        			<input type="text" name="ngform[answer_title][]" value="<?php echo esc_attr( $answer->title ); ?>" tabindex="<?php echo $i + 100 + 1; ?>"
        				placeholder="<?php echo esc_attr__('Enter your answer', 'ngsurvey' );?>" class="form-control me-2 required" size="60">
        		</div>
    			<div class="col-6">
        			<span class="answer-controls me-2">
        				<a class="btn-remove-answer" href="javascript:void(0)" title="<?php echo esc_attr__( 'Delete', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
            				<span class="dashicons dashicons-trash"></span>
            			</a>
        			</span>
        			<span class="answer-controls">
        				<a class="btn-sort-answer" href="javascript:void(0)" title="<?php echo esc_attr__( 'Click and drag to sort', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
            				<span class="dashicons dashicons-move"></span>
            			</a>
        			</span>
        		</div>
    			<input type="hidden" name="ngform[answer_id][]" value="<?php echo (int) $answer->id;?>">
    		</div>
    		<?php
    	}
    	?>
    </div>
    
    <button type="button" class="btn btn-outline-primary btn-add-answer">
    	<span class="dashicons dashicons-plus"></span> <?php echo esc_html__( 'Add an answer', 'ngsurvey' );?>
    </button>

    <button type="button" class="btn btn-outline-secondary btn-load-answer-presets" data-preset-type="answer" data-bs-toggle="modal" data-bs-target="#answer-presets-modal">
        <span class="dashicons dashicons-layout"></span> <?php echo esc_html__( 'Import Answers', 'ngsurvey' );?>
    </button>
    
    <div class="answer-template" style="display: none;">
    	<div class="row row-cols-lg-auto g-3 align-items-center mb-3 answer">
    		<div class="col-6">
    			<input type="text" name="answer_title" value="" placeholder="<?php echo esc_attr__('Enter your answer', 'ngsurvey' );?>" class="form-control me-2 required" size="60">
    		</div>
    		<div class="col-6">
        		<span class="answer-controls me-2">
        			<a class="btn-remove-answer" href="javascript:void(0)" title="<?php echo esc_attr__( 'Delete', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
        				<span class="dashicons dashicons-trash"></span>
        			</a>
        		</span>
        		<span class="answer-controls">
        			<a class="btn-sort-answer" href="javascript:void(0)" title="<?php echo esc_attr__( 'Click and drag to sort', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
        				<span class="dashicons dashicons-move"></span>
        			</a>
        		</span>
        	</div>
    		<input type="hidden" name="answer_id" value="0">
    	</div>
    </div>
</div>