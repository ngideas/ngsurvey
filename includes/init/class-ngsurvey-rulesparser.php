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

	    if( !is_string( $rules_content ) ) {
	        return false;
	    }
	    $conditions = json_decode( $rules_content, true );
	    if( json_last_error() !== JSON_ERROR_NONE || !is_array( $conditions ) ) {
	        return false;
	    }
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
	    if( !is_array( $rule ) || !isset( $rule['condition'], $rule['rules'] ) ||
	        !in_array( $rule['condition'], array( 'AND', 'OR' ), true ) ||
	        !is_array( $rule['rules'] ) || empty( $rule['rules'] ) ) {
	        return '';
	    }

	    $conditions = array();
	    foreach( $rule[ 'rules' ] as $r ) {
	        if( !is_array( $r ) ) {
	            return '';
	        }
	        if( array_key_exists( 'condition', $r ) ) {
	            $condition = $this->parseGroup( $r );
	        }  else {
	            $condition = $this->parseRule( $r );
	        }
	        if( $condition === '' ) {
	            return '';
	        }
	        $conditions[] = '(' . $condition . ')';
	    }

	    return implode( ' ' . $rule['condition'] . ' ', $conditions );
	}
	
	private function parseRule($rule)  {
	    
	    if( !isset( $rule['operator'], $rule['type'], $rule['id'] ) ||
	        !is_string( $rule['operator'] ) || !is_string( $rule['type'] ) || !is_scalar( $rule['id'] ) ||
	        !isset( $this->operators[$rule['operator']] ) ||
	        !in_array( $rule['type'], $this->operators[$rule['operator']]['apply_to'], true ) ) {
	        return '';
	    }
	    
	    $operator = $this->operator_sql[$rule['operator']]['operator'];
	    if( $this->operators[$rule['operator']]['accept_values'] ) {
	        if( !isset( $rule['value'] ) ) {
	            return '';
	        }
	        if( in_array( $operator, $this->needs_array, true ) ) {
	            if( !is_array( $rule['value'] ) || empty( $rule['value'] ) ) {
	                return '';
	            }
	            foreach( $rule['value'] as $value ) {
	                if( !is_scalar( $value ) ) {
	                    return '';
	                }
	            }
	        } else if( !is_scalar( $rule['value'] ) ) {
	            return '';
	        }
	    }

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
