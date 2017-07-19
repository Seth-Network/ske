<?php

class Seth_Locate_Objects_Event extends Locate_Object_Event {
	
	protected $objects = array();
	
	/**
	 * Adds a requested object or gets the first object. 
	 *
	 * @param mixed $value
	 * @return mixed|Seth_Locate_Objects_Event
	 */
	public function object($value = null) {
		if ($value === null) {
			return current($this->object);
		}
		$this->objects[] = $value;
		
		return $this;
	}
	
	/**
	 * Sets and gets the requested objects
	 *
	 * @param Array(mixed) $value
	 * @return Array(mixed)|Seth_Locate_Objects_Event
	 */
	public function objects($value = null) {
		if ($value === null) {
			return $this->objects;
		}
		$this->objects = $value;
		return $this;
	}
}