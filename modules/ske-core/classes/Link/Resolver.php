<?php


interface Link_Resolver {
	
	/**
	 * Returns the class' name of a link class which extends class Link to be used when creating
	 * links for given object type. If no link class is known, null is returned. Implementations of this
	 * interface should register themselfs to the DI container to be called when required.
	 * 
	 * @param String $object_clazz
	 * @return String|null
	 */
	public function get_link_class($object_clazz);
}