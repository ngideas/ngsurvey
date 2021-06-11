<?php
/**
 * The template for displaying Textbox question type on front-end.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/public/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$answer     = '';
foreach ( $data->responses as $response ) {
    if( $response['answer_id'] != 1 && !empty( $response[ 'answer_data' ] ) ) {
        $answer = $response[ 'answer_data' ];
        break;
    }
}
?>
<div class="my-3">
	<?php echo wp_kses_post( $answer );?>
</div>