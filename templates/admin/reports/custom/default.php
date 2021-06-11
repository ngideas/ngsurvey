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
<table class="table">
	<thead>
		<tr>
			<th width="2%" nowrap="nowrap"><?php echo esc_html__( 'Response ID', 'ngsurvey' );?></th>
			<th><?php echo esc_html__( 'Comments', 'ngsurvey' );?></th>
			<th width="25%"><?php echo esc_html__( 'User Information', 'ngsurvey' );?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $data->custom_answers as $custom_answer ):?>
		<tr>
			<td><?php echo $custom_answer->response_id;?></td>
			<td><?php echo wp_kses_post( $custom_answer->answer_data );?></td>
			<td>
				<small><?php echo implode( ' | ', array(esc_html( $custom_answer->platform_name ), esc_html( $custom_answer->browser_name ), esc_html( $custom_answer->country_name )) );?></small>
				<div><small><?php echo esc_html( $custom_answer->created_date );?></small></div>
			</td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
