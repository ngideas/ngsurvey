<?php
/**
 * Defines the conditional rules parser.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/init
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Utility class to parse the conditional rules and get the result
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey_Rules_Parser {
    private $count = 1;
    private $exists = 1;
    
    protected $operators = array (
        'equal'            => array ('accept_values' => true,  'apply_to' => ['string', 'integer', 'number', 'datetime']),
        'not_equal'        => array ('accept_values' => true,  'apply_to' => ['string', 'integer', 'number', 'datetime']),
        'in'               => array ('accept_values' => true,  'apply_to' => ['string', 'integer', 'number', 'datetime']),
        'not_in'           => array ('accept_values' => true,  'apply_to' => ['string', 'integer', 'number', 'datetime']),
        'less'             => array ('accept_values' => true,  'apply_to' => ['integer', 'number', 'datetime']),
        'less_or_equal'    => array ('accept_values' => true,  'apply_to' => ['integer', 'number', 'datetime']),
        'greater'          => array ('accept_values' => true,  'apply_to' => ['integer', 'number', 'datetime']),
        'greater_or_equal' => array ('accept_values' => true,  'apply_to' => ['integer', 'number', 'datetime']),
        'between'          => array ('accept_values' => true,  'apply_to' => ['integer', 'number', 'datetime']),
        'not_between'      => array ('accept_values' => true,  'apply_to' => ['integer', 'number', 'datetime']),
        'begins_with'      => array ('accept_values' => true,  'apply_to' => ['string']),
        'not_begins_with'  => array ('accept_values' => true,  'apply_to' => ['string']),
        'contains'         => array ('accept_values' => true,  'apply_to' => ['string']),
        'not_contains'     => array ('accept_values' => true,  'apply_to' => ['string']),
        'ends_with'        => array ('accept_values' => true,  'apply_to' => ['string']),
        'not_ends_with'    => array ('accept_values' => true,  'apply_to' => ['string']),
    	'is_empty'         => array ('accept_values' => false, 'negation' => true,	'apply_to' => ['string', 'integer', 'number', 'datetime']),
    	'is_not_empty'     => array ('accept_values' => false, 'negation' => false,	'apply_to' => ['string', 'integer', 'number', 'datetime']),
    	'is_null'          => array ('accept_values' => false, 'negation' => true,	'apply_to' => ['string', 'integer', 'number', 'datetime']),
    	'is_not_null'      => array ('accept_values' => false, 'negation' => false,	'apply_to' => ['string', 'integer', 'number', 'datetime'])
    );
    
    protected $operator_sql = array (
        'equal'            => array ('operator' => '='),
        'not_equal'        => array ('operator' => '!='),
        'in'               => array ('operator' => 'IN'),
        'not_in'           => array ('operator' => 'NOT IN'),
        'less'             => array ('operator' => '<'),
        'less_or_equal'    => array ('operator' => '<='),
        'greater'          => array ('operator' => '>'),
        'greater_or_equal' => array ('operator' => '>='),
        'between'          => array ('operator' => 'BETWEEN'),
        'not_between'      => array ('operator' => 'NOT BETWEEN'),
        'begins_with'      => array ('operator' => 'LIKE',     'prepend'  => '%'),
        'not_begins_with'  => array ('operator' => 'NOT LIKE', 'prepend'  => '%'),
        'contains'         => array ('operator' => 'LIKE',     'append'  => '%', 'prepend' => '%'),
        'not_contains'     => array ('operator' => 'NOT LIKE', 'append'  => '%', 'prepend' => '%'),
        'ends_with'        => array ('operator' => 'LIKE',     'append' => '%'),
        'not_ends_with'    => array ('operator' => 'NOT LIKE', 'append' => '%'),
        'is_empty'         => array ('operator' => '='),
        'is_not_empty'     => array ('operator' => '!='),
        'is_null'          => array ('operator' => 'NULL'),
        'is_not_null'      => array ('operator' => 'NOT NULL')
    );
    
    protected $needs_array = array(
        'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
    );

	public function validate_rules ( $rules_content, $response_id )	{
	    global $wpdb;

	    $conditions = json_decode( utf8_encode( $rules_content ), true);
	    $where = $this->parseGroup( $conditions );
	    
	    if( empty( $where ) ) {
	        return false;
	    }

	    $count = (int) $wpdb->get_var($wpdb->prepare(
	        "SELECT count(*) FROM {$wpdb->prefix}ngs_response_details t{$this->count} WHERE t{$this->count}.response_id = %d AND (" . $where . ')', 
	        $response_id
	    ));

	    return $count > 0 ? true : false;
	}
	
	private function parseGroup($rule) {
	    $parseResult = '';
	    $bool_operator = in_array($rule['condition'], array('AND', 'OR')) ? $rule['condition'] : 'AND';
	    $counter = 0;
	    
	    if( empty( $rule['rules'] ) ) {
	        return $parseResult;
	    }
	    
	    $total = count( $rule[ 'rules' ] );
	    foreach( $rule[ 'rules' ] as $r ) {
	        if( array_key_exists( 'condition', $r ) ) {
	            $parseResult = $parseResult . $this->parseGroup($r);
	        }  else {
	            $parseResult = $parseResult . '('. $this->parseRule($r) .')';
	            $total--;

	            if($counter < $total && !empty($parseResult)) {
	                $parseResult .= " ".$bool_operator." ";
	            }
	        }
	    }
	    
	    return $parseResult;
	}
	
	private function parseRule($rule)  {
	    
	    if( !isset($this->operators[$rule['operator']]) || !in_array( $rule['type'], $this->operators[$rule['operator']]['apply_to'] ) ) {
	        return '';
	    }
	    
	    $operator = $this->operator_sql[$rule['operator']]['operator'];

	    if( !$this->operators[$rule['operator']]['accept_values'] ) {
	        global $wpdb;
	        $not = $this->operators[$rule['operator']]['negation'] ? 'NOT' : '';
	        $parseResult = $not . " EXISTS (SELECT * FROM {$wpdb->prefix}ngs_response_details t{$this->exists} WHERE t{$this->exists}.question_id = " . (int) $rule['id'] . ")";
	        $this->exists++;
	    } else if( in_array( $operator, $this->needs_array ) ) {
	    	$parseResult = 't' . $this->count . '.question_id = ' . (int) $rule['id'] . ' AND ';
	        $conditions = array();
	        
	        foreach ($rule['value'] as $value) {

	            if( strpos($value, '_') !== false ) {
	                list( $answer, $column ) = explode( '_', $value, 2 );
	                $conditions[] = 't' . $this->count . '.answer_id = ' . (int) $answer . ' AND t' . $this->count . '.column_id = ' . (int) $column;
	            } else {
	                $conditions[] = 't' . $this->count . '.answer_id = ' . (int) $value;
	            }
	        }
	        $parseResult = $parseResult . '(' . implode(' ) OR ( ',  $conditions) . ')';
	    } else {
	    	$parseResult = 't' . $this->count . '.question_id = ' . (int) $rule['id'] . ' AND ';
	        switch ( $rule['type'] ) {
	            case 'integer':
	            case 'number':
	                
	                if( strpos( $rule['value'], '_' ) !== false ) {
	                    
	                    list( $answer, $column ) = explode( '_', $rule['value'], 2 );
	                    $parseResult = $parseResult . 't' . $this->count . '.answer_id = ' . (int) $answer . ' AND t' . $this->count . '.column_id = ' . (int) $column;
	                } else {
	                    $parseResult = $parseResult . 't' . $this->count . '.answer_id = ' . (int) $rule['value'];
	                }
	                break;
	                
	            case 'datetime':
	            case 'string':
	                
	                $prepend = isset($this->operator_sql[$rule['operator']]['prepend']) ? $this->operator_sql[$rule['operator']]['prepend'] : '';
	                $append = isset($this->operator_sql[$rule['operator']]['append']) ? $this->operator_sql[$rule['operator']]['append'] : '';
	                
	                $parseResult = $parseResult . 't' . $this->count . '.answer_data '. $operator .'\'' . $prepend . esc_sql( $rule['value'] ) . $append .'\'';
	                break;
	        }
	    }

	    return $parseResult;
	}
}
