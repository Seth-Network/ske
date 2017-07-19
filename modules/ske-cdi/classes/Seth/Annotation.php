<?php

abstract class Seth_Annotation {
	
	/**
	 * Name of the annotation used on annotatable object's doc comment, prefixed with an '@'-symbol
	 * 
	 * @var String
	 */
	protected $name = '';
	
	/**
	 * Returns the annotation's name
	 * 
	 * @return String
	 */
	public function get_name() {
		return $this->name;
	}
	
	/**
	 * Sets this annotation's properties provied as an assoc array OR only a string if only a single property
	 * is available.
	 * 
	 * @param String|Array(String=>String) $properties
	 * @return void
	 */
	public function properties($properties = NULL) {
		// auto convert properties into field values
		if ( !is_array($properties) && method_exists($this, 'value')) {
			$this->value($properties);
		} else if ( is_array($properties) ) {
			foreach ( $properties as $k => $v ) {
				if ( method_exists($this, $k) ) {
					$this->$k($v);
				}
			}
		}
	}
}