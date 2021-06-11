<?php
/**
 * Defines the registry object for use with params json arrays.
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
 * NgSurvey Registry class to load json string and provide accessor methods.
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/init
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey_Registry implements \ArrayAccess, \Countable, \Iterator {

	private $data = [];

	/**
	 * Create an Object
	 *
	 * @param mixed $source Object, array, JSON string, stdClass
	 * @throws Exception if parsing fails
	 */
	public function __construct($source = null)
	{
		if ($source === null) return;

		if ($source instanceof Object) {
			// clone data
			$this->data = json_decode(json_encode($source->data), true);
		} else if (is_string($source)) {
			// handle as JSON
			$this->data = json_decode($source, true);
		} else if (is_array($source) || $source instanceof stdClass) {
			$this->data = (array) $source;
		} else {
			throw new Exception('Invalid argument in Object constructor.');
		}
	}

	/**
	 * Get a value, or default if not set
	 *
	 * @param string $name property name
	 * @param mixed  $def default value
	 * @return mixed
	 */
	public function get($name, $def = null)
	{
		if ($this->__isset($name)) {
			return $this->data[$name];
		} else {
			return $def;
		}
	}

	/**
	 * Magic method. If property is undefined, will return null.
	 *
	 * @param string $name property name
	 * @return mixed
	 */
	public function __get($name)
	{
		if ($this->__isset($name)) {
			return $this->data[$name];
		} else {
			return null;
		}
	}

	/**
	 * Magic method. Assign a value to a property.
	 *
	 * @param string $name property name
	 * @param mixed  $value value to assign
	 */
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * Magic method. Check if a property exists.
	 *
	 * @param string $name property name
	 * @return bool true if exists
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}

	/**
	 * Magic method. Unset a property
	 *
	 * @param string $name property name to unset
	 */
	public function __unset($name)
	{
		unset($this->data[$name]);
	}

	/**
	 * @see __isset()
	 */
	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	/**
	 * @see __get()
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * @see __set()
	 */
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}

	/**
	 * @see __unset()
	 */
	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}

	/**
	 * Magic method. Get property count.
	 *
	 * @return int number of properties.
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Magic method.
	 *
	 * @return string string representation
	 */
	public function __toString()
	{
		return json_encode($this->data);
	}

	/**
	 * Convert self to JSON
	 *
	 * @param bool $pretty use pretty formatting
	 * @return string JSON output
	 */
	public function toJSON($pretty = false)
	{
		return json_encode($this->data, $pretty ? JSON_PRETTY_PRINT : 0);
	}

	/**
	 * Create an Object from given JSON string.
	 *
	 * @param string $json json
	 * @return Object
	 */
	public static function fromJSON($json)
	{
		return new Object($json);
	}

	/**
	 * Clone this object.
	 *
	 * @return Object a copy
	 */
	public function getClone()
	{
		return new Object($this);
	}

	/** 
	 * Magic method for iterator 
	 */
	function rewind()
	{
		return reset($this->data);
	}

	/** 
	 * Magic method for iterator
	 */
	function current()
	{
		return current($this->data);
	}

	/** 
	 * Magic method for iterator 
	 */
	function key()
	{
		return key($this->data);
	}

	/** 
	 * Magic method for iterator
	 */
	function next()
	{
		return next($this->data);
	}

	/** 
	 * Magic method for iterator 
	 */
	function valid()
	{
		return key($this->data) !== null;
	}
}
