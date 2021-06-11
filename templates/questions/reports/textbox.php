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

$question = $data;
?>

<?php if( !empty($question->chart_data) ):?>
<div class="row mb-3">
	<div class="col">
		<div style="height: 300px;">
			<canvas id="textbox-chart-<?php echo (int) $question->id;?>" 
				class="time-series-chart"
				data-title="<?php echo esc_attr__( 'Response Trends', 'ngsurvey' );?>"
				data-yaxis-label="<?php echo esc_attr__( 'Responses', 'ngsurvey' );?>"
				data-data="#chart-data-<?php echo (int) $question->id;?>"
				data-chart-type="line"
				data-legend="1"
				data-begin-at-zero="1"></canvas>
		</div>
	</div>
	<div id="chart-data-<?php echo (int) $question->id;?>" style="display: none;"><?php echo esc_html( json_encode( $question->chart_data ) );?></div>
</div>
<?php endif;?>
