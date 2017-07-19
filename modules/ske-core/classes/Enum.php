<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Seth Enumeration class
 *
 * Use this class as parent class to your
 * enumeration classes which will introduce
 * constants to specify the enumeration's values.
 *
 * If you do not use the strict enumeration flag on
 * construction, try to use string values to be able
 * to compare object instances with direct
 * constant values
 *
 *
 * @author eth4n
 *
 */
abstract class Enum {

	private $value = null;
	private $valueString = null;

	/**
	 * Creates an enumeration instance using $value. If $strict is set to TRUE
	 * and $value does not belong to the enumeration, an UnexpectedValueException
	 * is thrown
	 *
	 * @param mixed $value
	 * @param boolean $strict
	 * @throws UnexpectedValueException
	 */
	public function __construct($value, $strict=true) {
		$r = new ReflectionClass($this);

		// search constants for this value
		foreach ( $r->getConstants() as $key => $v ) {
			if ( strtolower($v) == strtolower($value) ) {
				$this->value = $value;
				$this->valueString = $key;
				break;
			}
		}

		// value not found and strict mode: exception
		if ( $this->value === null && $strict ) {
			throw new UnexpectedValueException($value);
		}
		// value not found but not in strict mode: just
		// use the value
		else if ( $this->value ) {
			$this->value = $value;
		}
	}

	/**
	 * Returns the enumeration instance's value or rather its key
	 * as string (if set and $autoKey is TRUE). The key is the
	 * constant string representation
	 *
	 * @param boolean $autoKey
	 * @return mixed
	 */
	public function getValue($autoKey=true) {
		return ( !$autoKey || $this->valueString == null ) ? $this->value:$this->valueString;
	}

	/**
	 * Returns the enumeration instance's value
	 *
	 * @return mixed
	 */
	public function _getValue() {
		return $this->value;
	}

	/**
	 * Returns the enumeration instance's key or NULL, if no
	 * key is available. The key is the constant string
	 * representation
	 *
	 * @return String
	 */
	public function _getKey() {
		return $this->valueString;
	}

	/**
	 * Returns all consts (possible values) as an array.
	 *
	 * @return array
	 */
	public function getConstList() {
		$r = new ReflectionClass($this);

		return $r->getConstants();
	}

	/**
	 * Generic compare method which will return TRUE, if this enum
	 * instance equals $value
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function __equals($value) {
		return $this->value == $value;
	}
	
	public static function __callStatic($name, $arguments) {
		$className = __CLASS__;
		return new $className($name);
	}

	/**
	 * Returns a string representation of this enumeration instance
	 */
	public function __toString() {
		return ( $this->valueString == null ) ? (string)$this->value:$this->valueString;
	}

}