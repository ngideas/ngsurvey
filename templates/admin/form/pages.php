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

$pages      = $data->pages;
?>
<div id="pages-list">
	<button type="button" class="btn btn-primary mb-3 btn-create-page">
    	<span class="dashicons dashicons-plus"></span> <?php echo esc_html__( 'Add a page', 'ngsurvey' ); ?>
    </button>

	<div id="pages" class="accordion">
    	<?php foreach ($pages as $page):?>
    	<div id="page-<?php echo (int) $page->id; ?>" data-id="<?php echo (int) $page->id; ?>" class="card page">
        	<div class="card-header d-flex" id="page-title-<?php echo (int) $page->id; ?>">
        		<h2 class="card-title m-0 flex-grow-1">
        			<button class="btn btn-link btn-block text-left" type="button" data-bs-toggle="collapse" 
        				data-bs-target=".collapse-<?php echo (int) $page->id; ?>" aria-expanded="true" aria-controls="collapse-<?php echo (int) $page->id; ?>">
        				<span class="dashicons dashicons-text-page"></span> <?php echo esc_html($page->title);?>
        			</button>
        		</h2>
        		<div class="p-2 d-none d-md-block">
        			[<?php echo esc_html__( 'ID', 'ngsurvey' ); ?>: <?php echo (int) $page->id;?>]
        		</div>
        		<div class="p-2 page-actions text-small">
        			<a class="btn-edit-page-title" href="javascript:void(0)" title="<?php echo esc_attr__( 'Edit', 'ngsurvey'); ?>" 
        				data-id="<?php echo (int) $page->id;?>" data-title="<?php echo esc_attr($page->title);?>" data-bs-toggle="tooltip">
        				<span class="dashicons dashicons-edit"></span>
        			</a>
        			<a class="btn-remove-page" data-id="<?php echo (int) $page->id;?>" href="javascript:void(0)" title="<?php echo esc_attr__( 'Delete', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
        				<span class="dashicons dashicons-trash"></span>
        			</a>
        			<a class="btn-sort-page" href="javascript:void(0)" title="<?php echo esc_attr__( 'Click and drag to sort', 'ngsurvey'); ?>" data-bs-toggle="tooltip">
        				<span class="dashicons dashicons-move"></span>
        			</a>
        		</div>
        	</div>
        </div>
    	<?php endforeach;?>
    </div>
</div>
