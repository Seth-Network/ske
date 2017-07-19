<?php

/**
 * @Inject
 */
interface Inject extends Annotation {
	
	/**
	 * Returns the object's class or interface name to inject.
	 * 
	 * @return String
	 * @Default(value="")
	 */
	public function value();
}