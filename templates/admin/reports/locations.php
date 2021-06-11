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
<div class="container-fluid pl-0 survey-browsers-list">
	<div class="row">
		<div class="col-md-5 mb-3">
			<div class="card mb-3">
    			<div class="card-body">
    				<canvas id="locations-bar-chart" 
    					data-title="<?php echo esc_attr__( 'Location Trends', 'ngsurvey' );?>"
    					data-data="#locations-data"></canvas>
    			</div>
    		</div>
			<div class="card">
				<div class="card-body">
    				<canvas id="locations-pie-chart" 
    					data-title="<?php echo esc_attr__( 'Location Trends', 'ngsurvey' );?>"
    					data-data="#locations-data"></canvas>
    			</div>
    		</div>
		</div>
		<div class="col">
            <table class="table table-hover me-0 data-table">
            	<thead>
            		<tr>
            			<th><?php echo esc_html__( 'Country', 'ngsurvey' );?></th>
            			<th><?php echo esc_html__( 'State', 'ngsurvey' );?></th>
            			<th><?php echo esc_html__( 'City', 'ngsurvey' );?></th>
            			<th width="10%"><?php echo esc_html__( 'Count', 'ngsurvey' );?></th>
            		</tr>
            	</thead>
            	<tbody class="report-content"></tbody>
            </table>
		</div>
	</div>
</div>