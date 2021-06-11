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

$item       = $data->item;
$template   = $data->template;
$total      = 0;

foreach ( $data->finished_pending_count as $stat ) {
    $total = $total + $stat[ 'value' ];
}

$ng_colors = array(
    'red' => 'rgb(255, 99, 132)',
    'orange' => 'rgb(255, 159, 64)',
    'yellow' => 'rgb(255, 205, 86)',
    'green' => 'rgb(75, 192, 192)',
    'blue' => 'rgb(54, 162, 235)',
    'purple' => 'rgb(153, 102, 255)',
    'grey' => 'rgb(119, 136, 153)',
    'indianred' => 'rgb(205, 92, 92)',
    'teal' => 'rgb(0, 128, 128)',
    'steelblue' => 'rgb(70, 130, 180)',
    'sandybrown' => 'rgb(244, 164, 96)',
    'olive' => 'rgb(128, 128, 0)',
    'mediumvoiletred' => 'rgb(199, 21, 133)',
    'gold' => 'rgb(255, 215, 0)',
    'indigo' => 'rgb(75, 0, 130)',
    'lightgreen' => 'rgb(144, 238, 144)',
    'wheat' => 'rgb(245, 222, 179)',
    'rosybrown' => 'rgb(188, 143, 143)',
    'skyblue' => 'rgb(135, 206, 235)',
    'lime' => 'rgb(0, 255, 0)',
    'success' => 'rgb(40, 167, 69)',
    'info' => 'rgb(23, 162, 184)',
    'danger' => 'rgb(220, 53, 69)'
);
?>
<div id="ngs">

	<h3 class="page-heading my-3"><span class="dashicons dashicons-chart-bar"></span> <?php echo wp_kses_post( $item->post_title ); ?></h3>

	<nav class="mb-3">
		<div class="nav nav-tabs" id="nav-reports" role="tablist">
    		<a class="nav-item nav-link active" id="dashboard-tab" href="#dashboard" data-bs-toggle="tab" role="tab" aria-controls="dashboard" aria-selected="true">
    			<?php echo esc_html__( 'Dashboard', 'ngsurvey' );?>
    		</a>
    		<a class="nav-item nav-link" id="consolidated-report-tab" href="#consolidated-report" 
    			data-task="consolidated.display" data-bs-toggle="tab" role="tab" aria-controls="consolidated-report" aria-selected="false">
    			<?php echo esc_html__( 'Consolidated Report', 'ngsurvey' );?>
    		</a>
    		<a class="nav-item nav-link" id="responses-list-tab" href="#responses-list" 
    			data-task="responses.display" data-bs-toggle="tab" role="tab" aria-controls="responses-list" aria-selected="false">
    			<?php echo esc_html__( 'All Responses', 'ngsurvey' );?>
    		</a>
    		<a class="nav-item nav-link" id="locations-report-tab" href="#locations-report" 
    			data-task="locations.display" data-bs-toggle="tab" role="tab" aria-controls="locations-report" aria-selected="false">
    			<?php echo esc_html__( 'Locations', 'ngsurvey' );?>
    		</a>
    		<a class="nav-item nav-link" id="platforms-report-tab" href="#platforms-report" 
    			data-task="platforms.display" data-bs-toggle="tab" role="tab" aria-controls="platforms-report" aria-selected="false">
    			<?php echo esc_html__( 'Platforms', 'ngsurvey' );?>
    		</a>
    		<a class="nav-item nav-link" id="browsers-report-tab" href="#browsers-report" 
    			data-task="browsers.display" data-bs-toggle="tab" role="tab" aria-controls="browsers-report" aria-selected="false">
    			<?php echo esc_html__( 'Browsers', 'ngsurvey' );?>
    		</a>
    		<a class="nav-item nav-link" id="devices-report-tab" href="#devices-report" 
    			data-task="devices.display" data-bs-toggle="tab" role="tab" aria-controls="devices-report" aria-selected="false">
    			<?php echo esc_html__( 'Devices', 'ngsurvey' );?>
    		</a>
		</div>
	</nav>

	<div class="tab-content" id="survey-reports">
		<div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
			<?php $template->get_template_part( 'admin/reports/dashboard' );?>
		</div>
		<div class="tab-pane fade" id="consolidated-report" role="tabpanel" aria-labelledby="consolidated-report-tab">
			<?php $template->get_template_part( 'admin/reports/consolidated' );?>
		</div>
		<div class="tab-pane fade" id="responses-list" role="tabpanel" aria-labelledby="responses-list-tab">
			<?php $template->get_template_part( 'admin/reports/responses' );?>
		</div>
		<div class="tab-pane fade" id="locations-report" role="tabpanel" aria-labelledby="locations-report-tab">
			<?php $template->get_template_part( 'admin/reports/locations' );?>
		</div>
		<div class="tab-pane fade" id="platforms-report" role="tabpanel" aria-labelledby="platforms-report-tab">
			<?php $template->get_template_part( 'admin/reports/platforms' );?>
		</div>
		<div class="tab-pane fade" id="browsers-report" role="tabpanel" aria-labelledby="browsers-report-tab">
			<?php $template->get_template_part( 'admin/reports/browsers' );?>
		</div>
		<div class="tab-pane fade" id="devices-report" role="tabpanel" aria-labelledby="devices-report-tab">
			<?php $template->get_template_part( 'admin/reports/devices' );?>
		</div>
	</div>
    
	<input type="hidden" name="ngform[sid]" value="<?php echo (int) $item->ID; ?>">
	<input type="hidden" name="ngpage" value="reports">

    <div style="display: none;">
    	<div id="datewise-responses-data"><?php echo esc_html( json_encode( $data->datewise_responses ) );?></div>
    	<div id="countrywise-responses-data"><?php echo esc_html( json_encode( $data->countrywise_responses ) );?></div>
    	<div id="locations-data"><?php echo esc_html( json_encode( $data->locations_responses ) );?></div>
    	<div id="platforms-data"><?php echo esc_html( json_encode( $data->platforms_responses ) );?></div>
    	<div id="browsers-data"><?php echo esc_html( json_encode( $data->browsers_responses ) );?></div>
    	<div id="devices-data"><?php echo esc_html( json_encode( $data->devices_responses ) );?></div>
    	<div id="finished-and-pending-counts"><?php echo esc_html( json_encode( $data->finished_pending_count ) );?></div>
    	<div id="ng-bg-colors"><?php echo esc_html( json_encode($ng_colors) );?></div>
    	<div id="pro-version-info"><?php $template->get_template_part( 'public/common/pro' );?></div>
    </div>
    
	<div class="modal fullscreen fade" id="response-details-modal" tabindex="-1" role="dialog" aria-labelledby="response-details-modal-label" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="response-details-modal-label"><?php echo esc_html__( 'Response details', 'ngsurvey' );?></h5>
					<button type="button" class="close btn-close-modal" aria-label="<?php echo esc_html__( 'Close', 'ngsurvey' );?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table class="table"></table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-close-modal"><?php echo esc_html__( 'Close', 'ngsurvey' );?></button>
				</div>
			</div>
		</div>
	</div>

	<?php $template->get_template_part( 'admin/common/static' );?>
    <?php $template->get_template_part( 'public/common/loader' );?>
    <?php wp_enqueue_media();?>
</div>