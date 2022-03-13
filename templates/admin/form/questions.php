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

$questions  = $data->questions;
$pages      = $data->pages;
$groups     = $data->groups;
$template   = $data->template;
$count      = 0;

ksort($groups);
?>
<div class="row">
	<div class="col-md-2">
        <div id="question-types" class="accordion accordion-flush mb-3">
        	<?php foreach ( $groups as $group => $types ): ?>
        	<div class="accordion-item">
    			<h2 class="accordion-header">
    				<button class="accordion-button <?php echo $count == 0 ? '' : ' collapsed';?>" type="button" data-bs-toggle="collapse" 
    					data-bs-target="#collapse-<?php echo esc_attr($group); ?>" aria-expanded="true" aria-controls="collapse-<?php echo esc_attr($group); ?>">
    					<?php echo esc_html__( ucfirst($group), 'ngsurvey' ); ?>
    				</button>
    			</h2>
        		
        		<div id="collapse-<?php echo esc_attr( $group ); ?>" class="accordion-collapse collapse<?php echo $count == 0 ? ' show' : ''; $count++?>" 
        			aria-labelledby="heading-<?php echo esc_attr( $group ); ?>" data-bs-parent="#question-types">
        			
        			<div class="list-group list-group-flush ">
        				<?php foreach ( $types as $type ):?>
        				<a href="javascript:void(0);" class="list-group-item list-group-item-action btn-add-question" data-type="<?php echo esc_attr($type->name);?>">
        					<span class="<?php echo esc_attr( $type->icon ); ?>"></span> <?php echo esc_attr( $type->title ); ?>
        				</a>
        				<?php endforeach;?> 
        			</div>
        		</div>
        	</div>
        	<?php endforeach;?>
        </div>
	</div>
	<div class="col-md-10">
        <div id="questions-form">
            <div id="pages-form" class="pages-form mb-3">
            	<div class="card" style="padding: 0;">
            		<div class="card-body">
            			<div class="row">
            				<div class="col-md-4">
                    			<select class="form-select page-id w-100" id="page-id" name="page-id">
                    				<?php foreach ( $pages as $page ):?>
                    				<option value="<?php echo (int) $page->id; ?>" data-title="<?php echo esc_attr( $page->title ); ?>">
                    					<?php echo esc_html( $page->title ); ?> [ID: <?php echo (int) $page->id; ?>]
                    				</option>
                    				<?php endforeach;?>
                    			</select>
            				</div>
            				<div class="col-md-8">
                    			<?php 
                    			$buttons = array(
                    			    array( 'name' => 'btn-create-page', 'icon' => 'dashicons dashicons-plus', 'class' => 'btn-primary', 'text' => '', 'title' => __( 'Add page' , 'ngsurvey' ) ),
                    			    array( 'name' => 'btn-edit-page-title', 'icon' => 'dashicons dashicons-edit', 'class' => 'btn-secondary', 'text' => '', 'title' => __( 'Edit title', 'ngsurvey' ) ),
                    			    array( 'name' => 'btn-remove-page', 'icon' => 'dashicons dashicons-trash', 'class' => 'btn-danger', 'text' => '', 'title' => __( 'Remove page', 'ngsurvey' ) )
                    			);
                    			$buttons = apply_filters( 'ngsurvey_edit_questions_actions', $buttons );
                    			
                    			foreach ( $buttons as $button ) {
                    			    ?>
                    			    <button type="button" 
                    			    	class="btn btn-sm <?php echo esc_attr( $button[ 'class' ] );?> me-1 <?php echo esc_attr( $button[ 'name' ] );?>" 
                    			    	title="<?php echo esc_attr( $button[ 'title' ] );?>">
                    			    	<span class="<?php echo esc_attr( $button[ 'icon' ] );?>"></span> <?php echo esc_html( $button[ 'text' ] );?>
                    			    </button>
                    			    <?php
                    			}
                    			?>
                    		</div>
            			</div>
            		</div>
            	</div>
            </div>
        
            <div class="alert alert-info" role="alert">
            	<span class="dashicons dashicons-info"></span> <?php echo esc_html__( 'Click on the question types on the left sidebar to add new questions.', 'ngsurvey' ); ?>
            </div>
        
            <div class="accordion accordion-flush" id="questions">
            	<?php foreach ( $questions as $question ): ?>
            		<?php $template->set_template_data( array( 'question' => $question, 'template' => $template ) )->get_template_part( 'admin/form/question' ); ?>
            	<?php endforeach; ?>
            </div>
        </div>
	</div>
</div>
