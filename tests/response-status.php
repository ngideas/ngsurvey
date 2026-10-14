<?php
/**
 * Run against a disposable WordPress installation using wp eval-file.
 * Run twice: once normally and once with NGSURVEY_STATUS_MODE=public.
 * Admin and public models use the same class name and need separate processes.
 * Test fixtures are rolled back after the checks.
 */
if ( ! defined( 'ABSPATH' ) || ! defined( 'NGSURVEY_PATH' ) ) {
    exit( "Run with wp eval-file after activating NgSurvey.\n" );
}

$public = getenv( 'NGSURVEY_STATUS_MODE' ) === 'public';
require_once NGSURVEY_PATH . ( $public ? 'public' : 'admin' ) . '/models/class-model-responses.php';
if ( ! $public ) {
    require_once NGSURVEY_PATH . 'admin/models/class-model-reports.php';
    require_once NGSURVEY_PATH . 'admin/controllers/class-controller-reports.php';
}

global $wpdb;
$checks = array();
$check = static function ( $condition, $label ) use ( &$checks ) {
    if ( ! $condition ) {
        throw new RuntimeException( $label );
    }
    $checks[] = $label;
};
$original_user = get_current_user_id();
$wpdb->query( 'START TRANSACTION' );
try {
    $admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
    $check( count( $admins ) === 1, 'Administrator available for report rendering.' );
    wp_set_current_user( $admins[0]->ID );
    $sid = wp_insert_post( array( 'post_type' => NGSURVEY_CPT_NAME, 'post_status' => 'publish', 'post_title' => 'Response status regression' ) );
    $zero = '0000-00-00 00:00:00';
    $start = '2026-10-14 12:00:00';
    $fixtures = array(
        'same_second' => array( $start, $start, true ),
        'later' => array( $start, '2026-10-14 12:00:01', true ),
        'pending' => array( $start, $zero, false ),
        'zero_defaults' => array( $zero, $zero, false ),
    );
    $ids = array();
    foreach ( $fixtures as $name => $fixture ) {
        $inserted = $wpdb->insert( $wpdb->prefix . 'ngs_responses', array(
            'survey_id' => $sid,
            'created_by' => $admins[0]->ID,
            'created_date' => $fixture[0],
            'created_date_gmt' => $fixture[0],
            'finished_date' => $fixture[1],
            'finished_date_gmt' => $fixture[1],
        ) );
        $check( $inserted !== false, $name . ' fixture inserted.' );
        $ids[ $name ] = $wpdb->insert_id;
    }
    $responses = new NgSurvey_Model_Responses();
    if ( $public ) {
        foreach ( $fixtures as $name => $fixture ) {
            $check( $responses->is_response_finished( $ids[ $name ] ) === $fixture[2], $name . ' finalization status is correct.' );
        }
    } else {
        $reports = new NgSurvey_Model_Reports();
        $counts = $reports->get_pending_and_completed_count( $sid );
        $check( (int) $counts[0]['value'] === 2, 'Same-second and later submissions are completed.' );
        $check( (int) $counts[1]['value'] === 2, 'Zero completion timestamps remain pending.' );
        $check( array_sum( array_column( $counts, 'value' ) ) === 4, 'Dashboard counts include every response.' );
        $latest = $reports->get_latest_responses( $sid, 0, 10 );
        $listed = $responses->get_list( $sid )->responses;
        foreach ( array( 'latest' => $latest, 'all' => $listed ) as $list_name => $items ) {
            $statuses = array_column( $items, 'finished', 'id' );
            foreach ( $fixtures as $name => $fixture ) {
                $check( (bool) $statuses[ $ids[ $name ] ] === $fixture[2], $list_name . ' list: ' . $name . ' status is correct.' );
            }
        }
        if ( defined( 'NGSURVEY_EXTENSIONS_DIR' ) ) {
            require_once NGSURVEY_EXTENSIONS_DIR . 'models/class-model-csv.php';
            $pid = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ngs_pages WHERE survey_id = %d LIMIT 1", $sid ) );
            $wpdb->insert( $wpdb->prefix . 'ngs_questions', array( 'title' => 'Status fixture', 'qtype' => 'textbox', 'params' => '{}' ) );
            $qid = $wpdb->insert_id;
            $wpdb->insert( $wpdb->prefix . 'ngs_pages_questions_map', array( 'page_id' => $pid, 'question_id' => $qid, 'sort_order' => 1 ) );
            foreach ( $ids as $rid ) {
                $wpdb->insert( $wpdb->prefix . 'ngs_response_details', array( 'response_id' => $rid, 'page_id' => $pid, 'question_id' => $qid, 'answer_data' => 'Answer' ) );
            }
            $csv = ( new NgSurvey_Extensions_Model_Csv() )->get_csv_report( $sid, null, null, 0, 0 );
            $statuses = array_column( array_slice( $csv, 1 ), 4, 0 );
            foreach ( $fixtures as $name => $fixture ) {
                $check( isset( $statuses[ $ids[ $name ] ] ) && (bool) $statuses[ $ids[ $name ] ] === $fixture[2], 'CSV export: ' . $name . ' status is correct.' );
            }
        }
        $_REQUEST = array( 'post' => $sid, '_wpnonce' => wp_create_nonce( 'view_reports_nonce' ) );
        $controller = new NgSurvey_Controller_Reports();
        ob_start();
        $controller->display();
        $html = ob_get_clean();
        foreach ( array( 'Total Responses' => 4, 'Completed' => 2, 'Pending' => 2 ) as $label => $value ) {
            $pattern = '/<td>' . preg_quote( esc_html__( $label, 'ngsurvey' ), '/' ) . '<\/td>\s*<td>' . $value . '<\/td>/';
            $check( preg_match( $pattern, $html ) === 1, 'Rendered dashboard ' . $label . ' is ' . $value . '.' );
        }
    }
    $check( $wpdb->last_error === '', 'No database errors.' );
    echo json_encode( array( 'wordpress' => get_bloginfo( 'version' ), 'mode' => $public ? 'public' : 'admin', 'passed' => count( $checks ), 'checks' => $checks ), JSON_PRETTY_PRINT ) . "\n";
} finally {
    $wpdb->query( 'ROLLBACK' );
    wp_set_current_user( $original_user );
}
