<?php

class Seth_Link {
	
	/**
	 * Array of globally set params. This is usefull if you like to set all future links to a desire directory
	 * 
	 * @var Array(String->mixed)
	 */
	protected static $globalParams = array();
	
	/**
	 * Array of globally set query params. This is usefull if you like to set all future link to add a hash tag or anchor to its links
	 *
	 * @var Array(String->mixed)
	 */
	protected static $globalQuery = array();
	
	protected $required = array('controller', 'action');
	
	protected $params = array();
	
	protected $query = array();
	
	protected $route = "default";
	
	/**
	 * Title for this link when generating a HTML anchor
	 * @var String
	 */
	protected $title = null;
	
	
	/**
	 * 
	 * @param array $params
	 * @return Link
	 */
	public function view($params=array()) {
		$p = self::require_params(func_get_args(), array('class' => ":Link.get_link_class"), $this);
		return static::factory($p['class'], $this)->params($p)->param('action', 'view');
	}
	
	/**
	 *
	 * @param array $params
	 * @return Link
	 */
	public function edit($params=array()) {
		$p = self::require_params(func_get_args(), array('class' => ":Link.get_link_class"), $this);
		return static::factory($p['class'], $this)->params($p)->param('action', 'edit');
	}
	
	/**
	 *
	 * @param array $params
	 * @return Link
	 */
	public function delete($params=array()) {
		$p = self::require_params(func_get_args(), array('class' => ":Link.get_link_class"), $this);
		return static::factory($p['class'], $this)->params($p)->param('action', 'delete');
	}
	
	/**
	 * 
	 * @param array $params
	 * @return Link
	 */
	public static function link($params=array()) {
		$p = self::require_params(func_get_args(), array('class' => ":Link.get_link_class"));
		return static::factory($p['class'], isset($this) ? $this:null)->params($p);
	}
	
	/**
	 * Tries to extract the required link class from the given value. If the value is NULL, the link class is Link. If an object is
	 * given, either the object's class itself or, if object is Linkable, the responsible link class is returned. If any fails, the 
	 * value itself is returned
	 * 
	 * @see Linkable
	 * @param mixed $value
	 * @return String
	 */
	protected static function get_link_class($value) {
		if ( $value === null ) {
			return "Link";
		} else if ( is_object($value) && $value instanceof Linkable ) {
			return $value->get_link_class();
		} else if ( is_object($value) ) {
			return get_class($value);
		}
		return $value;
	}
	
	protected static function factory($clazz, Seth_Link $l=null) {
		$linkClass = null;
		
		// find linkClass by obj
		$resolvers = Kohana::$di->create(Link_Resolver::class);
		
		if ( $resolvers !== null ) {
			$resolvers = ( is_array($resolvers) ) ? $resolvers:array($resolvers);
			foreach ( $resolvers as $resolver ) {
				if ( ( $linkClass = $resolver->get_link_class($clazz)) !== null ) {
					break;
				}
			}
		}
		
		if ( $linkClass === null && class_exists($clazz) ) {
			$linkClass = $clazz;
		}
		else if ( $linkClass === null ) {
			$linkClass = $clazz ."_Link";
		}
		
		$link = new $linkClass;
		
		if ( !($link instanceof Seth_Link)) {
			throw new Kohana_Exception("Can not resolve link for object of class '". $clazz ."'.");
		}
		
		// transfer globals
		$link->params = array_merge(self::$globalParams, $link->params);
		$link->query = array_merge(self::$globalQuery, $link->query);
		
		// transfer old data
		if ( $l !== null ) {
			$link->absorb($l);
		}
		$link->param("class", $clazz);
		
		return $link;
	}
	
	/**
	 * Absorbs all settings of given link to this object instance by copiing all required fields
	 * 
	 * @param Link $l
	 * @return void
	 */
	protected function absorb(Seth_Link $l) {
		$this->params = $l->params;
		$this->query = $l->query;
		$this->required = $l->required;
		$this->route = $l->route;
		$this->title = $l->title;
	}
	
	/**
	 * Adds an additional parameter key to the list of required ones
	 * 
	 * @chainable
	 * @param String $param
	 * @return Link
	 */
	protected function requires($key) {
		$this->required[] = $key;
		return $this;
	}
	
	
	/**
	 * Sets and gets this link's parameter for given key
	 *
	 * @chainable
	 * @param String $key
	 * @param mixed $value
	 * @return mixed|Link
	 */
	public function param($key, $value = NULL) {
		if ( $key !== null && $value === null ) {
			return ( isset($this->params[$key]) ) ? $this->params[$key]:null;
		}
	
		$this->params[$key] = $value;
		return $this;
	}
	
	public function unset_param($key) {
		unset($this->params[$key]);
	}
	
	/**
	 * Sets and gets this link's parameter list. Setting the parameters wont overwrite any existing parameters
	 *
	 * @chainable
	 * @param Array $value
	 * @return Array|Link
	 */
	public function params(array $value=null) {
		if ( $value === null ) {
			return $this->params;
		}
	
		$this->params = array_merge($value, $this->params);
		return $this;
	}
	
	
	
	/**
	 * Sets and gets the Link class global parameter for given key. Returns the value for the key
	 * 
	 * @param String $key
	 * @param mixed $value
	 * @return mixed
	 */
	public static function gparam($key = null, $value = null) {
		if ( $key !== null && $value === null ) {
			return self::$globalParams[$key];
		} else if ( $key === null && $value === null ) {
			return self::$globalParams;
		}
	
		self::$globalParams[$key] = $value;
		return $value;
	}
	
	/**
	 * Sets and gets the Link class global query params for given key. Returns the value for the key
	 *
	 * @param String $key
	 * @param mixed $value
	 * @return mixed
	 */
	public static function gquery($key = null, $value = null) {
		if ( $key !== null && $value === null ) {
			return self::$globalQuery[$key];
		} else if ( $key === null && $value === null ) {
			return self::$globalQuery;
		}
	
		self::$globalQuery[$key] = $value;
		return $value;
	}
	
	/**
	 * Sets and gets this link's query arguement for given key
	 *
	 * @chainable
	 * @param String|Array(String=>mixed) $key
	 * @param mixed $value
	 * @return Array(String=>mixed)|Link
	 */
	public function query($key=NULL, $value=null) {
		if ( $key !== null && $value === null ) {
			if ( is_array($key) ) {
				$this->query = $key;
				return $this;
			}
			return $this->query[$key];
		} else if ( $key === null ) {
			return $this->query;
		}
	
		$this->query[$key] = $value;
		return $this;
	}
	
	/**
	 * Sets or unsets the link's directory parameter. If $value is set to NULL, the directory parameter will
	 * be deleted. Returns this link instance everytime!
	 * 
	 * @chainable
	 * @param String $value
	 * @return Link
	 */
	public function directory($value) {
		if ( $value === null ) {
			unset($this->params['directory']);
		} else {
			$this->param('directory', $value);
		}
		return $this;
	}
	
	/**
	 * Sets and gets this link's title. Note that the title is not escaped to allow HTML elements within links (images, etc).
	 *
	 * @param String $value
	 * @return String|Link
	 */
	public function title($value=null) {
		if ( $value === null ) {
			return $this->title;
		}
	
		$this->title = $value;
		return $this;
	}
	
	/**
	 * Sets or unsets the link's action parameter.
	 *
	 * @chainable
	 * @param String $value
	 * @return String|Link
	 */
	public function action($value=NULL) {
		if ( $value === null ) {
			unset($this->params['action']);
		}
	
		$this->param('action', $value);
	
		return $this;
	}
	
	/**
	 * Create HTML link anchors. Note that the title is not escaped, to allow HTML elements within links (images, etc).
	 * 
	 * @see HTML::anchor
	 * @param string $title Link text
	 * @param array $attributes HTML anchor attributes
	 * @param string $protocol Use a specific protocol
	 * @return String
	 */
	public function anchor($title = NULL, array $attributes = NULL, $protocol = NULL) {
		

		return HTML::anchor($this->href(true), $title !== NULL ? $title:$this->title(), $attributes, $protocol);
	}
	
	/**
	 * Creates a request object with this link's URI
	 * 
	 * @return Request
	 */
	public function request() {
		$query =  http_build_query($this->query());
		if ( $query != "" ) {
			$query = "?". $query;
		}
		return Request::factory(Route::get($this->route())->uri($this->params()) . $query);
	}
	
	/**
	 * Returns the absolute link URI which can be used within a href attribute	 * 
	 * 
	 * @return String
	 * @throws Kohana_Exception
	 */
	public function href($absolute=false) {
		// check if required fields are available
		// TODO
		
		// check if route is set
		if ( $this->route() == "" ) {
			throw new Kohana_Exception("Can not generate link: No route set!");
		}
		
		$query =  http_build_query($this->query());
		if ( $query != "" ) {
			$query = "?". $query;
		}
		
		if ( $absolute ) {
			return Route::get($this->route())->uri($this->params()) . $query;
		} else {
			return URL::site(Route::get($this->route())->uri($this->params())) . $query;
		}
	}
	
	/**
	 * Sets and gets this link's route
	 *
	 * @param String $value
	 * @return String|Link
	 */
	public function route($value=null) {
		if ( $value === null ) {
			return $this->route;
		}
	
		$this->route = $value;
		return $this;
	}
	
	/**
	 * Checks if the list of required parameters is available either in the argument array or in the given link's parameter list.
	 * By default, the first argument of each's link creation method is an array called $params. If so, this array has to contain
	 * all required keys with correct type. If the $params array is not present, then the $args will be mapped directly to the required 
	 * parameters (and same order). If the $args array does not contain the $required keys, an optional given link $l may already contain 
	 * the desired information.
	 * The method returns an array containing the required keys and their correct values. Else, an exception is thrown.
	 * 
	 * To specify the required values' type, the corresponding value of the $required-array can be used to define a variable type,
	 * a class name, a comma separated list of valid types and, separated by a colon, a callback to extract the correct value. The
	 * callback-string can either a function name or a classname[dot]methodname for static calls or [dot]methodname for method calls if
	 * the value is an object. A default value can be specified after a pipe. Callbacks or default values arent used if the given Link provides
	 * a suitable value
	 * 
	 * Examples:
	 * require_params(array(array('objClass' => A, 5, 'limit' => 2000)), array('objClass' => 'String:strtolower', 'size' => 'int', 'limit' => 'int:MyClass.toKiloByte', 'direction' => 'String|North'))
	 * 	Returns: array('objClass' => 'a', 'size' => 5, 'limit' => 1.95, 'direction' => 'North')
	 * 
	 * Same as 
	 * require_params(array(A, 5, 2000), array('objClass' => 'String:strtolower', 'size' => 'int', 'limit' => 'int:MyClass.toKiloByte'))
	 * 	Returns: array('objClass' => 'a', 'size' => 5, 'limit' => 1,95)
	 * 
	 * @param Array $args
	 * @param Array(String=>String) $params
	 * @param Seth_Link $l
	 */
	protected static function require_params(array $args, $required=array(), Seth_Link $l=null) {
		$values = array();
		foreach ( $required as $k => $v ) {
			$valueDesc = $v;
			if ( !is_string($k) ) {
				$k = $v;
				$valueDesc = null;
			}
			
			// fetch next available value
			$value = null;
			if ( !self::fetch_next_value($args, $k, $value) ) {
				// value wasnt found in the args array: check for link
				if ( $l !== null ) {
					if ( isset($l->params[$k]) ) {
						// value was found in link: no callbacks, checks or default values will be applied
						$values[$k] = $l->params[$k];
						continue;
					}
				}
				
				// value was not found, neither in args nor in link
				throw new Kohana_Exception("Can not build link object: Required param ':key not' found!", array(":key" => $k));
			}
			
			
			// no description given:
			if ( $valueDesc === null || $valueDesc == "" ) {
				$values[$k] = $value;
				continue;
			}
			
			// apply description
			$pattern = '/(?<types>[^|:]+|)(?::(?<callback>[^|]+)|)(?:\|(?<default>.*)|)/';
			if ( preg_match($pattern, $v, $matches) == 0 ) {
				throw new Kohana_Exception("Can not build link object: bad parameter description ':desc'", array(':desc' => $valueDesc));
			}
			// check for correct type
			if ( isset($matches['types']) && $matches['types'] != "" ) {
				$typeMatch = 0;
				foreach ( explode(",", $matches['types'] .",") as $requiredType) {
					$requiredType = trim($requiredType);
					if ( $requiredType == "" ) {
						continue;
					}
					if ((strtolower($requiredType) == "int" && is_int($value)) ||
						(strtolower($requiredType) == "float" && is_float($value)) ||
						(strtolower($requiredType) == "bool" && is_bool($value)) ||
						(strtolower($requiredType) == "double" && is_double($value)) ||
						(strtolower($requiredType) == "long" && is_long($value)) ||
						(strtolower($requiredType) == "object" && is_object($value)) ||
						(strtolower($requiredType) == "array" && is_array($value)) ||
						(strtolower($requiredType) == "string" && is_string($value)) ||
						(is_object($value) && $value instanceof $requiredType) ){
						$typeMatch++;
					}
				}
				
				if ( $typeMatch== 0 ) {
					throw new Kohana_Exception("Can not build link object: Required parameter :param is of wrong type :type, possible valid types are :types", array(":param" => $k, ":type" => gettype($value), ":types" => $matches['types']));
				}
			}
			
			// call transformer callback
			if ( isset($matches['callback'])) {
				$callback = trim($matches['callback']);
				if ( $callback != "" ) {
					if ( strpos($callback, ".") !== false && $callback{0} == ".") {
						$callback = substr($callback, 1);
						if ( !is_object($value) ) {
							throw new Kohana_Exception("Can not call callback method :func on a non object for required parameter :param", array(":func" => $callback, ":param" => $k));
						} else {
							$value = $value->$callback();
						}
					} else if ( strpos($callback, ".") === false && !function_exists($callback)) {
						throw new Kohana_Exception("Can not call callback function :func for required parameter :param: Function does not exist", array(":func" => $callback, ":param" => $k));
					} else if ( strpos($callback, ".") === false ) {
						$value = $callback($value);
					} else {
						$callbackArray = explode(".", $callback);
						$clazz = $callbackArray[0];
						$method = $callbackArray[1];
						if ( !class_exists($clazz)) {
							throw new Kohana_Exception("Can not call static callback method :func for required parameter :param: Class :o does not exist", array(":o" => $clazz, ":func" => $callback, ":param" => $k));
						}
						$value = $clazz::$method($value);
					}
				}
			}
			
			// use default value
			if ( $value === null && isset($matches['default'])) {
				$value = $matches['default'];
			}
			$values[$k] = $value;
		}
		
		
		return $values;
	}
	
	private static function fetch_next_value(array &$args, $key, &$value) {
		if ( count($args) >= 1 && !is_array($args[0])) {
			$value=  array_pop($args);
			return true;
		} else if ( count($args) == 1 && is_array($args[0]) ) {
			$localArgs = $args[0];
			
			if ( isset($localArgs[$key]) ) {
				$value = $localArgs[$key];
				return true;
			} else {
				foreach ( $localArgs as $i => $v ) {
					if ( is_numeric($i) ) {
						$value = $v;
						unset($args[0][$i]);
						return true;
					}
				}
			}
		} 
		return false;
	}
}