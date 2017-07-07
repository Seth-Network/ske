<?php

/**
 * Array of files to copy from local path (relative within the module folder) to an installation path (path within the kohana installation)
 * Configuration files need not to be listed here as any changes on a config file will be stored in the application-config path. List here assets
 * files like javascripts, images etc
 * 
 * @var Array(String => String)
 */
return array(
	'classes/kohana.php' 						=> APPPATH .'classes/kohana.php',
	'classes/orm.php' 							=> APPPATH .'classes/orm.php',
	'classes/config/file.php'					=> APPPATH .'classes/config/file.php',
	'classes/database/query/builder/select.php'	=> APPPATH .'classes/database/query/builder/select.php',
	'config/env.php'							=> APPPATH .'config/env.php',
	'config/modules.php'						=> APPPATH .'config/modules.php',
	'assets/patch/bootstrap.php'				=> APPPATH .'bootstrap.php',
);