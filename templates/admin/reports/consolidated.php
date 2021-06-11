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

$questions = isset( $data->questions ) ? $data->questions : array();
$template = $data->template;
?>
<div class="container-fluid pl-0">
	<div class="row">
		<div class="col">
		    <div class="action-toolbar d-flex flex-row-reverse mb-2">
		    	<a href="javascript:void(0);" id="btn-expand-all"><span class="dashicons dashicons-arrow-down-alt"></span> <?php echo esc_html__( 'Expand All', 'ngsurvey' );?></a>
		    	<a href="javascript:void(0);" id="btn-collapse-all" class="me-3"><span class="dashicons dashicons-arrow-up-alt"></span> <?php echo esc_html__( 'Collapse All', 'ngsurvey' );?></a>
		    </div>
			<div id="questions" class="accordion accordion-flush report-content">
			<?php 
			foreach ( $questions as $question ) 
			{
			    ?>
                <div id="question-<?php echo (int) $question->id; ?>" class="accordion-item question qtype-<?php echo esc_attr( $question->qtype ); ?>" data-id="<?php echo (int) $question->id; ?>">
                	<div class="accordion-header d-flex" id="question-title-<?php echo (int) $question->id; ?>">
                		<div class="accordion-button collapsed p-2">
                			<a href="#" class="flex-grow-1" data-bs-toggle="collapse" data-bs-target=".collapse-<?php echo (int) $question->id; ?>" aria-expanded="true" aria-controls="collapse-<?php echo (int) $question->id; ?>">
                    			<span class="<?php echo esc_attr( $question->question_type->icon ); ?> me-2"></span> <?php echo esc_html($question->title);?>
                    		</a>
                    		<div class="p-2 d-none d-md-block">
                    			[<?php echo esc_html__( 'ID', 'ngsurvey' ); ?>: <?php echo (int) $question->id;?>]
                    		</div>
                		</div>
                	</div>
                
                	<div class="accordion-collapse collapse collapse-<?php echo (int) $question->id; ?> question-body" aria-labelledby="heading-<?php echo (int) $question->id; ?>" data-bs-parent="#questions">
                		<div class="accordion-body p-0">
                			<div class="list-group list-group-flush">
								<div class="list-group-item">
                        			<div class="mb-4">
                        				<?php echo $question->reports_html;?>
                        			</div>
                        			
                        			<?php if( count( $question->custom_answers ) ):?>
                        			<div class="mb-4">
                        				<?php 
                        				if( !empty( $question->custom_html ) ) {
                        				    echo $question->custom_html;
                        				} else {
                        				    $template->set_template_data( $question )->get_template_part( 'admin/reports/custom/default' );
                        				}
                        				?>
                        			</div>
                        			<?php endif;?>
                        			
                        			<hr>
                        			<ul class="list-inline mb-0">
                        				<?php if( count( $question->custom_answers ) > 4 ):?>
                        				<li class="list-inline-item">
                        					<span class="dashicons dashicons-format-chat"></span>
                        					<a href="javascript:void(0);" class="btn-view-comments" data-bs-toggle="modal" data-bs-target="#custom-answers-modal" data-id="<?php echo (int) $question->id;?>">
                        						<?php echo esc_html__( 'View all comments', 'ngsurvey' );?>
                        					</a>
                        				</li>
                        				<?php endif;?>
                        				
                        				<?php if( $question->num_responses > 1 ):?>
                        				<li class="list-inline-item">
                        					<span class="dashicons dashicons-buddicons-buddypress-logo"></span>
                        					<?php echo sprintf( esc_html__( '%d people responded to this question.', 'ngsurvey' ), $question->num_responses );?>
                        				</li>
                        				<?php endif;?>
                        			</ul>
                        		</div>
                        	</div>
                		</div>
                	</div>
                	
                	<input type="hidden" name="ngform[qtype]" value="<?php echo esc_attr( $question->qtype );?>">
                </div>
            	<?php 
			}
			?>
			</div>

			<div class="modal fullscreen fade" id="custom-answers-modal" tabindex="-1" role="dialog" aria-labelledby="custom-answers-modal-label" aria-hidden="true">
				<div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="custom-answers-modal-label"><?php echo esc_html__( 'User Comments', 'ngsurvey' );?></h5>
							<button type="button" class="close btn-close-modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div class="table-responsive">
								<table class="table"></table>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary btn-close-modal">Close</button>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>