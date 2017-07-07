<?php

/**
 * @Singleton
 */
class Seth_Annotation_Cache {

	const CACHE_NAME = "ske.annotations";
	
	const CONSTANTS_SUFFIX = '/constants';
	
	/**
	 * Local cache array which is loaded and eventually deserialized from Kohana_Cache
	 *
	 * @var Array
	 */
	protected $cache = NULL;
	
	protected $cache_loaded = false;
	
	/**
	 * Flag to indicate if the local cache was changed during execution. If yes then the local cache will be deserialized and written back
	 *
	 * @var boolean
	 */
	protected $changed = false;

	
	/**
	 * Gets and sets this cache local data structure to represent the cache's data
	 *
	 * @chainable
	 * @param Array $value
	 * @return Array|Seth_Annotation_Cache
	 */
	protected function cache(array $value = NULL) {
		if ( $value === NULL ) {
			return $this->cache;
		}
		$this->cache = $value;
		return $this;
	}
	
	/**
	 * Gets this cache's name as it is stored in the Kohana_Cache
	 *
	 * @return String
	 */
	protected function name() {
		return self::CACHE_NAME;
	}
	
	/**
	 * Loads the internal cache's data if it is not loaded yet. If the cache is not loaded, the Kohana_Cache will be asked to provide
	 * the data with this cache's name (see name()). If no cache is available yet, this method will call build_cache() which is
	 * used to create a initial cache state. If the data is loaded from Kohana_Cache, method deserialize_raw_cache() is called to
	 * deserialize the data
	 *
	 * @return void
	 */
	protected function __load_cache() {
		if (!$this->cache_loaded && class_exists('Cache')) {
			if (Kohana::$profiling === TRUE) $benchmark = Profiler::start(str_replace('_', '/', strtolower(get_called_class())), __FUNCTION__);
			// register shutdown function to save back cache data
			register_shutdown_function(array(
					$this,
					'__unload_cache'
			));
			$c = null;
			if ( Kohana::$environment != Kohana::DEVELOPMENT ) {
				$c = Cache::instance()->get($this->name(), null);
			}
				
			if ($c === null) {
				$c = array();
				$this->changed(true);
			}
				
			// store data
			$this->cache($c);
			$this->cache_loaded = true;
			if (isset($benchmark)) Profiler::stop($benchmark);
		}
	}
	
	/**
	 * Sets and gets if this cache is changed during execution
	 *
	 * @chainable
	 * @param boolean $value
	 * @return boolean|Seth_Annotation_Cache
	 */
	protected function changed($value = NULL) {
		if ( $value === NULL ) {
			return $this->changed;
		}
		if ( $this->cache_loaded ) {
		$this->changed = $value;
		}
		return $this;
	}
	
	/**
	 * DO NOT CALL THIS METHOD BY YOURSELF
	 * This method is called as a shutdown function and, if the cache has changed, will serialize the cache and will store the raw data into Kohana_Cache
	 */
	public function __unload_cache() {
		if ($this->changed() && $this->cache_loaded && Kohana::$environment != Kohana::DEVELOPMENT) {
			
			// save back
			Cache::instance()->set($this->name(), $this->cache(), (60 * 60 * 24));
		}
	}
	
	
	/**
	 * Returns the cached value with given identifier. If the identifier does not exists in the current cached data, the default value is returned
	 *
	 * @param String|int $identifier
	 * @return mixed
	 */
	protected function _get($identifier, $default = NULL) {
		$this->__load_cache();
		if ( $identifier !== null && $this->cache() !== null && isset($this->cache()[$identifier])) {
			if (Kohana::$profiling === TRUE) Profiler::stop(Profiler::start(str_replace('_', '/', strtolower(get_called_class())), 'hit'));
			return $this->cache()[$identifier];
		} else if ( $identifier !== null ) {
			if (Kohana::$profiling === TRUE) Profiler::stop(Profiler::start(str_replace('_', '/', strtolower(get_called_class())), 'miss'));
		}
			
		return $default;
	}
	
	/**
	 * Stores the given value within given identifier in this cache.
	 *
	 * @chainable
	 * @param String|int $identifier
	 * @param mixed $value
	 * @return Seth_Annotation_Cache
	 */
	protected function _add($identifier, $value) {
		if ( $identifier !== null ) {
			$this->__load_cache();
			$this->cache[$identifier] = $value;
			$this->changed(true);
		}
		return $this;
	}
	
	/**
	 * Removes given identifier from this cache
	 *
	 * @chainable
	 * @param String|int $identifier
	 * @return Seth_Annotation_Cache
	 */
	protected function _remove($identifier) {
		$this->__load_cache();
		unset($this->cache[$identifier]);
		$this->changed(true);
		return $this;
	}
	
	protected function get_cache_key($obj, $suffix = '') {
		// get correct reflectable object
		if ( is_object($obj) && ( $obj instanceof ReflectionClass || $obj instanceof ReflectionProperty || $obj instanceof ReflectionMethod)) {
			$reflection = $obj;
		} else if ( is_string($obj) || is_object($obj) ) {
			$reflection = new ReflectionClass($obj);
		}
			
		if ( $reflection === NULL ) {
			return null;
		}
		
		if ( $reflection instanceof ReflectionClass ) {
			return $reflection->getNamespaceName() ."/". $reflection->getName() . $suffix;
		} else if ( $reflection instanceof ReflectionProperty || $reflection instanceof ReflectionMethod ) {
			return $reflection->getDeclaringClass()->getNamespaceName() ."/". $reflection->getDeclaringClass()->getName() ."::". $reflection->getName() . $suffix;
		}
		
		
		return null;
	}
	
	public function get_cached_constant_annotations($obj) {
		$key = $this->get_cache_key($obj, self::CONSTANTS_SUFFIX);
		return $this->_get($key, null);
	}
	
	public function get_cached_annotations($obj) {
		$key = $this->get_cache_key($obj);
	
		return $this->_get($key, null);
	}
	
	public function cache_annotations($obj, array $annotations) {
		$key = $this->get_cache_key($obj);
		$this->_add($key, $annotations);
	}
	
	public function cache_constant_annotations($obj, array $annotations) {
		$key = $this->get_cache_key($obj, self::CONSTANTS_SUFFIX);
		$this->_add($key, $annotations);
	}
}