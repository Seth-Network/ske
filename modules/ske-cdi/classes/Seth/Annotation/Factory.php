<?php

/**
 * @Singleton
 */
class Seth_Annotation_Factory implements Annotation_Factory {
	
	/**
	 * Array of annotation names and classes.
	 * 
	 * @var Array(String=>String)
	 */
	protected $annotations;
	
	/**
	 * @var Seth_Annotation_Cache
	 */
	public $cache;
	
	public function __construct(Seth_Annotation_Cache $cache) {
		$this->cache = $cache;
	}
	
	/**
	 * Registers a new annotation class at the factory.
	 * 
	 * @param String $interface_clazz
	 */
	public function register_annotation($interface_clazz) {
		$annotation = $this->create_annotation($interface_clazz);
		
		$this->annotations[$annotation->name()] = $interface_clazz;
	}
	
	/**
	 * Returns all known annotations
	 * 
	 * @return Array(String=>String)
	 */
	public function get_registered_annotations() {
		return $this->annotations;
	}
	
	/**
	 * Returns an array with all fields of given class name and their attached annotations. If a filter is given, only fields
	 * having the annotation to filter will be returned. Returned array of will the field as first key, second key is the annotation's
	 * name.
	 *
	 * @param String|Object|ReflectionClass $clazz
	 * @param String|Array(String) $filter
	 * @return Array(String=>Array(String => Annotation))
	 */
	public function get_fields($clazz, $filter = NULL) {
		if (Kohana::$profiling === TRUE) $benchmark = Profiler::start(str_replace('_', '/', strtolower(get_called_class())), __FUNCTION__);
		$result = array();
		
		if ( $filter !== NULL && is_string($filter) ) {
			$filter = array($filter);
			
		} else if ( !is_array($filter) ) {
			$filter = array();
		}
		
		if ( !is_object($clazz) || !($clazz instanceof ReflectionClass) ) {
			$clazz = new ReflectionClass($clazz);
		}
		
		foreach ( $clazz->getProperties() as $property ) {
			$annotations = $this->get_annotations($property);
			foreach ( $filter as $f ) {
				if ( !isset($annotations[$f]) ) {
					continue 2;
				}
			}
			$result[$property->getName()] = $annotations;
		}
		if (isset($benchmark)) Profiler::stop($benchmark);
		return $result;
	}
	
	/**
	 * Returns an array with all constants of given class name and their attached annotations. If a filter is given, only constants
	 * having the annotation to filter will be returned. Returned array will have the constant's name (not value) as first key, second key is the annotation's
	 * name.
	 *
	 * @param String|Object|ReflectionClass $clazz
	 * @param String|Array(String) $filter
	 * @return Array(String=>Array(String => Annotation))
	 */
	public function get_constants($clazz, $filter = NULL) {
		if (Kohana::$profiling === TRUE) $benchmark = Profiler::start(str_replace('_', '/', strtolower(get_called_class())), __FUNCTION__);
		$result = array();
		
		if ( $filter !== NULL && is_string($filter) ) {
			$filter = array($filter);
				
		} else if ( !is_array($filter) ) {
			$filter = array();
		}
		
		if ( !is_object($clazz) || !($clazz instanceof ReflectionClass) ) {
			$clazz = new ReflectionClass($clazz);
		}
		
		$cache_changed = false;
		$cached_annotations = $this->cache->get_cached_constant_annotations($clazz);
		
		
		
		$focus_clazz = $clazz;
		do {
			
			$docHelper = new SCMS_Annotation_Factory_ConstDoc($focus_clazz);
			foreach ( $focus_clazz->getConstants() as $c => $v) {
				if ( $cached_annotations === null || !isset($cached_annotations[$c])) {
					$doc = $docHelper->getDocComment($c);
					if ( $doc !== null ) {
						$annotations = $this->get_annotations_from_doc($doc);
						$cached_annotations[$c] = $annotations;
						$cache_changed = true;
					}
				} else {
					$annotations = $cached_annotations[$c];
				}
				foreach ( $filter as $f ) {
					if ( !isset($annotations[$f]) ) {
						continue 2;
					}
				}
				$result[$v] = $annotations;
			}
			$focus_clazz = $focus_clazz->getParentClass();
		} while ( $focus_clazz !== null && $focus_clazz instanceof ReflectionClass );
		
		if ( $cache_changed ) {
			$this->cache->cache_constant_annotations($clazz, $cached_annotations);
		}
		if (isset($benchmark)) Profiler::stop($benchmark);
		return $result;
	}
	
	/**
	 * Returns an array with all methods of given class name and their attached annotations. If a filter is given, only methods
	 * having the annotation to filter will be returned. Returned array of will the method as first key, second key is the annotation's
	 * name.
	 *
	 * @param String|Object|ReflectionClass $clazz
	 * @param String|Array(String) $filter
	 * @return Array(String=>Array(String => Annotation))
	 */
	public function get_methods($clazz, $filter = NULL) {
		if (Kohana::$profiling === TRUE) $benchmark = Profiler::start(str_replace('_', '/', strtolower(get_called_class())), __FUNCTION__);
		$result = array();
		
		if ( $filter !== NULL && is_string($filter) ) {
			$filter = array($filter);
		} else if ( !is_array($filter) ) {
			$filter = array();
		}
		
		if ( !is_object($clazz) || !($clazz instanceof ReflectionClass) ) {
			$clazz = new ReflectionClass($clazz);
		}

		foreach ( $clazz->getMethods() as $method ) {
			$annotations = $this->get_annotations($method);
			
			foreach ( $filter as $f ) {
				if ( !isset($annotations[$f]) ) {
					continue 2;
				}
			}
			$result[$method->getName()] = $annotations;
		}
		if (isset($benchmark)) Profiler::stop($benchmark);
		return $result;
	}
	
	/**
	 * Returns all annotations attached to given reflectable object. A reflectable object can be a class (either identified
	 * by a class name, an object or the corresponding ReflectionClass-object), an class' field (identified by ReflectionProperty) or
	 * a class' method (ReflectionMethod). Returned array will have the annotation's name as key.
	 *
	 * @param String|Object|ReflectionClass|ReflectionProperty|ReflectionMethod $obj
	 * @return Array(String => Annotation)
	 */
	public function get_annotations($obj) {
		
		$annotations = $this->cache->get_cached_annotations($obj);
		
		if ( $annotations === null ) {
			$raw = $this->get_raw_annotations($obj);
			$annotations = $this->get_annotations_from_raw($raw);
			$this->cache->cache_annotations($obj, $annotations);
		}
		
		return $annotations;
	}
	
	protected function get_annotations_from_doc($doc) {
		$raw = $this->get_raw_annotations(null, $doc);
		return $this->get_annotations_from_raw($raw);
	}
	
	protected function get_annotations_from_raw($raws) {
		if (Kohana::$profiling === TRUE) $benchmark = Profiler::start(str_replace('_', '/', strtolower(get_called_class())), __FUNCTION__);
		
		$annotations = array();
			
		foreach ( $raws as $name => $raw ) {
			foreach ( $raw as $properties ) {
				try {
					$a = $this->get_annotation($name);
		
					$a->init($properties);
					
					if ( isset($annotations[$a->name()]) ) {
						$as = $annotations[$a->name()];
						$as = ( is_array($as) ) ? $as:array($as);
						$as[] = $a;
						$annotations[$a->name()] = $as;
						$annotations[$a->clazz_name()] = $as;
					} else {
						$annotations[$a->name()] = $a;
						$annotations[$a->clazz_name()] = $a;
					}
				} catch ( Kohana_Exception $e ) {
				}
			}
		}
		if (isset($benchmark)) Profiler::stop($benchmark);
		
		return $annotations;
	}
	
	/**
	 * Returns raw annotation data on given annotatable object like a class, a class' property or method. If given obj is null, you can provide
	 * the comment directly as second param. The returned array will contain the annotation's name
	 * as key and the annotation's property as the value
	 * 
	 * @param String|Object|ReflectionClass|ReflectionProperty|ReflectionMethod $obj
	 * @param String $docComment
	 * @return Array(String=>Array(Array(String=>String)))
	 * @throws Kohana_Exception
	 */
	protected function get_raw_annotations($obj = null, $docComment = null) {
		if (Kohana::$profiling === TRUE) $benchmark = Profiler::start(str_replace('_', '/', strtolower(get_called_class())), __FUNCTION__);
		$p = '/^\s*\*\s*@([a-zA-z_-]+)(\(.+\)|\s.+|)/';
		$pp = '/([a-zA-Z_-]+)=(?:"([^"]*?)"|\'([^\']*?)\'|([a-zA-Z-_0-9]+)),?/';
		$reflection = null;
		$raw_annotation_data = array();
		
		if ( $obj !== null && $docComment === null ) {
			// get correct reflectable object
			if ( is_object($obj) && ( $obj instanceof ReflectionClass || $obj instanceof ReflectionProperty || $obj instanceof ReflectionMethod)) {
				$reflection = $obj;
			} else if ( is_string($obj) || is_object($obj) ) {
				$reflection = new ReflectionClass($obj);
			}
			
			if ( $reflection === NULL ) {
				throw new Kohana_Exception('No reflectable object given');
			}
			
			$docComment = $reflection->getDocComment();
		} 
			
		$lines = explode("\n", $docComment);
		
		foreach ($lines as $line) {
			if ( preg_match($p, $line, $matches) ) {
				$name = $matches[1];
				
				// ignore default tags by IDE
				if ( $name == 'param' || $name == 'return' || $name == 'throws' ) {
				#	continue; // FIX 24.10.16: Do not ignore default tags in order to automatically creates API docu (e.g. swagger)
				}
				if ( !isset($raw_annotation_data[$name])) {
					$raw_annotation_data[$name] = array();
				}
				
				$properties = trim($matches[2]);
				if ( $properties == '' ) {
					$raw_annotation_data[$name][] = array();
				} else if ( preg_match_all($pp, $properties, $pmatches, PREG_SET_ORDER) != 0 ) {
					$props = array();
					foreach ( $pmatches as $pmatch ) {
						$props[$pmatch[1]] = ( !isset($pmatch[3]) ) ? $pmatch[2]:$pmatch[3];
					}
					$raw_annotation_data[$name][] = $props;
				} else {
					$raw_annotation_data[$name][] = array('value' => $properties);
				}
			}
		}
		if (isset($benchmark)) Profiler::stop($benchmark);
		return $raw_annotation_data;
	}
	
	/**
	 * Returns a new annotation instance for given annotation name.
	 * 
	 * @param String $name
	 * @return Annotation
	 */
	protected function get_annotation($name) {
		if ( isset($this->annotations[$name]) ) {
			return $this->create_annotation($this->annotations[$name]);
		}
		
		// See if interface exists
		if ( interface_exists($name) ) {
			$a = $this->create_annotation($name);
			$this->register_annotation($name);
			return $a;
		}
		
		throw new Kohana_Exception('Unknown annotation with name '. $name .'!');
	}
	
	/**
	 * Returns a new annotation instance for given annotation interface name.
	 * 
	 * @param String $interface_clazz
	 * @return Annotation
	 */
	protected function create_annotation($interface_clazz) {
		$clazz = $this->build_annotation_clazz_name($interface_clazz);
		
		// is annotation's class already loaded?
		if ( class_exists($clazz, false) ) {
			return new $clazz;
		}
		
		// class not loaded yet: see if interface_clazz's file is newer than generated annotation's clazz file
		$interface_file = Kohana::find_file('classes', str_replace('_', '/', $interface_clazz));
		$clazz_file = Kohana::find_file('classes', str_replace('_', '/', $clazz));
		
		if ( $clazz_file === false || filemtime($interface_file) > filemtime($clazz_file) ) {
			$this->create_annotation_clazz($interface_clazz);
		}
		
		return new $clazz;
	}
	
	protected function create_annotation_clazz($interface_clazz) {
		if ( $interface_clazz === NULL || $interface_clazz == '' ) {
			throw new Kohana_Exception("Annotation's interface class must not be empty or null!");
		}
		
		$clazz = $this->build_annotation_clazz_name($interface_clazz);
		$annotation = new ReflectionClass($interface_clazz);
		
		if ( !$annotation->isInterface() ) {
			 throw new Kohana_Exception('Annotation '. $interface_clazz .' must be an interface definition!');
		} elseif ( !$annotation->isSubclassOf('Annotation') ) {
			throw new Kohana_Exception("Annotation's interface class ". $interface_clazz ." must extend the generic Annotation interface!");
		}
		
		// fetch raw annotations from interface
		$raw_annotations = $this->get_raw_annotations($annotation);
		
		// annotations name should be first annotation (raw one) or the annotation interface's name
		if ( empty($raw_annotations) ) {
			$name = $interface_clazz;
		} else {
			$name = array_keys($raw_annotations)[0];
		}
		
		$additional_interfaces = '';
		if ( isset($raw_annotations[Interceptable::class])) {
			$additional_interfaces .= ', Interceptable';
		}
		
		$data  = Kohana::FILE_SECURITY ."\n\n";
		$data .= "/**\n";
		$data .= " * This is a PHP generated class based on annotation's interface '". $interface_clazz ."'.\n";
		$data .= " *\n";
		$data .= " * @author: ". __CLASS__ ."\n";
		$data .= " * @date: ". date("Y-m-d H:i:s") ."\n";
		$data .= " */\n";
		$data .= "class ". $clazz ." extends Annotation_Base implements ". $interface_clazz . $additional_interfaces ." {\n";
		$data .= "	protected \$name = '". $name ."';\n";
		$data .= "	protected \$clazz = '". $interface_clazz ."';\n";
		$methods = '';
		
		$accept = array();
		$defaults = array();
		
		foreach ( $annotation->getMethods() as $method ) {
			if ( $method->getDeclaringClass()->getName() != $annotation->getName() ) {
				continue;
			}
			$accept[] = $method->getName();
			
			$as = $this->get_annotations($method);
			
			
			if ( isset($as[Default_Value::class]) ) {
				$a = $as[Default_Value::class];
				$defaults[$method->getName()] = $a->value();
			}
			
			$methods .= "	public function ". $method->getName() ."() {\n";
			$methods .= "		return \$this->_property('". $method->getName() ."');\n";
			$methods .= "	}\n";
		}
		
		$data .= "	protected \$accept = ". var_export($accept, true) .";\n";
		$data .= "	protected \$defaults = ". var_export($defaults, true) .";\n";
		$data .= $methods;
		$data .= "}";
		
		$dir = dirname(APPPATH . 'classes/'. str_replace('_', '/', $clazz) .'.php');
		if ( !file_exists($dir) && !@mkdir($dir, 0777, true)) {
			throw new Kohana_Exception("Can not create annotation class :file: Can not create directory!", array(":file" => APPPATH . 'classes/'. str_replace('_', '/', $clazz) .'.php'));
		}
		
		if (  file_put_contents(APPPATH . 'classes/'. str_replace('_', '/', $clazz) .'.php' , $data) === false) {
			throw new Kohana_Exception("Can not create annotation class :file: Method failed!", array(":file" => APPPATH . 'classes/'. str_replace('_', '/', $clazz) .'.php'));
		} else {
			return $this;
		}
		
	}
	
	/**
	 * Returns the clazz name of the generated annotation class based on given interface class name
	 * 
	 * @param String $interface_clazz
	 * @return String
	 */
	protected function build_annotation_clazz_name($interface_clazz) {
		return 'Seth_Generated_Annotation_'. $interface_clazz;
	}
	
	/**
	 * Returns TRUE if given reflectable object is annotated with given annotation. A reflectable object can be a class (either identified
	 * by a class name, an object or the corresponding ReflectionClass-object), an class' field (identified by ReflectionProperty) or
	 * a class' method (ReflectionMethod).
	 * 
	 * @param String|Object|ReflectionClass|ReflectionProperty|ReflectionMethod $obj
	 * @param String|Annotation $annotation
	 * @return boolean
	 */
	public function has_annotation($obj, $annotation) {
		if ( is_string($annotation) ) {
			$annotation = $this->get_annotation($annotation);
		}
		
		if ( $annotation === null || !is_object($annotation) || !($annotation instanceof Annotation) ) {
			return false;
		}
		
		$as = $this->get_annotations($obj);
		foreach ( $as as $a ) {
			$ai = ( is_array($a) ) ? $a:array($a);
			foreach ( $ai as $a ) {
				if ( $a->name() == $annotation->name() ) {
					return true;
				}
			}
		}
		return false;
	}
}

/**
 * Simple DocComment support for class constants.
 *
 * Source: https://stackoverflow.com/questions/22103019/php-reflection-get-constants-doc-comment
 * @author mpdeimos
 */
class SCMS_Annotation_Factory_ConstDoc
{
	/** @var array Constant names to DocComment strings. */
	private $docComments = [];

	/** Constructor. */
	public function __construct($clazz)
	{
		$this->parse($clazz);
	}

	/** Parses the class for constant DocComments. */
	private function parse(ReflectionClass $clazz)
	{
		$content = file_get_contents($clazz->getFileName());
		$tokens = token_get_all($content);

		$doc = null;
		$isConst = false;
		foreach($tokens as $token)
		{
				
			if ( is_array($token) ) {
				list($tokenType, $tokenValue) = $token;
			} else {
				continue;
			}

			switch ($tokenType)
			{
				// ignored tokens
				case T_WHITESPACE:
				case T_COMMENT:
					break;

				case T_DOC_COMMENT:
					$doc = $tokenValue;
					break;

				case T_CONST:
					$isConst = true;
					break;

				case T_STRING:
					if ($isConst)
					{
						$this->docComments[$tokenValue] = $doc;
					}
					$doc = null;
					$isConst = false;
					break;

					// all other tokens reset the parser
				default:
					$doc = null;
					$isConst = false;
					break;
			}
		}
	}

	/** Returns an array of all constants to their DocComment. If no comment is present the comment is null. */
	public function getDocComments()
	{
		return $this->docComments;
	}

	/** Returns the DocComment of a class constant. Null if the constant has no DocComment or the constant does not exist. */
	public function getDocComment($constantName)
	{
		if (!isset($this->docComments))
		{
			return null;
		} else if ( !isset($this->docComments[$constantName]) ) {
			return null;
		}

		return $this->docComments[$constantName];
	}

	/** Cleans the doc comment. Returns null if the doc comment is null. */
	private static function clean($doc)
	{
		if ($doc === null)
		{
			return null;
		}

		$result = null;
		$lines = preg_split('/\R/', $doc);
		foreach($lines as $line)
		{
			$line = trim($line, "/* \t\x0B\0");
			if ($line === '')
			{
				continue;
			}

			if ($result != null)
			{
				$result .= ' ';
			}
			$result .= $line;
		}
		return $result;
	}
}