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
<div class="container-fluid pl-0 survey-responses-list">
	<div class="row">
		<div class="col">
            <table class="table table-hover bg-white me-0 data-table">
            	<thead>
            		<tr>
            			<th width="1%"><input name="select_all" value="1" class="btn-select-all" type="checkbox" /></th>
            			<th><?php echo esc_html__( 'User Name', 'ngsurvey' );?></th>
            			<th class="d-none d-sm-table-cell"><?php echo esc_html__( 'Start Date', 'ngsurvey' );?></th>
            			<th class="d-none d-md-table-cell"><?php echo esc_html__( 'Finished On', 'ngsurvey' );?></th>
            			<th width="5%"><?php echo esc_html__( 'Status', 'ngsurvey' );?></th>
            			<th width="5%"><?php echo esc_html__( 'Results', 'ngsurvey' );?></th>
            			<th width="2%"><?php echo esc_html__( 'ID', 'ngsurvey' );?></th>
            		</tr>
            	</thead>
            	<tbody class="report-content"></tbody>
            </table>
		</div>
	</div>
</div>