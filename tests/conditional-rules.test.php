<?php
/**
 * Dependency-free regression checks: php tests/conditional-rules.test.php
 *
 * Uses WordPress boundary doubles; live WordPress tests verify database matching.
 */
define( 'ABSPATH', __DIR__ );
error_reporting( E_ALL );
set_error_handler( function ( $severity, $message, $file, $line ) {
    throw new ErrorException( $message, 0, $severity, $file, $line );
} );

function check( $condition, $message ) {
    if( !$condition ) {
        throw new RuntimeException( $message );
    }
}

class NgSurvey_Test_Json_Response extends RuntimeException {}
class NgSurvey_Test_Invalid_Rule extends RuntimeException {}

function sanitize_text_field( $value ) { return $value; }
function __( $value, $domain ) { return $value; }
function sanitize_key( $value ) { return $value; }
function wp_unslash( $value ) { return stripslashes( $value ); }
function wp_kses_post( $value ) { return $value; }
function esc_sql( $value ) { return str_replace( "'", "''", $value ); }
function wp_send_json_success( $response ) { throw new NgSurvey_Test_Json_Response(); }

class NgSurvey_Test_Model {
    public $saved_rule;
    public function update( $survey_id, $page_id, $rule_id, $title, $rule ) {
        $this->saved_rule = $rule;
        return array();
    }
    public function get_rules( $survey_id, $page_id ) { return array(); }
    public function get_pages( $survey_id ) { return array(); }
}

class NgSurvey_Test_Template {
    public function set_template_data( $data ) { return $this; }
    public function get_template_part( $part ) {}
}

class NgSurvey_Controller {
    public $template;
    public $model;
    public function __construct( $config = array() ) {
        $this->model = new NgSurvey_Test_Model();
        $this->template = new NgSurvey_Test_Template();
    }
    protected function get_model( $name ) { return $this->model; }
    protected function authorise( $survey_id ) {}
    protected function raise_error( $code = '001', $message = 'Unauthorised access' ) {
        throw new NgSurvey_Test_Invalid_Rule( $message );
    }
}

class NgSurvey_Test_Database {
    public $prefix = 'wp_';
    public $queries = array();
    public function prepare( $query, $response_id ) {
        return str_replace( '%d', (int) $response_id, $query );
    }
    public function get_var( $query ) {
        $this->queries[] = $query;
        return 1;
    }
}

require dirname( __DIR__ ) . '/admin/controllers/class-controller-rules.php';
require dirname( __DIR__ ) . '/includes/init/class-ngsurvey-rulesparser.php';

function rule_group( $value ) {
    return array(
        'condition' => 'AND',
        'rules' => array( array(
            'id' => 23, 'type' => 'string', 'operator' => 'equal', 'value' => $value,
        ) ),
    );
}

function save_rule( $content ) {
    $_POST = array( 'ngform' => array(
        'sid' => 1, 'pid' => 2, 'rid' => 3, 'title' => 'Unicode condition',
        'rule_content' => is_string( $content ) ? addslashes( $content ) : $content,
        'action' => 'show_question', 'action_page' => 0, 'action_question' => 24,
    ) );
    $controller = new NgSurvey_Controller_Rules();
    try {
        $controller->save();
    } catch ( NgSurvey_Test_Json_Response $response ) {
        return $controller->model->saved_rule['rule_content'];
    }
    throw new RuntimeException( 'Save did not return a success response.' );
}

$wpdb = new NgSurvey_Test_Database();
$parser = new NgSurvey_Rules_Parser();
$values = array( 'café', 'नमस्ते', '東京', '😃', 'café "quoted" \\ path' );
foreach( $values as $value ) {
    $group = rule_group( $value );
    foreach( array( 0, JSON_UNESCAPED_UNICODE ) as $encoding ) {
        $content = json_encode( $group, $encoding );
        $saved = save_rule( $content );
        check( json_decode( $saved, true ) === $group, 'Saving must preserve Unicode and JSON string escapes.' );
        foreach( array( $content, $saved ) as $rules ) {
            check( $parser->validate_rules( $rules, 42 ), 'A valid rule must reach the database.' );
            $query = end( $wpdb->queries );
            check( strpos( $query, "t1.answer_data ='" . esc_sql( $value ) . "'" ) !== false,
                'The database must receive the original UTF-8 value.' );
        }
    }
}

$nested = rule_group( 'café' );
$nested['rules'][] = array(
    'condition' => 'OR',
    'rules' => array( rule_group( '東京' )['rules'][0], rule_group( 'नमस्ते' )['rules'][0] ),
);
check( $parser->validate_rules( json_encode( $nested ), 42 ), 'Nested groups must remain valid.' );
$query = end( $wpdb->queries );
check( strpos( $query, "(t1.question_id = 23 AND t1.answer_data ='café') AND ((t1.question_id = 23 AND t1.answer_data ='東京') OR (t1.question_id = 23 AND t1.answer_data ='नमस्ते'))" ) !== false,
    'Nested conditions must retain their AND/OR grouping.' );

$invalid_json = array( '', '{', 'null', 'true', '123', '"text"', '{}', '[]', '{"condition":"AND","rules":false}', "\xFF", array() );
foreach( $invalid_json as $content ) {
    try {
        save_rule( $content );
        throw new RuntimeException( 'Invalid content was saved.' );
    } catch ( NgSurvey_Test_Invalid_Rule $expected ) {}
}

$invalid_groups = array(
    array( 'condition' => 'AND', 'rules' => array( null ) ),
    array( 'condition' => 'AND', 'rules' => array( array() ) ),
    array( 'condition' => 'AND', 'rules' => array( array( 'condition' => 'OR', 'rules' => false ) ) ),
);
foreach( array( 'operator', 'type', 'id', 'value' ) as $field ) {
    $rule = rule_group( 'café' );
    $rule['rules'][0][$field] = array();
    $invalid_groups[] = $rule;
}
$rule = rule_group( 'café' );
$rule['rules'][0]['operator'] = 'in';
$rule['rules'][0]['value'] = array( array() );
$invalid_groups[] = $rule;
$queries_before = count( $wpdb->queries );
foreach( array_merge( $invalid_json, array_map( 'json_encode', $invalid_groups ), array( null ) ) as $content ) {
    check( $parser->validate_rules( $content, 42 ) === false, 'Malformed rules must fail closed.' );
}
check( count( $wpdb->queries ) === $queries_before, 'Malformed rules must not issue SQL.' );

echo "PASS: Unicode save/query roundtrips, nested groups, and malformed JSON/shape checks (E_ALL).\n";
