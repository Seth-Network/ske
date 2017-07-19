<?php

abstract class Seth_Annotation_Base implements Annotation {
	
	/**
	 * Name of the annotation
	 * @var String
	 */
	protected $name = '';
	
	/**
	 * Name of the annotation's interface
	 * @var String
	 */
	protected $clazz = '';
	
	/**
	 * Array of properties
	 * 
	 * @var Array(String=>String)
	 */
	protected $properties = array();
	
	protected $defaults = array();
	
	protected $accept = array();
	
	/**
	 * Returns the annotation's name.
	 *
	 * @return String
	 */
	public function name() {
		return $this->name;
	}
	
	/**
	 * Returns the annotation's interface name.
	 * 
	 * @return String
	 */
	public function clazz_name() {
		return $this->clazz;
	}
	
	/**
	 * Sets and gets the annotation's properties
	 * 
	 * @param Array(String=>String) $value
	 * @return Annotation|Array(String=>String)
	 */
	protected function _properties($value = NULL ) {
		if ( $value === NULL ) {
			return $this->properties;
		}
		$this->properties = $value;
		return $this;
	}
	
	/**
	 * Initializes the annotation with given properties.
	 * 
	 * @param Array(String=>String) $properties
	 * @throws Kohana_Exception
	 */
	public function init(array $properties = NULL) {
		$accept = $this->accept;
		foreach ( $properties as $k => $v ) {
			if ( array_search($k, $accept) === false ) {
				throw new Kohana_Exception('Annotation "'. $this->clazz_name() .'" does not accept property "'. $k .'"');
			}
		}
		
		foreach ( $this->defaults as $k => $v ) {
			if ( !isset($properties[$k]) ) {
				$properties[$k] = $v;
			}
		}
		
		foreach ( $accept as $k ) {
			if ( !isset($properties[$k])) {
				throw new Kohana_Exception('Annotation "'. $this->clazz_name() .'" is missing mandatory property "'. $k .'"');
			}
		}
		
		$this->_properties($properties);
	}
	
	/**
	 * Returns the property with given key. If the property does not exists, the $default value is returned.
	 * 
	 * @param String $key
	 * @param mixed $default
	 * @return mixed
	 */
	protected function _property($key, $default = NULL) {
		$p = $this->_properties();
		
		if ( isset($p[$key]) ) {
			return $p[$key];
		}
		return $default;
	}
	
	/**
	 * Returns the annotation's default value for given property.
	 * 
	 * @param String $key
	 * @return mixed
	 */
	public function get_default($key) {
		if ( isset($this->defaults[$key])) {
			$this->defaults[$key];
		}
		return null;
	}
}