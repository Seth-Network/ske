<?php

interface DI_Container {
	
	/**
	 * Returns a fully constructed object based on $name using $args and $share as constructor arguments if supplied
	 * @param string name The name of the class to instantiate
	 * @param array $args An array with any additional arguments to be passed into the constructor upon instantiation
	 * @param array $share Whether or not this class instance be shared, so that the same instance is passed around each time
	 * @return object A fully constructed object based on the specified input arguments
	 */
	public function create($name, array $args = [], array $share = []);
	
	/**
	 * Registers given class name to the DI container. This method will not create a new instance of given class.
	 *
	 * @param String $clazz
	 * @return void
	 */
	public function register($clazz);
	
}