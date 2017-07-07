<?php

class Seth_Factory_Context {
	
	/**
	 * Array of callables which resambles the factory chain.
	 * 
	 * @var Array(callable)
	 */
	protected $chain = array();
	
	/**
	 * The class name which should be created and returned.
	 * 
	 * @var String
	 */
	protected $clazz = '';
	
	/**
	 * Context associated data map
	 * @var Array(String=>mixed)
	 */
	protected $data = array();
	
	/**
	 * Constructs a new factory context with given callables which will be called one after the other until one
	 * callable returns a suitable object as requested.
	 * 
	 * @param Array(callable) $callables
	 */
	public function __construct(array $callables) {
		$this->chain = $callables;
	}
	
	/**
	 * Sets and gets the class name of the object which should be created.
	 * 
	 * @chainable
	 * @param String $value
	 * @return String|Factory_Context
	 */
	public function clazz($value = NULL) {
		if ( $value === NULL ) {
			return $this->clazz;
		}
		$this->clazz = $value;
		return $this;
	}
	
	/**
	 * Sets and gets the context associated data.
	 * 
	 * @chainable
	 * @param Array(String=>mixed) $value
	 * @return Array(String=>mixed)|Factory_Context
	 */
	public function data(array $value = NULL) {
		if ( $value === NULL ) {
			return $this->data;
		}
		$this->data = $value;
		return $this;
	}
	
	/**
	 * Proceed with the next factory in the factory chain.
	 * 
	 * @return Object	Returns an object with given class name
	 */
	public function proceed() {
		if ( empty($this->chain) ) {
			return null;
		}
		$callable = array_shift($this->chain);
		$obj = call_user_func($callable, $this);
		return $obj;
	}
}