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

$questions  = isset( $data->questions ) ? $data->questions : array();
$pages      = isset( $data->pages ) ? $data->pages : array();
$rules      = isset( $data->rules ) ? $data->rules : array();
?>
<div id="conditional-rules" class="row">
	<div class="col-md-2">
		<div id="rule_pages" class="accordion accordion-flush mb-3">
        	<div class="accordion-item">
    			<h2 class="accordion-header">
    				<button class="accordion-button" type="button" data-bs-toggle="collapse" aria-expanded="true">
    					<?php echo esc_html__( 'Pages', 'ngsurvey' ); ?>
    				</button>
    			</h2>
        		
        		<div class="collapse show">
        			<div class="list-group list-group-flush rule-pages-list">
        				<?php foreach ( $pages as $i => $page ):?>
        				<a href="javascript:void(0);" data-id="<?php echo (int) $page->id;?>" class="list-group-item list-group-item-action<?php echo $i == 0 ? ' active' : ''; ?> btn-load-page-rules">
        					<?php echo esc_attr( $page->title ); ?>
        				</a>
        				<?php endforeach;?> 
        			</div>
        		</div>
        	</div>
        </div>
	</div>
	<div class="col-md-10">
        <div class="alert alert-info" role="alert">
        	<span class="dashicons dashicons-info"></span> <?php echo esc_html__( 'When you make changes to the questions, click on the page on left sidebar to reload the rules.', 'ngsurvey' ); ?>
        </div>
        
        <button type="button" class="btn btn-primary mb-3 btn-create-rule">
        	<span class="dashicons dashicons-plus"></span> <?php echo esc_html__( 'Add a rule', 'ngsurvey' ); ?>
        </button>
        
        <div class="accordion accordion-flush" id="rules">
        	<?php foreach ($rules as $rule):?>
        	<div id="rule-<?php echo (int) $rule->id; ?>" data-id="<?php echo (int) $rule->id; ?>" class="accordion-item rule">
        		<div class="accordion-header d-flex" id="rule-title-<?php echo (int) $rule->id; ?>">
                	<div class="accordion-button collapsed p-2">
            			<a class="flex-grow-1" href="#" data-bs-toggle="collapse" data-bs-target=".collapse-rule-<?php echo (int) $rule->id; ?>" aria-expanded="true" aria-controls="collapse-<?php echo (int) $rule->id; ?>">
            				<span class="dashicons dashicons-randomize"></span> <?php echo esc_html($rule->title);?>
            			</a>
                		<div class="collapse collapse-rule-<?php echo (int) $rule->id; ?> p-2 btn-save-rule-wrap" data-bs-parent="#rules">
                			<button type="button" class="btn btn-link p-0 m-0 btn-save-rule" style="line-height: 1;">
                				<span class="dashicons dashicons-cloud"></span> <?php echo esc_html__( 'Save', 'ngsurvey' )?>
                			</button>
                		</div>
                		<div class="p-2 d-none d-md-block">
                			[<?php echo esc_html__( 'ID', 'ngsurvey' ); ?>: <?php echo (int) $rule->id;?>]
                		</div>
                		<div class="p-2 rule-actions text-small">
                			<a class="btn-remove-rule" href="javascript:void(0)" title="<?php echo esc_attr__( 'Delete', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
                				<span class="dashicons dashicons-trash"></span>
                			</a>
                			<a class="btn-sort-rule" href="javascript:void(0)" title="<?php echo esc_attr__( 'Click and drag to sort', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
                				<span class="dashicons dashicons-move"></span>
                			</a>
                		</div>
                	</div>
                </div>
            
            	<div class="accordion-collapse collapse collapse-rule-<?php echo (int) $rule->id; ?> rule-body" aria-labelledby="heading-<?php echo (int) $rule->id; ?>" data-bs-parent="#rules">
            		<div class="accordion-body p-0">
            			<div class="list-group list-group-flush">
							<div class="list-group-item">
                                <form name="conditional-rules-form" action="" enctype="application/x-www-form-urlencoded" method="post">
        	                		<div class="mb-4">
                            			<label for="ngform_rule_title_<?php echo (int) $rule->id;?>" class="form-label"><?php echo esc_html__( 'Rule Title', 'ngsurvey' )?>:</label>
                            			<input type="text" name="ngform[title]" id="ngform_rule_title_<?php echo (int) $rule->id;?>" value="<?php echo esc_attr( $rule->title ); ?>" 
                            				class="form-control" aria-describedby="ngform_title_help_<?php echo (int) $rule->id;?>" required="required">
                            			<small id="ngform_title_help_<?php echo (int) $rule->id;?>" class="form-text text-muted"><?php echo esc_html__( 'Enter title of your rule', 'ngsurvey'  ); ?></small>
                            		</div>
        
                                
                        			<div class="row">
                        				<div class="col-md-8">
                        					<p class="lead"><?php echo esc_html__( 'Select conditions', 'ngsurvey' ); ?></p>
                        					
                        					<div class="rules-builder"></div>
                                        </div>
                                        <div class="col-md-4">
                                        	<p class="lead"><?php echo esc_html__( 'Choose an action', 'ngsurvey' ); ?></p>
                                        	
                                        	<select name="ngform[action]" class="form-control mb-3">
                                        		<option value=""><?php echo esc_html__( '- Select an action -', 'ngsurvey' ); ?></option>
                                        		<option value="show_page"><?php echo esc_html__( 'Show page', 'ngsurvey' ); ?></option>
                                        		<option value="skip_page"><?php echo esc_html__( 'Skip page', 'ngsurvey' ); ?></option>
                                        		<option value="show_question"><?php echo esc_html__( 'Show question on the current page', 'ngsurvey' ); ?></option>
                                        		<option value="hide_question"><?php echo esc_html__( 'Hide question on the current page', 'ngsurvey' ); ?></option>
                                        		<option value="show_future_qn"><?php echo esc_html__( 'Show question on a future page', 'ngsurvey' ); ?></option>
                                        		<option value="hide_future_qn"><?php echo esc_html__( 'Hide question on a future page', 'ngsurvey' ); ?></option>
                                        		<option value="finalize"><?php echo esc_html__( 'End response', 'ngsurvey' ); ?></option>
                                        	</select>
                                        	<select name="ngform[action_page]" class="form-control mb-3" style="display: none;">
                                        		<option value=""><?php echo esc_html__( '- Select a page -', 'ngsurvey' ); ?></option>
                                        	</select>
                                        	<select name="ngform[action_question]" class="form-control mb-3" style="display: none;">
                                        		<option value=""><?php echo esc_html__( '- Select a question -', 'ngsurvey' ); ?></option>
                                        	</select>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="ngform[rid]" value="<?php echo (int) $rule->id; ?>">
                                    <input type="hidden" name="ngform[rule_content]" value="<?php echo esc_attr( $rule->rule_content ); ?>">
                                    <input type="hidden" name="ngform[rule_actions]" value="<?php echo esc_attr( $rule->rule_actions ); ?>">
                                </form>
							</div>
						</div>
            		</div>
            	</div>
            </div>
        	<?php endforeach;?>
        </div>
        
        <div id="rule-templates" style="display: none;">
        	<?php foreach ( $questions as $question ): ?>
        	<div class="rule-template"><?php echo esc_html( implode( '', $question->rules ) ); ?></div>
        	<?php endforeach;?>
        </div>
	</div>
</div>
