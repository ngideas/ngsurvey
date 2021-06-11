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

$total      = 0;

foreach ( $data->finished_pending_count as $stat ) {
    $total = $total + $stat[ 'value' ];
}
?>
<div class="container-fluid pl-0 survey-reports-dashboard">
    <div class="row">
    	<div class="col-md-6 mb-3">
    		<div class="card">
    			<div class="card-body">
    				<canvas id="countrywise-responses-chart" 
    					data-title="<?php echo esc_attr__( 'Location Trends', 'ngsurvey' );?>"
    					data-data="#countrywise-responses-data"></canvas>
    			</div>
    		</div>
    	</div>
    	<div class="col-md-6 mb-3">
    		<div class="card">
    			<div class="card-body">
    				<canvas id="datewise-responses-chart" 
    					data-title="<?php echo esc_attr__( 'Responses Trends', 'ngsurvey' );?>"
    					data-yaxis-label="<?php echo esc_attr__( 'Responses', 'ngsurvey' );?>"
    					data-data="#datewise-responses-data"></canvas>
    			</div>
    		</div>
    	</div>
    </div>
    <div class="row">
    	<div class="col-md-4 mb-3">
    		<table class="table table-hover dashboard-stats">
    			<tbody>
    				<tr>
    					<td><?php echo esc_html__( 'Total Responses', 'ngsurvey' );?></td>
    					<td><?php echo (int) $total;?></td>
    					<td><a href="#consolidated-report-tab"><?php echo esc_html__( 'View Consolidated Report', 'ngsurvey' );?></a></td>
    				</tr>
    				<tr>
    					<td><?php echo esc_html__( $data->finished_pending_count[ 0 ][ 'label' ] );?></td>
    					<td><?php echo (int) $data->finished_pending_count[ 0 ][ 'value' ];?></td>
    					<td><a href="#responses-list-tab"><?php echo esc_html__( 'Browse Completed Responses', 'ngsurvey' );?></a></td>
    				</tr>
    				<tr>
    					<td><?php echo esc_html__( $data->finished_pending_count[ 1 ][ 'label' ] );?></td>
    					<td><?php echo (int) $data->finished_pending_count[ 1 ][ 'value' ];?></td>
    					<td><a href="#responses-list-tab"><?php echo esc_html__( 'Browse Pending Responses', 'ngsurvey' );?></a></td>
    				</tr>
    				<tr>
    					<td><?php echo esc_html__( $data->tracking_stats[ 0 ][ 'label' ] );?></td>
    					<td><?php echo (int) $data->tracking_stats[ 0 ][ 'value' ];?></td>
    					<td><a href="#locations-report-tab"><?php echo esc_html__( 'View Location Report', 'ngsurvey' );?></a></td>
    				</tr>
    				<tr>
    					<td><?php echo esc_html__( $data->tracking_stats[ 2 ][ 'label' ] );?></td>
    					<td><?php echo (int) $data->tracking_stats[ 2 ][ 'value' ];?></td>
    					<td><a href="#platforms-report-tab"><?php echo esc_html__( 'View Platforms Report', 'ngsurvey' );?></a></td>
    				</tr>
    				<tr>
    					<td><?php echo esc_html__( $data->tracking_stats[ 1 ][ 'label' ] );?></td>
    					<td><?php echo (int) $data->tracking_stats[ 1 ][ 'value' ];?></td>
    					<td><a href="#browsers-report-tab"><?php echo esc_html__( 'View Browsers Report', 'ngsurvey' );?></a></td>
    				</tr>
    				<tr>
    					<td><?php echo esc_html__( $data->tracking_stats[ 3 ][ 'label' ] );?></td>
    					<td><?php echo (int) $data->tracking_stats[ 3 ][ 'value' ];?></td>
    					<td><a href="#devices-report-tab"><?php echo esc_html__( 'View Devices Report', 'ngsurvey' );?></a></td>
    				</tr>
    			</tbody>
    		</table>
    	</div>
    	<div class="col-md-4 mb-3">
    		<div class="card">
    			<div class="card-body">
    				<canvas id="finished-and-pending-count-chart" 
    					data-title="<?php echo esc_attr__( 'Response Status', 'ngsurvey' );?>"
    					data-data="#finished-and-pending-counts"></canvas>
    			</div>
    		</div>
    	</div>
    	<div class="col-md-4 mb-3">
    		<table class="table table-hover">
    			<thead>
    				<tr>
    					<th><?php echo esc_html__( 'ID', 'ngsurvey' );?></th>
    					<th><?php echo esc_html__( 'User Name', 'ngsurvey' );?></th>
    					<th class="d-none d-sm-table-cell"><?php echo esc_html__( 'Start Date', 'ngsurvey' );?></th>
    					<th><?php echo esc_html__( 'Results', 'ngsurvey' );?></th>
    				</tr>
    			</thead>
    			<tbody>
    				<?php foreach ( $data->latest_responses as $response ):?>
    				<tr>
    					<td><?php echo (int) $response->id?></td>
    					<td><?php echo !empty( $response->display_name ) ? esc_html( $response->display_name ) : esc_html__( 'Guest', 'ngsurvey' );?></td>
    					<td class="d-none d-sm-table-cell"><?php echo esc_html__( $response->created_date_gmt ); ?></td>
    					<td>
    						<a href="javascript:void(0);" class="btn-view-response" data-id="<?php echo (int) $response->id;?>" data-bs-toggle="modal" data-bs-target="#response-details-modal">
    							<?php echo esc_html__( 'View', 'ngsurvey' );?>
    						</a>
    					</td>
    				</tr>
    				<?php endforeach;?>
    			</tbody>
    		</table>
    	</div>
    </div>
</div>
