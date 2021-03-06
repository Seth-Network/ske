<?php defined('SYSPATH') or die('No direct script access.');
/*

*/
###########################################################################################


/**
 ###########################################################################################
 E V E N T  L I S T E N E R S
 ###########################################################################################
 */



/**
 ###########################################################################################
 R O U T E S
 ###########################################################################################
*/



/**
 ###########################################################################################
 H E L P E R
 ###########################################################################################
 */
/**
 * Wrapper function to chain constructer and methods together,
 * which is not possible in PHP prior 5.4.0:
 * new A()->b()->c()
 *
 * Just use syntax:
 * with(new A())->b()->c()
 *
 * @param Object $object	Any object which will be returned
 * @return Object	Same object as $object
 */
function &with($object){
	return $object;
}

if ( ! function_exists('___'))
{
	/**
	 *
	 *
	 *    ___('forum.title.welcome', 'Welcome back, :user', array(':user' => $username));
	 *
	 * [!!] The target language is defined by [I18n::$lang].
	 *
	 * @uses    I18n::get
	 * @param		string	$key Key to search in language file to get translated text
	 * @param   string  $default_string text to use if language file does not contain language key
	 * @param   array   $values values to replace in the translated text
	 * @param   string  $lang   source language
	 * @return  string
	 */
	function ___($key, $default_string=null, array $values = NULL, $lang = 'en-us') {

		if ( $default_string === null ) {
			$default_string = $key;
		}
		if ($lang !== I18n::$lang)
		{
			if ( ! $lang)
			{
				// Use the global target language
				$lang = I18n::$lang;
			}

			// Load the translation table for this language
			$table = I18n::load($lang);

			// The message and target languages are different
			// Get the translation for this message
			if ( isset($table[$key]) ) {
				$string = $table[$key];
			} else {
				$string = $default_string;

				if ( Kohana::$profiling ) {
					// TODO Mark this label key as missing

				}
			}

		}

		return empty($values) ? $string : strtr($string, $values);
	}
}
