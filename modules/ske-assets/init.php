<?php defined('SYSPATH') or die('No direct script access.');
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
 * duplicate to DOCROOT/assets/* to bypass the SKE Asset loading facility and provide a
 * "cached" version of the asset via normal web server loading method.
 */
Route::set('ske_assets', 'assets/<file>',array(
			'file' 			=> '.*'))
		->defaults(array(
			'controller' 	=> 'assets',
			'action' 		=> 'show'
		));


