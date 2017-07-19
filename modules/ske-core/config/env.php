<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Configuration file loaded during environment setup in bootstrap.php
 * This config will be loaded directly without the use of a config loader
 * class as the bootstrap process does not have access to any objects.
 *
 * Please refer to the Kohana configuration site to see further information.
 *
 * Note: You need to patch your bootstrap.php file in the application folder
 * with the one provided by the Seth Kohana Extension (SKE) module, located
 * in assets/patch/ for this config to work. This file will make configuration
 * changes via webinterface easier!
 *
 * @author eth4n
 */
return array(
    /**
     * Default timezone
     *
     * @see  http://php.net/timezones
     */
    'timezone' => 'Europe/Berlin',

    /**
     * Default locale
     *
     * @see  http://php.net/setlocale
     */
    'locale' => 'en_US.utf-8',

    /**
     * default language
     */
    'language' => 'en-en',

    /**
     * Current Kohana environment name
     *
     * @see Kohana_Core
     */
    'environment' => Kohana::DEVELOPMENT,

    /**
     * Default log writer attached will write logs to this dir
     *
     * @see Kohana_Log_File::__construct()
     */
    'log_dir' => APPPATH . 'logs',

    /**
     * Minimum log level to be written to the logs. You have to use the integer value
     * instead of the constant as the constant's class is not available in bootstrap.
     *
     * EMERGENCY = 0
     * ALERT     = 1
     * CRITICAL  = 2
     * ERROR     = 3
     * WARNING   = 4
     * NOTICE    = 5
     * INFO      = 6
     * DEBUG     = 7
     */
    'log_level' => 5,

    /**
     * Immediately write when logs are added
     */
    'log_write_immediately' => false,

    /**
     * Cookie salt
     * @see  http://kohanaframework.org/3.3/guide/kohana/cookies
     */
    'cache_salt' => '',

    /**
     * Add the default Kohana route to the application within
     * the bootstrap
     */
    'enable_default_route' => true,

    /* ---------------------------------------------
     * 	Configuration for Kohana::init()
     * --------------------------------------------- */
    /**
     * path, and optionally domain, of your application
     */
    'base_url' => '/',

    /**
     * enable or disable internal profiling
     */
    'profile' => true,

    /**
     * name of your index file, usually "index.php"
     */
    'index_file' => FALSE,

    /**
     * internal character set used for input and output
     */
    'charset' => 'utf-8',

    /**
     * set the internal cache directory
     */
    'cache_dir' => APPPATH . 'cache',

    /**
     * enable or disable error handling
     */
    'errors' => TRUE,

    /**
     * enable or disable internal caching
     */
    'caching' => FALSE,
);