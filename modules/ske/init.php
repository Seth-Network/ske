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
 * Route to dynamically load assets. If configured, the Asset controller tries to place a
 * duplicate to DOC_ROOT/assets/* to bypass the SKE Asset loading facility and provide a
 * "cached" version of the asset via normal web server loading method.
 */
Route::set('ske_assets', 'assets/<file>',array(
			'file' 			=> '.*'))
		->defaults(array(
			'controller' 	=> 'assets',
			'action' 		=> 'show'
		));


Route::set('ske_admin_di', 'admin/di/<action>',array(
				'action' 		=> '(singletons|managed)'))
				->defaults(array(
						'controller' 	=> 'DI',
						'directory'		=> 'admin'
				));

Route::set('ske_admin_annotations', 'admin/annotations/<action>',array(
		'action' 		=> '(list)'))
		->defaults(array(
				'controller' 	=> 'Annotations',
				'directory'		=> 'admin'
		));

Route::set('ske_admin_l18n', 'admin/l18n/<action>',array(
		'action' 		=> '(list)'))
		->defaults(array(
				'controller' 	=> 'Localization',
				'directory'		=> 'admin'
		));
Route::set('ske_admin_l18n_api', 'admin/api/l18n/(<lang>/)<action>',array(
		'lang'			=> '([a-z][a-z]\-[a-z][a-z])',
		'action' 		=> '(update|missing)'))
		->defaults(array(
				'controller' 	=> 'Localization',
				'directory'		=> 'admin/api'
		));

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
