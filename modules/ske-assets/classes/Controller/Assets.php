<?php

class Controller_Assets extends Controller {
    const CONFIG = 'ske_assets';
	
	/**
	 * Returns the URL to the requested asset either by adressing this controller or using
	 * a public asset directory. The asset directory (by default "assets/" can be anywhere
	 * within the Kohana file system, @see Kohana::find_file(). If a asset cache directory
	 * is specified, assets will be copied there and the returned URL will point to the
	 * cached version. If a cached version of the asset file is available and outdated,
	 * the cached asset will be updated.
	 * 
	 * Examples:
	 * Controller_Assets::get("myStyles.css", "css")
	 * Controller_Assets::get("functions.js", "js/dev")
	 * Controller_Assets::get("admin/my.css")
	 * 
	 * @see config:ske_assets::assets_dir
	 * @see config:ske_assets::cache_dir
	 * @param String $resource
	 * @param string $dir
	 * @param boolean $returnSite If TRUE, the returned resource will be returned using URL:site(resource), else, only the resource will be returned
	 * @return String
	 */
	public static function get($resource, $dir="", $returnSite=true) {
		$cache_dir = Kohana::$config->load(self::CONFIG)->get('cache_dir', null);
		
		// add tailing slash to directory
		if ( $dir != "" && substr($dir, -1) != '/' ) {
			$dir = $dir ."/";
		}
		
		// sanitize file path: remove ../ and ./
		$file = preg_replace('/\w+\/\.\.\//', '', $dir . $resource);
		
		// find local, uncached file
		$localFile = static::find_file($file);
		$cachedFile = $cache_dir ."/". $file;
		$cachedFileURI = str_replace(DOCROOT, "", preg_replace('/\w+\/\.\.\//', '', $cachedFile));
		
		// If file should be loaded as VIEW, no caching should be done
		if ( array_search($file, Kohana::$config->load(self::CONFIG)->get('load_as_view', array())) !== false ) {
			// cache version exists: delete it
			if ( $cache_dir !== null && file_exists($cachedFile) ) {
				@unlink($cachedFile);
			}
		}
		// check if a cached version of the file exists: return direct path to cache
		else if ( $cache_dir !== null && file_exists($cachedFile) ) {
			// if local file is newer, try to copy to a cached version. If copy succeeded, return a link to the cached file
			// If local file is not newer, instantly return cached file link. If copy fails, do not return anything
			if ( filemtime($localFile) <= filemtime($cachedFile) || static::copy_to_cache($localFile, $cachedFile) ) {
				return ( $returnSite ) ? URL::site($cachedFileURI):$cachedFileURI;
			}
			
		} 
		// check if cache directory exists which means: caching is enabled
		else if ( $cache_dir !== null && !file_exists($cache_dir ."/". $file) && file_exists($cache_dir) && is_dir($cache_dir) ) {
			// mark $file as eligible for caching
			
			// TODO Use some kind of delayed transfer?
			// transfer frequently used assets to a public "asset" directory which can be accessed directly without this controller
			// if an asset is available in the asset directory, this method will return the direct accessible link to it.
			
			// transfer file, if successfully, return cached file link
			if ( static::copy_to_cache($localFile, $cachedFile) ) {
				return ( $returnSite ) ? URL::site($cachedFileURI):$cachedFileURI;
			}
		}
		
		
		return ( $returnSite ) ? URL::site(Route::get('ske_assets')->uri(array("file"=> $file))):Route::get('ske_assets')->uri(array("file"=> $file));
	}
	
	

	public static function script($file, array $attributes = NULL, $protocol = NULL, $index = FALSE) {
		return HTML::script(self::get($file, "", false), $attributes, $protocol, $index);
	}
	
	public static function scripts(array $files, array $attributes = NULL, $protocol = NULL, $index = FALSE) {
		$s = "";
		
		// TODO Merge all script files together in one file?
		foreach ( $files as $file ) {
			$s .= static::script($file, $attributes, $protocol, $index) ."\n";
		}
		return $s;
	}
	
	public static function style($file, array $attributes = NULL, $protocol = NULL, $index = FALSE) {
		return HTML::style(self::get($file, "", false), $attributes, $protocol, $index);
	}
	
	public static function styles(array $files, array $attributes = NULL, $protocol = NULL, $index = FALSE) {
		$s = "";
	
		// TODO Merge all style files together in one file?
		foreach ( $files as $file ) {
			$s .= static::style($file, $attributes, $protocol, $index) ."\n";
		}
		return $s;
	}
	
	
	/**
	 * Copys a local asset file to a given destination file. Returns TRUE if the copy succeeded, FALSE if an error occured.
	 * All required directory for the target file will be created
	 * 
	 * @param String $from	Source file name
	 * @param String $to	Target file name
	 * @return boolean
	 */
	protected static function copy_to_cache($from, $to) {
		// check if this is a correct file
		if ( !file_exists($from) || !is_file($from) ) {
			return false;
		}
		
		// TODO: Check extension: I do not think this is necessary as this method is only invoked by local PHP scripts using Asset::get().
		
		// create directory if not exists
		if ( !file_exists(dirname($to)) ) {
			if ( !@mkdir(dirname($to), 0777, true) ) {
				return false;
			}
		}
		
		// copy file
		return copy($from, $to);
		
	}
	
	/**
	 * Finds the given $file in the local asset directories of the Kohana file system. Returns either the local file 
	 * or NULL if the file could not be found. The asset directory is defined by the 'ske_assets' configuration group
	 * with node 'assets_dir'
	 * 
	 * @see config:ske_assets::assets_dir
	 * @param String $file
	 * @return String|NULL
	 */
	protected static function find_file($file) {
		// use kohana filesystem to find file with the assets directories
		$localFile = Kohana::find_file(Kohana::$config->load(self::CONFIG)->get('assets_dir', 'assets'), $file, false);
		
		// File Exists?
		if( file_exists($localFile) ){
			return $localFile;
		}
		return null;
	}
	
	public function action_show() {
		// fetch file from parameter
		$file = $this->request->param("file");
		
		// sanitize file path: remove ../ and ./
		$file = preg_replace('/\w+\/\.\.\//', '', $file);
		
		// find local, uncached file
		$localFile = static::find_file($file);
		
		if ( $localFile !== null ) {
		
			// Parse Info / Get Extension
			$path_parts = pathinfo($localFile);
			$ext = strtolower($path_parts["extension"]);
			
			// check if asset is of correct type
			if ( array_search($ext, Kohana::$config->load(self::CONFIG)->get('suffixes', array())) === false ) {
				throw new  HTTP_Exception_404("Unable to find asset :file!", array(":file" => $file));
			}
		
			// Determine Content Type
			$ctype = Kohana_File::mime_by_ext($ext);
			if ( $ctype === false ) {
				$ctype="application/force-download";
			}
			
			header("Content-Type: $ctype");
			
			// check if the asset should be loaded as VIEW to support query parameters
			if ( array_search($file, Kohana::$config->load(self::CONFIG)->get('load_as_view', array())) !== false ) {
				$view = new Seth_Asset_View($localFile);
				$this->response->headers("Content-Type", $ctype);
				$this->response->body($view->render());
			} else {
				ob_clean();
				flush();
				readfile($localFile);
				
				// copy the file to the asset cache directory
				$cache_dir = Kohana::$config->load(self::CONFIG)->get('cache_dir', null);
				$cachedFile = $cache_dir ."/". $file;
				
				// check if cache directory is set, no cached version is available or cached version is outdated => copy new cached version
				if ( $cache_dir !== null && (!file_exists($cachedFile) || filemtime($localFile) > filemtime($cachedFile)) ) {
					// depending on the settings, this will prevent any further scripts from checking the cached file's actuality: A manual
					// reset of all cached assets will be necessary
					// This can happen if any asset resource is accessed without Asset::get() and this action creates the cached version. In future,
					// the asset is accessed directly and without Asset::get(), no check against filemtime() is performed and the asset might
					// be outdated sometimes
					static::copy_to_cache($localFile, $cachedFile);
				}
			}
		} else {
			throw new  HTTP_Exception_404("Unable to find asset :file!", array(":file" => $file));
		}
	}
}