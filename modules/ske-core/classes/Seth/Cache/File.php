<?php

/**
 * File cache addition to track the actual caching ids with their respective file name used
 * in the admin for a nice display and to group different cache files in cache groups, using 
 * a dot separated cache id having 'group.id'
 * 
 * @author eth4n
 *
 */
class Seth_Cache_File extends Kohana_Cache_File {
	
	/**
	 * Mapping of caching ids to their filename representation, more or less
	 * id => sha(id)
	 * 
	 * @var Array(String=>String)
	 */
	protected static $keys = null;
	
	/**
	 * Temporary cache directory addition, derived from the caching id. Using a dot in the
	 * caching id to define a cache group. This is usefull for display cache origins in the
	 * admin console or to schedule jobs to clear out specific cache groups
	 * 
	 * @var String
	 */
	protected static $temp_cache_dir = null;
	
	/**
	 * Creates a hashed filename based on the string. This is used
	 * to create shorter unique IDs for each cache filename.
	 *
	 *     // Create the cache filename
	 *     $filename = Cache_File::filename($this->_sanitize_id($id));
	 *
	 * @param   string   string to hash into filename
	 * @return  string
	 */
	protected static function filename($string) {
		if ( ( $p = strpos($string, '.')) !== false ) {
			self::$temp_cache_dir  = substr($string, 0, $p);
		}
		return sha1($string).'.cache';
	}
	
	/**
	 * Resolves the cache directory real path from the filename
	 *
	 *      // Get the realpath of the cache folder
	 *      $realpath = $this->_resolve_directory($filename);
	 *
	 * @param   string   filename to resolve
	 * @return  string
	 */
	protected function _resolve_directory($filename) {
		if ( self::$temp_cache_dir !== null ) {
			$tmp = $this->_cache_dir->getRealPath() . DIRECTORY_SEPARATOR . self::$temp_cache_dir  . DIRECTORY_SEPARATOR;
			self::$temp_cache_dir = null;
			return $tmp;
		}
		return $this->_cache_dir->getRealPath().DIRECTORY_SEPARATOR.$filename[0].$filename[1].DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Set a value to cache with id and lifetime
	 *
	 *     $data = 'bar';
	 *
	 *     // Set 'bar' to 'foo' in file group, using default expiry
	 *     Cache::instance('file')->set('foo', $data);
	 *
	 *     // Set 'bar' to 'foo' in file group for 30 seconds
	 *     Cache::instance('file')->set('foo', $data, 30);
	 *
	 * @param   string   id of cache entry
	 * @param   string   data to set to cache
	 * @param   integer  lifetime in seconds
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = NULL) {
		if ( self::$keys === null ) {
			self::$keys = $this->get(SKE::CACHE_KEYS, array(SKE::CACHE_KEYS => sha1(self::_sanitize_id(SKE::CACHE_KEYS))));
		}
		if ( !isset(self::$keys[$id]) ) {
			self::$keys[$id] = sha1(self::_sanitize_id($id));
			// store keys up to 7 days
			parent::set(SKE::CACHE_KEYS, self::$keys, 60*60*24*7);
		}
		return parent::set($id, $data, $lifetime);
	}
}