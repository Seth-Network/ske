<?php

class Seth_Locate_Object_Event extends Event {
	
	/**
	 * Class name of the object which is requested
	 * @var String
	 */
	protected $clazz;
	
	/**
	 * Identifier the object is using. The identifier can be a unique string defined by the implementation.
	 * 
	 * @var String
	 */
	protected $identifier;
	
	/**
	 * The requested object.
	 * 
	 * @var mixed
	 */
	protected $object = null;
	
	/**
	 * Sets and gets the requested clazz the object should have
	 *
	 * @param String $value
	 * @return String|Seth_Locate_Object_Event
	 */
	public function clazz($value = null) {
		if ($value === null) {
			return $this->clazz;
		}
		$this->clazz = $value;
		return $this;
	}
	
	/**
	 * Sets and gets the requested identifier of the object
	 *
	 * @param String $value
	 * @return String|Seth_Locate_Object_Event
	 */
	public function identifier($value = null) {
		if ($value === null) {
			return $this->identifier;
		}
		$this->identifier = $value;
		return $this;
	}
	
	/**
	 * Sets and gets the requested object. The event will immediately canceled.
	 *
	 * @param mixed $value
	 * @return mixed|Seth_Locate_Object_Event
	 */
	public function object($value = null) {
		if ($value === null) {
			return $this->object;
		}
		$this->object = $value;
		$this->cancelled(true);
		
		return $this;
	}
	
	
}