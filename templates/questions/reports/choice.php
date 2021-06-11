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

$question       = $data;
$responses      = $question->responses;
$chartData      = array();
$totalResponses = 0;

// First calculate the statistics
foreach ( $question->answers as &$answer ) {
    $answer->votes = 0;
    foreach ( $responses as $response ) {
        if( $response->answer_id == $answer->id ) {
            $answer->votes = (int) $response->responses;
            break;
        }
    }
    
    $chartData[] = array( 'label' => $answer->title, 'value' => $answer->votes );
    $totalResponses = $totalResponses + $answer->votes;
}
?>
<div class="mb-4">
	<table class="table table-bordered">
		<tbody>
        	<?php
            // Now render the data
            foreach ($question->answers as $i => $answer)
            {
                $pct = $totalResponses > 0 ? round($answer->votes * 100 / $totalResponses, 2) : 0;
                ?>
                <tr>
                	<td width="50%" scope="row">
                		<?php echo wp_kses_post( $answer->title );?>
                	</td>
                	<td width="5%">
                		<?php echo $pct;?>%
                	</td>
                	<td width="45%">
                		<div class="progress">
                    		<div class="progress-bar"
                    			data-ng-color="<?php echo $i;?>" 
                    			role="progressbar" 
                    			style="width: <?php echo $pct;?>%" 
                    			aria-valuenow="<?php echo $pct;?>" 
                    			aria-valuemin="0" 
                    			aria-valuemax="100"><?php echo $pct.'%';?></div>
                		</div>
                	</td>
                </tr>
            	<?php 
            }
            ?>
    	</tbody>
    </table>
</div>
<div class="row">
	<div class="col-md-5">
		<canvas id="choice-pie-chart-<?php echo (int) $question->id;?>" 
				data-title="<?php echo esc_attr__( 'Response Statistics', 'ngsurvey' );?>"
				data-yaxis-label="<?php echo esc_attr__( 'Responses', 'ngsurvey' );?>"
				data-data="#chart-data-<?php echo $question->id;?>"
				data-chart-type="doughnut"
				data-legend="1"></canvas>
	</div>
	<div class="col-md-7">
		<canvas id="choice-bar-chart-<?php echo (int) $question->id;?>" 
				data-title="<?php echo esc_attr__( 'Response Statistics', 'ngsurvey' );?>"
				data-yaxis-label="<?php echo esc_attr__( 'Responses', 'ngsurvey' );?>"
				data-data="#chart-data-<?php echo (int) $question->id;?>"
				data-chart-type="bar"
				data-legend="0"></canvas>
	</div>
	
	<div id="chart-data-<?php echo (int) $question->id;?>" style="display: none;"><?php echo esc_html( json_encode( $chartData ) );?></div>
</div>	

