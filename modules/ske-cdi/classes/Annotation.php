<?php

interface Annotation {
	
	/**
	 * Returns the annotation's name.
	 * 
	 * @return String
	 */
	public function name();
	
	/**
	 * Initializes the annotation with given properties.
	 *
	 * @param Array(String=>String) $properties
	 * @throws Kohana_Exception
	 */
	public function init(array $properties = NULL);
}