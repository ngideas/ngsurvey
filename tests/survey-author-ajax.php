<?php
/**
 * Run on a disposable WordPress site with NgSurvey and NgSurvey Extensions active:
 * wp eval-file /path/to/ngsurvey/tests/survey-author-ajax.php --path=/path/to/wordpress
 * Temporary users, surveys, and responses are rolled back after the checks.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NgSurvey_CPT' ) || ! get_role( 'survey_author' ) ) {
	exit( "Run with wp eval-file after activating NgSurvey and NgSurvey Extensions.\n" );
}
if ( ! defined( 'DOING_AJAX' ) ) {
	define( 'DOING_AJAX', true );
}
class NgSurvey_Author_Test_Json_Exit extends RuntimeException {}
$die_handler = static function () {
	return static function () { throw new NgSurvey_Author_Test_Json_Exit(); };
};
add_filter( 'wp_die_ajax_handler', $die_handler );
$notices = array();
$record_notice = static function ( $function, $message ) use ( &$notices ) { $notices[] = $function . ': ' . $message; };
add_action( 'doing_it_wrong_run', $record_notice, 10, 2 );
$checks = array();
$check = static function ( $condition, $label ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $label );
	}
	$checks[] = $label;
};
$dispatcher = new NgSurvey_CPT( NGSURVEY_CPT_NAME, NGSURVEY_MAIN_NAME, NGSURVEY_VERSION );
$permission = new ReflectionMethod( NgSurvey_CPT::class, 'can_handle_author_ajax_task' );
$permission->setAccessible( true );
$allowed = static function ( $task, $form, $extra = array() ) use ( $dispatcher, $permission ) {
	$_POST = array_merge( array( 'ngform' => $form ), $extra );
	return $permission->invoke( $dispatcher, $task );
};
$dispatch = static function ( $task, $form, $extra = array() ) use ( $dispatcher ) {
	global $wpdb;
	$_POST = array_merge( array( 'ngform' => $form, 'task' => $task, '_ajax_nonce' => wp_create_nonce( 'ngsa_nounce' ) ), $extra );
	$_REQUEST = $_POST;
	ob_start();
	try {
		$dispatcher->handle_ajax_task();
	} catch ( NgSurvey_Author_Test_Json_Exit $exit ) {
		// wp_send_json terminates requests; capture it without stopping the suite.
	}
	$result = json_decode( ob_get_clean(), true );
	if ( $wpdb->last_error ) {
		throw new RuntimeException( $task . ': ' . $wpdb->last_error );
	}
	return $result;
};

global $wpdb;
$original_user = get_current_user_id();
$wpdb->query( 'START TRANSACTION' );
try {
	if ( ! $wpdb->get_var( "SELECT country_code FROM {$wpdb->prefix}ngs_countries WHERE country_code = 'IN' LIMIT 1" ) ) {
		$wpdb->insert( "{$wpdb->prefix}ngs_countries", array( 'country_code' => 'IN', 'country_name' => 'India', 'language' => '*' ) );
	}
	$author = wp_insert_user( array( 'user_login' => 'ngs_author_' . uniqid(), 'user_pass' => wp_generate_password(), 'role' => 'survey_author' ) );
	$other = wp_insert_user( array( 'user_login' => 'ngs_other_' . uniqid(), 'user_pass' => wp_generate_password(), 'role' => 'survey_author' ) );
	$subscriber = wp_insert_user( array( 'user_login' => 'ngs_reader_' . uniqid(), 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ) );
	$check( ! is_wp_error( $author ) && ! is_wp_error( $other ) && ! is_wp_error( $subscriber ), 'Temporary users created.' );
	wp_set_current_user( $author );
	$fixtures = array();
	foreach ( array( $author, $other, $subscriber ) as $owner ) {
		$sid = wp_insert_post( array( 'post_type' => NGSURVEY_CPT_NAME, 'post_status' => 'publish', 'post_author' => $owner, 'post_title' => 'Author dispatcher regression' ) );
		$pid = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ngs_pages WHERE survey_id = %d", $sid ) );
		$wpdb->insert( "{$wpdb->prefix}ngs_questions", array( 'title' => 'Original question', 'description' => '', 'qtype' => 'textbox', 'params' => '{}' ) );
		$qid = (int) $wpdb->insert_id;
		$wpdb->insert( "{$wpdb->prefix}ngs_pages_questions_map", array( 'page_id' => $pid, 'question_id' => $qid, 'sort_order' => 1 ) );
		$wpdb->insert( "{$wpdb->prefix}ngs_rules", array( 'survey_id' => $sid, 'page_id' => $pid, 'title' => 'Original rule', 'sort_order' => 1, 'rule_content' => '{}', 'rule_actions' => '{}' ) );
		$rule = (int) $wpdb->insert_id;
		$wpdb->insert( "{$wpdb->prefix}ngs_responses", array( 'survey_id' => $sid, 'created_by' => 0 ) );
		$response = (int) $wpdb->insert_id;
		$wpdb->insert( "{$wpdb->prefix}ngs_response_details", array( 'response_id' => $response, 'page_id' => $pid, 'question_id' => $qid, 'answer_data' => 'Test response' ) );
		$wpdb->insert( "{$wpdb->prefix}ngs_tracking", array( 'post_id' => $response, 'post_type' => 2, 'country' => 'IN', 'state' => 'Test state', 'city' => 'Test city', 'platform_name' => 'Test platform', 'browser_name' => 'Test browser', 'device_type' => 'Test device' ) );
		$wpdb->insert( "{$wpdb->prefix}ngs_answers", array( 'question_id' => $qid, 'answer_type' => 'x', 'title' => 'Original answer', 'sort_order' => 1 ) );
		$answer = (int) $wpdb->insert_id;
		$fixtures[ $owner ] = compact( 'sid', 'pid', 'qid', 'rule', 'response', 'answer' );
	}
	$own = $fixtures[ $author ];
	$foreign = $fixtures[ $other ];
	$conditions = array( 'condition' => 'AND', 'rules' => array( array( 'id' => $own['qid'], 'type' => 'string', 'field' => 'textbox', 'operator' => 'is_empty' ) ) );
	$form = array( 'sid' => $own['sid'], 'pid' => $own['pid'], 'qid' => $own['qid'], 'rid' => $own['rule'], 'nid' => $own['pid'], 'qtype' => 'textbox', 'rule_content' => wp_slash( wp_json_encode( $conditions ) ) );
	$check( ! current_user_can( 'manage_options' ), 'The survey author has no administrator settings capability.' );
	foreach ( array( 'questions.create', 'questions.save', 'questions.copy', 'questions.move', 'questions.remove', 'questions.custom', 'pages.display', 'pages.create', 'pages.update', 'pages.remove', 'rules.display', 'rules.create', 'rules.save', 'rules.remove', 'responses.display', 'reports.get_custom_answers', 'consolidated.display' ) as $task ) {
		$check( $allowed( $task, $form ), "$task allows the author's survey." );
		$check( ! $allowed( $task, array_merge( $form, array( 'sid' => $foreign['sid'] ) ) ), "$task rejects another author's survey." );
	}
	foreach ( array( 'questions' => $own['qid'], 'pages' => $own['pid'], 'rules' => $own['rule'] ) as $type => $id ) {
		$check( $allowed( $type . '.sort', $form, array( 'ordering' => array( $id ) ) ), "$type.sort accepts owned resources." );
		$foreign_id = $type === 'questions' ? $foreign['qid'] : ( $type === 'pages' ? $foreign['pid'] : $foreign['rule'] );
		$check( ! $allowed( $type . '.sort', $form, array( 'ordering' => array( $foreign_id ) ) ), "$type.sort rejects foreign resources." );
	}
	foreach ( array( 'questions.create', 'questions.save', 'pages.update', 'pages.remove', 'rules.create' ) as $task ) {
		$check( ! $allowed( $task, array_merge( $form, array( 'pid' => $foreign['pid'] ) ) ), "$task rejects a foreign page with an owned sid." );
	}
	foreach ( array( 'questions.save', 'questions.copy', 'questions.move', 'questions.remove', 'questions.custom', 'reports.get_custom_answers' ) as $task ) {
		$check( ! $allowed( $task, array_merge( $form, array( 'qid' => $foreign['qid'] ) ) ), "$task rejects a foreign question with an owned sid." );
	}
	$check( ! $allowed( 'questions.move', array_merge( $form, array( 'nid' => $foreign['pid'] ) ) ), 'Question moves cannot target a foreign page.' );
	foreach ( array( 'answer_id', 'column_id' ) as $field ) {
		$check( $allowed( 'questions.save', array_merge( $form, array( $field => array( $own['answer'], 0, '' ) ) ) ), "Question save accepts owned/new $field values." );
		$check( ! $allowed( 'questions.save', array_merge( $form, array( $field => array( $foreign['answer'] ) ) ) ), "Question save rejects foreign $field values." );
	}
	foreach ( array( 'rules.save', 'rules.remove' ) as $task ) {
		$check( ! $allowed( $task, array_merge( $form, array( 'rid' => $foreign['rule'] ) ) ), "$task rejects a foreign rule." );
	}
	$check( ! $allowed( 'rules.save', array_merge( $form, array( 'action_page' => $foreign['pid'] ) ) ), 'Rule actions reject foreign pages.' );
	$check( ! $allowed( 'rules.save', array_merge( $form, array( 'action_question' => $foreign['qid'] ) ) ), 'Rule actions reject foreign questions.' );
	$nested = array( 'condition' => 'OR', 'rules' => array( $conditions ) );
	$check( $allowed( 'rules.save', array_merge( $form, array( 'rule_content' => wp_slash( wp_json_encode( $nested ) ) ) ) ), 'Nested rule conditions accept owned questions.' );
	$nested['rules'][0]['rules'][0]['id'] = $foreign['qid'];
	$result = $dispatch( 'rules.save', array_merge( $form, array( 'rule_content' => wp_slash( wp_json_encode( $nested ) ) ) ) );
	$check( isset( $result['success'] ) && ! $result['success'], 'Nested rule conditions cannot probe a foreign question.' );
	$check( ! $allowed( 'rules.save', array_merge( $form, array( 'rule_content' => '{invalid' ) ) ), 'Malformed rule JSON is denied without warnings.' );
	$nested['rules'][0]['rules'] = array( 'invalid leaf' );
	$check( ! $allowed( 'rules.save', array_merge( $form, array( 'rule_content' => wp_slash( wp_json_encode( $nested ) ) ) ) ), 'Malformed nested rules are denied without warnings.' );
	$normalized_attack = '{"condition":"AND","rules":[{"id":' . $own['qid'] . ',"<script>id</script>":' . $foreign['qid'] . ',"type":"string","operator":"is_empty"}]}';
	$check( ! $allowed( 'rules.save', array_merge( $form, array( 'rule_content' => wp_slash( $normalized_attack ) ) ) ), 'Rule ownership checks the same sanitized JSON keys that the controller stores.' );
	$check( $allowed( 'response.display', array_merge( $form, array( 'rid' => $own['response'] ) ) ), 'Authors can view their own response.' );
	$check( ! $allowed( 'response.display', array_merge( $form, array( 'rid' => $foreign['response'] ) ) ), 'Authors cannot view foreign responses.' );
	$check( $allowed( 'responses.delete', $form, array( 'rid' => array( $own['response'] ) ) ), 'Authors can delete their own responses.' );
	$check( ! $allowed( 'responses.delete', $form, array( 'rid' => array( $own['response'], $foreign['response'] ) ) ), 'Bulk response deletion rejects mixed ownership.' );
	foreach ( array( 'settings.save', 'extensions.activate', 'questions.get_model', 'questions.__construct', 'responses.custom' ) as $task ) {
		$result = $dispatch( $task, $form );
		$check( isset( $result['success'] ) && ! $result['success'], "$task is denied by the dispatcher." );
	}
	$result = $dispatch( 'questions.create', $form );
	$check( ! empty( $result['success'] ), 'A nonadmin author creates a question through the real AJAX dispatcher.' );
	$result = $dispatch( 'questions.save', array_merge( $form, array( 'title' => 'Saved by author', 'description' => '', 'options' => array() ) ) );
	$check( ! empty( $result['success'] ) && $wpdb->get_var( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}ngs_questions WHERE id = %d", $own['qid'] ) ) === 'Saved by author', 'A nonadmin author saves a question and persists its title.' );
	$result = $dispatch( 'pages.update', $form, array( 'title' => 'Renamed by author' ) );
	$check( ! empty( $result['success'] ) && $wpdb->get_var( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}ngs_pages WHERE id = %d", $own['pid'] ) ) === 'Renamed by author', 'A nonadmin author renames a page through AJAX.' );
	$result = $dispatch( 'questions.save', array_merge( $form, array( 'qid' => $foreign['qid'], 'title' => 'Forbidden change' ) ) );
	$check( isset( $result['success'] ) && ! $result['success'] && $wpdb->get_var( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}ngs_questions WHERE id = %d", $foreign['qid'] ) ) === 'Original question', 'A forged question save is rejected without modifying the foreign question.' );
	$result = $dispatch( 'rules.save', array_merge( $form, array(
		'title' => 'Saved rule', 'rule_content' => wp_slash( wp_json_encode( array( 'condition' => 'AND', 'rules' => array( array( 'id' => $own['qid'], 'field' => 'textbox', 'type' => 'string', 'operator' => 'equal', 'value' => 'café' ) ) ) ) ),
		'action' => 'show_question', 'action_page' => '', 'action_question' => $own['qid'],
	) ) );
	$check( ! empty( $result['success'] ), 'A nonadmin author saves conditional rules through AJAX.' );
	$finalize_form = $_POST['ngform'];
	$finalize_form['action'] = 'finalize';
	$finalize_form['action_question'] = '';
	$result = $dispatch( 'rules.save', $finalize_form );
	$check( ! empty( $result['success'] ), 'Finalize rules accept both unused target selectors left blank.' );
	$result = $dispatch( 'responses.display', $form, array( 'draw' => 1, 'start' => 0, 'length' => 10, 'order' => array(), 'search' => array( 'value' => '' ) ) );
	$check( ! empty( $result['success'] ) && count( $result['data'] ) === 1, 'A nonadmin author lists only their survey responses through AJAX.' );
	foreach ( array( 'locations.display', 'platforms.display', 'browsers.display', 'devices.display' ) as $task ) {
		$pagination = array( 'draw' => 1, 'start' => 0, 'length' => 10, 'order' => array(), 'search' => array( 'value' => '' ) );
		$result = $dispatch( $task, $form, $pagination );
		$check( ! empty( $result['success'] ) && count( $result['data'] ) === 1 && (int) $result['data'][0]['responses'] === 1, "$task returns the author's own aggregate response data: " . wp_json_encode( $result ) );
		$result = $dispatch( $task, array_merge( $form, array( 'sid' => $foreign['sid'] ) ), $pagination );
		$check( isset( $result['success'] ) && ! $result['success'], "$task denies another author's report tab." );
	}
	$export = array( 'operation' => 'extensions.csv-download.get_csv_data', 'start' => 0, 'length' => 10, 'order' => array(), 'search' => array( 'value' => '' ) );
	$result = $dispatch( 'ajax.execute', $form, $export );
	$check( ! empty( $result['success'] ) && is_string( $result['data'] ) && str_contains( $result['data'], 'Response ID' ), 'A nonadmin author exports their own response CSV.' );
	$result = $dispatch( 'ajax.execute', array_merge( $form, array( 'sid' => $foreign['sid'] ) ), $export );
	$check( isset( $result['success'] ) && ! $result['success'], 'CSV export denies another author\'s survey.' );
	$result = $dispatch( 'ajax.execute', $form, array( 'operation' => 'extensions.email-settings.display' ) );
	$check( isset( $result['success'] ) && ! $result['success'], 'Generic extension operations remain denied.' );
	$result = $dispatch( 'responses.delete', $form, array( 'rid' => array( $own['response'], $foreign['response'] ) ) );
	$check( isset( $result['success'] ) && ! $result['success'], 'The real dispatcher rejects response deletion with mixed ownership.' );
	$result = $dispatch( 'responses.delete', $form, array( 'rid' => array( $own['response'] ) ) );
	$check( ! empty( $result['success'] ), 'A nonadmin author deletes their own response through AJAX.' );
	$check( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ngs_responses WHERE id = %d", $own['response'] ) ) &&
		! $wpdb->get_var( $wpdb->prepare( "SELECT response_id FROM {$wpdb->prefix}ngs_response_details WHERE response_id = %d", $own['response'] ) ) &&
		! $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}ngs_tracking WHERE post_id = %d AND post_type = 2", $own['response'] ) ), 'Response deletion removes the owned response, answers, and tracking rows.' );
	$check( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ngs_responses WHERE id = %d", $foreign['response'] ) ) &&
		$wpdb->get_var( $wpdb->prepare( "SELECT response_id FROM {$wpdb->prefix}ngs_response_details WHERE response_id = %d", $foreign['response'] ) ) &&
		$wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}ngs_tracking WHERE post_id = %d AND post_type = 2", $foreign['response'] ) ), 'Response deletion preserves the foreign response, answers, and tracking rows.' );
	wp_get_current_user()->add_cap( 'upload_files', false );
	$check( ! $allowed( 'questions.custom', $form ), 'Question uploads require the upload_files capability.' );
	wp_get_current_user()->remove_cap( 'upload_files' );
	$administrators = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	wp_set_current_user( (int) $administrators[0] );
	$result = $dispatch( 'pages.update', array( 'sid' => $foreign['sid'], 'pid' => $foreign['pid'] ), array( 'title' => 'Renamed by administrator' ) );
	$check( ! empty( $result['success'] ), 'Administrators retain access to other authors\' survey pages.' );
	wp_set_current_user( $author );
	// Even a mapping in an owned page must not grant mutation of a shared foreign question.
	$wpdb->insert( "{$wpdb->prefix}ngs_pages_questions_map", array( 'page_id' => $own['pid'], 'question_id' => $foreign['qid'], 'sort_order' => 9 ) );
	$check( ! $allowed( 'questions.save', array_merge( $form, array( 'qid' => $foreign['qid'] ) ) ), 'Shared foreign questions cannot be modified through an owned page.' );
	wp_set_current_user( $subscriber );
	$check( ! $allowed( 'pages.create', array( 'sid' => $fixtures[ $subscriber ]['sid'] ) ), 'A subscriber cannot manage even a survey assigned to them.' );
	wp_set_current_user( 0 );
	$check( ! $allowed( 'pages.create', $form ), 'Anonymous users cannot use the author dispatch path.' );
	$check( empty( $notices ), 'No WordPress doing_it_wrong notices: ' . implode( '; ', $notices ) );
} finally {
	$wpdb->query( 'ROLLBACK' );
	wp_set_current_user( $original_user );
	remove_filter( 'wp_die_ajax_handler', $die_handler );
	remove_action( 'doing_it_wrong_run', $record_notice, 10 );
}
echo wp_json_encode( array( 'wordpress' => get_bloginfo( 'version' ), 'php' => PHP_VERSION, 'passed' => count( $checks ), 'checks' => $checks ), JSON_PRETTY_PRINT ) . "\n";
