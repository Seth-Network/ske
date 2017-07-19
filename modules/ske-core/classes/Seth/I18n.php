<?php
class Seth_I18n extends Kohana_I18n {
	
	/**
	 * Returns translation of a string. If no translation exists, the original string will be returned. No parameters are replaced.
	 * $hello = I18n::get('Hello friends, my name is :name');
	 *
	 * @param string $string
	 *        text to translate
	 * @param string $lang
	 *        target language
	 * @return string
	 */
	public static function get($string, $lang = NULL) {
		if (!$lang) {
			// Use the global target language
			$lang = I18n::$lang;
		}
		
		// Load the translation table for this language
		$table = I18n::load($lang);
		
		// Return the translated string if it exists
		if (isset($table[$string])) {
			return $table[$string];
		}
		else {
			if (Kohana::$profiling) {
			#	Seth_I18n::mark_missing($string, $lang);
			}
			return $string;
		}
	}
	
	
	public static function get_for_lang($string, $lang, $default = '') {
		// Load the translation table for this language
		$table = I18n::load($lang);
	
		// Return the translated string if it exists
		if (isset($table[$string])) {
			return $table[$string];
		}
		return $default;
	}
	
	public static function get_missing() {
		return Cache::instance()->get(SKE::CACHE_MISSING_LANG_MSG, array());
	}
	
	public static function mark_missing($string, $lang) {
		$missing = Cache::instance()->get(SKE::CACHE_MISSING_LANG_MSG, array());
		
		if (!isset($missing[$string])) {
			$missing[$string] = array();
		}
		if (array_search($lang, $missing[$string]) === false) {
			$missing[$string][] = $lang;
			Cache::instance()->set(SKE::CACHE_MISSING_LANG_MSG, $missing, SKE::CACHE_LIFETIME);
		}
	}
	
	/**
	 * Writes the passed config for $group
	 *
	 * Returns chainable instance on success or throws
	 * Kohana_Config_Exception on failure
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The configuration to write
	 * @return boolean
	 */
	public static function write($lang, $key, $value) {
		$table = array();
		
		if ( trim($lang) == "" || trim($key) == "" || trim($value) == "" ) {
			return;
		}
		
		// Split the language: language, region, locale, etc
		$parts = explode('-', $lang);
		
		// Create a path for this set of parts
		$path = implode(DIRECTORY_SEPARATOR, $parts);
		
		$file = APPPATH.'i18n'. DIRECTORY_SEPARATOR.  $path .'.php';
		
		// only check for files in app path
		if (is_file($file)) {
			$table = include $file;
		} else {
			$pinfo = pathinfo($file);
			if ( is_array($pinfo) && !is_dir($pinfo['dirname'])) {
				@mkdir($pinfo['dirname'], 0777, true);
			}
		}
		
		
		$table[$key] = $value;
	
	
		$data  = Kohana::FILE_SECURITY ."\n\n";
		$data .= "/**\n";
		$data .= " * This is a PHP generated configuration file by ". __CLASS__ ."\n";
		$data .= " * For detailed documentation for this configuration see the \n";
		$data .= " * module's configuration file.\n";
		$data .= " *\n";
		$data .= " * @author: ". __CLASS__ ."\n";
		$data .= " * @date: ". date("Y-m-d H:i:s") ."\n";
		$data .= " */\n\n";
		$data .= "return ". var_export($table, true) .";";
	
		if ( file_put_contents($file , $data) === false) {
			throw new Kohana_Exception("Can not save translation file :file: Method failed!", array(":file" => $file));
		} 
	}
}