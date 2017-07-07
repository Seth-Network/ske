<?php

/**
 * @Interceptor
 */
interface Interceptor extends Annotation {
	
	/**
	 * Returns the annotation this interceptor is targeting
	 * 
	 * @return String
	 * @Default(value="")
	 */
	public function value();
}