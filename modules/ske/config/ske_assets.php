<?php

return array(
		/*
		 * Relative directory within the kohana file system where assets can be located
		 */
		'assets_dir' => 'assets',
		
		/*
		 * Public accassible directory which will be used as cache directory to bypass the asset controller
		 * Set to NULL to disable asset caching
		 */
		'cache_dir' => DOCROOT .'assets',
		
		/*
		 * Array of possible asset suffixes. Assets with suffixes not listed here 
		 * can not be acccessed using the SKE Asset controller!
		 */
		'suffixes' => array(
			"css", 
			"js", 
			"map",
			"jpg", 
			"jpeg", 
			"png",
			"gif",
			"ttf",
			"woff",
			"woff2"
		),
		
		/**
		 * Array of possible assets which are loaded as VIEW files. By default, assets will
		 * be loaded with readfile(). To load an asset file as VIEW (with Kohana_View::factory()) allows access of
		 * script resources, query parameter etc.
		 * 
		 * Files which should be loaded as VIEW files must not exists in the ASSETS directory!
		 */
		'load_as_view' => array(
		)
);