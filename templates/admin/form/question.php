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

$question       = $data->question;
$template       = $data->template;
?>
<div id="question-<?php echo (int) $question->id; ?>" class="accordion-item question qtype-<?php echo esc_attr( $question->qtype ); ?>" data-id="<?php echo (int) $question->id; ?>">
	<div class="accordion-header d-flex" id="question-title-<?php echo (int) $question->id; ?>">
		<div class="accordion-button collapsed p-2">
    		<a href="#" class="flex-grow-1" data-bs-toggle="collapse" data-bs-target=".collapse-<?php echo (int) $question->id; ?>" aria-expanded="true" aria-controls="collapse-<?php echo (int) $question->id; ?>">
    			<span class="<?php echo esc_attr( $question->question_type->icon ); ?> me-2"></span> <?php echo esc_html($question->title);?>
    		</a>
    		<div class="collapse collapse-<?php echo (int) $question->id; ?> p-2 btn-save-question-wrap" data-bs-parent="#questions">
    			<button type="button" class="btn btn-link p-0 m-0 btn-save-question" style="line-height: 1;">
    				<span class="dashicons dashicons-cloud"></span> <?php echo esc_html__( 'Save', 'ngsurvey' )?>
    			</button>
    		</div>
    		<div class="p-2 d-none d-md-block">
    			[<?php echo esc_html__( 'ID', 'ngsurvey' ); ?>: <?php echo (int) $question->id;?>]
    		</div>
    		<div class="p-2 question-actions text-small">
    			<a class="btn-remove-question" href="javascript:void(0)" title="<?php echo esc_attr__( 'Delete', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
    				<span class="dashicons dashicons-trash"></span>
    			</a>
    			<a class="btn-move-question" href="javascript:void(0)" title="<?php echo esc_attr__( 'Move', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
    				<span class="dashicons dashicons-share-alt2"></span>
    			</a>
    			<a class="btn-copy-question" href="javascript:void(0)" title="<?php echo esc_attr__( 'Duplicate', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
    				<span class="dashicons dashicons-format-gallery"></span>
    			</a>
    			<a class="btn-sort-question" href="javascript:void(0)" title="<?php echo esc_attr__( 'Click and drag to sort', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
    				<span class="dashicons dashicons-move"></span>
    			</a>
    		</div>
    	</div>
	</div>

	<div class="accordion-collapse collapse collapse-<?php echo (int) $question->id; ?> question-body" aria-labelledby="heading-<?php echo (int) $question->id; ?>" data-bs-parent="#questions">
		<div class="accordion-body p-0">
			<div class="list-group list-group-flush">
				<div class="list-group-item">
        			<form name="ngForm" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" enctype="multipart/form-data" method="post">
            			<nav>
            				<div class="nav nav-tabs" id="nav-tab" role="tablist">
            					<a class="nav-item nav-link active" id="nav-details-tab" data-bs-toggle="tab" href="#nav-details-<?php echo (int) $question->id; ?>" 
            						role="tab" aria-controls="nav-details-<?php echo (int) $question->id; ?>" aria-selected="true">
            						<?php echo esc_html__( 'Basic Details', 'ngsurvey' ); ?>
            					</a>
            					<?php if( !empty( $question->form_html ) ): ?>
            					<a class="nav-item nav-link" id="nav-question-data-tab" data-bs-toggle="tab" href="#nav-question-data-<?php echo (int) $question->id; ?>" 
            						role="tab" aria-controls="nav-question-data-<?php echo (int) $question->id; ?>" aria-selected="false">
            						<?php echo esc_html__( 'Question Data', 'ngsurvey' ); ?>
            					</a>
            					<?php endif;?>
            					<a class="nav-item nav-link" id="nav-options-tab" data-bs-toggle="tab" href="#nav-options-<?php echo (int) $question->id; ?>" 
            						role="tab" aria-controls="nav-options-<?php echo (int) $question->id; ?>" aria-selected="false">
            						<?php echo esc_html__( 'Options', 'ngsurvey' ); ?>
            					</a>
            				</div>
                        </nav>
                        
                        <div class="tab-content" id="nav-tab-content">
            				<!-- Main details tab -->
                        	<div class="tab-pane fade show active pt-3 question-main" id="nav-details-<?php echo (int) $question->id; ?>" role="tabpanel" aria-labelledby="nav-details-tab">
                        		<div class="mb-4">
                        			<label for="ngform_title_<?php echo (int) $question->id;?>" class="form-label"><?php echo esc_html__( 'Question Title', 'ngsurvey' )?>:</label>
                        			<input type="text" name="ngform[title]" id="ngform_title_<?php echo (int) $question->id;?>" value="<?php echo esc_attr( $question->title ); ?>" 
                        				class="form-control" aria-describedby="ngform_title_help_<?php echo (int) $question->id;?>" required="required">
                        			<small id="ngform_title_help_<?php echo (int) $question->id;?>" class="form-text text-muted"><?php echo esc_html__( 'Enter title of your question', 'ngsurvey'  ); ?></small>
                        		</div>
                        		
                        		<div class="mb-4">
                        			<label for="ngform_title_<?php echo (int) $question->id;?>"><?php echo esc_html__( 'Question Description', 'ngsurvey' )?>:</label>
                        			<textarea name="ngform[description]" id="ngform_description_<?php echo (int) $question->id;?>" rows="5" 
                        				cols="50" class="form-control ngeditor"><?php echo esc_textarea( $question->description );?></textarea>
                        		</div>
                        	</div>
            
        					<?php if( !empty( $question->form_html ) ): ?>
            				<!-- Question specific details tab -->
                        	<div class="tab-pane fade pt-3 question-data" id="nav-question-data-<?php echo (int) $question->id; ?>" role="tabpanel" aria-labelledby="nav-question-data-tab">
                        		<?php echo $question->form_html; ?>
                        	</div>
                        	<?php endif;?>
        
            				<!-- Question options tab -->
                        	<div class="tab-pane fade pt-3 question-options" id="nav-options-<?php echo (int) $question->id; ?>" role="tabpanel" aria-labelledby="nav-options-tab">
                        		<?php $template->get_template_part( 'admin/form/options' ); ?>
                        	</div>
                        </div>
        
                    	<input type="hidden" name="ngform[qid]" value="<?php echo (int) $question->id; ?>">
                    	<input type="hidden" name="ngform[qtype]" value="<?php echo esc_attr( $question->qtype ); ?>">
                    </form>
				</div>
			</div>
		</div>
	</div>
</div>
