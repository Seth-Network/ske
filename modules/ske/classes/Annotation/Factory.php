<?php

interface Annotation_Factory {
	
	/**
	 * Registers a new annotation class at the factory.
	 * 
	 * @param String $interface_clazz
	 */
	public function register_annotation($interface_clazz);
	
	/**
	 * Returns an array with all fields of given class name and their attached annotations. If a filter is given, only fields
	 * having the annotation to filter will be returned. Returned array will have the field as first key, second key is the annotation's
	 * name.
	 *
	 * @param String|Object|ReflectionClass $clazz
	 * @param String|Array(String) $filter
	 * @return Array(String=>Array(String => Annotation))
	 */
	public function get_fields($clazz, $filter = NULL);
	
	/**
	 * Returns an array with all constants of given class name and their attached annotations. If a filter is given, only constants
	 * having the annotation to filter will be returned. Returned array will have the constant's name (not value) as first key, second key is the annotation's
	 * name.
	 *
	 * @param String|Object|ReflectionClass $clazz
	 * @param String|Array(String) $filter
	 * @return Array(String=>Array(String => Annotation))
	 */
	public function get_constants($clazz, $filter = NULL);
	
	/**
	 * Returns an array with all methods of given class name and their attached annotations. If a filter is given, only methods
	 * having the annotation to filter will be returned. Returned array of will the method as first key, second key is the annotation's
	 * name.
	 *
	 * @param String|Object|ReflectionClass $clazz
	 * @param String|Array(String) $filter
	 * @return Array(String=>Array(String => Annotation))
	 */
	public function get_methods($clazz, $filter = NULL);
	
	/**
	 * Returns all annotations attached to given reflectable object. A reflectable object can be a class (either identified
	 * by a class name, an object or the corresponding ReflectionClass-object), an class' field (identified by ReflectionProperty) or
	 * a class' method (ReflectionMethod). Returned array will have the annotation's name as key.
	 *
	 * @param String|Object|ReflectionClass|ReflectionProperty|ReflectionMethod $obj
	 * @return Array(String => Annotation)
	 */
	public function get_annotations($obj);
	
	/**
	 * Returns TRUE if given reflectable object is annotated with given annotation. A reflectable object can be a class (either identified
	 * by a class name, an object or the corresponding ReflectionClass-object), an class' field (identified by ReflectionProperty) or
	 * a class' method (ReflectionMethod).
	 *
	 * @param String|Object|ReflectionClass|ReflectionProperty|ReflectionMethod $obj
	 * @param String|Annotation $annotation
	 * @return boolean
	 */
	public function has_annotation($obj, $annotation);
}