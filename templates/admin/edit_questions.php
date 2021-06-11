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

$item       = $data->item;
$pages      = $data->pages;
$template   = $data->template;
?>
<div id="ngs">
    <div class="container-fluid py-3 pl-0">
		<div class="row">
			<div class="col">
                <h3 class="page-heading"><span class="dashicons dashicons-edit-page"></span> <?php echo wp_kses_post( $item->post_title ); ?></h3>
            </div>
		</div>
		<div class="row">
			<div class="col">
            	<ul class="nav nav-tabs my-3" id="main-navigation" role="tablist">
            		<li class="nav-item" role="presentation">
            			<a class="nav-link active" id="questions-tab" data-bs-toggle="tab" href="#questions-content" role="tab" aria-controls="questions-content" aria-selected="true">
            				<span class="dashicons dashicons-menu-alt"></span> <?php echo esc_html__( 'Questions', 'ngsurvey' ); ?>
            			</a>
            		</li>
            		<li class="nav-item" role="presentation">
            			<a class="nav-link" id="pages-tab" data-bs-toggle="tab" href="#pages-content" role="tab" aria-controls="pages-content" aria-selected="false">
            				<span class="dashicons dashicons-admin-page"></span> <?php echo esc_html__( 'Pages', 'ngsurvey' ); ?>
            			</a>
            		</li>
            		<li class="nav-item" role="presentation">
            			<a class="nav-link" id="conditional-rules-tab" data-bs-toggle="tab" href="#rules-content" role="tab" aria-controls="rules-content" aria-selected="false">
            				<span class="dashicons dashicons-randomize"></span> <?php echo esc_html__( 'Conditional Rules', 'ngsurvey' ); ?>
            			</a>
            		</li>
            	</ul>
                
                <div class="tab-content" id="questions-form">
					<div class="tab-pane fade show active" id="questions-content" role="tabpanel" aria-labelledby="questions-tab">
						<?php $template->get_template_part( 'admin/form/questions' );?>
					</div>

					<div class="tab-pane fade" id="pages-content" role="tabpanel" aria-labelledby="pages-tab">
						<?php $template->set_template_data( $data )->get_template_part( 'admin/form/pages' );?>
					</div>
					
					<div class="tab-pane fade" id="rules-content" role="tabpanel" aria-labelledby="conditional-rules-tab">
						<?php $template->set_template_data( $data )->get_template_part( 'admin/form/rules' );?>
					</div>
    			</div>
			</div>
		</div>
    </div>
    
    <div id="questions-json-data" style="display: none;"><?php echo wp_kses_post( $data->json_qns ); ?></div>
    
	<input type="hidden" name="ngform[sid]" value="<?php echo (int) $item->ID; ?>">
	<input type="hidden" name="ngform[pid]" value="<?php echo (int) $pages[0]->id; ?>">
	<input type="hidden" name="ngpage" value="form">

	<?php $template->get_template_part( 'admin/common/static' );?>    
    <?php $template->get_template_part( 'public/common/loader' );?>
    <?php wp_enqueue_editor();?>
    <?php wp_enqueue_media();?>
</div>
