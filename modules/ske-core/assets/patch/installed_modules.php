<?php defined('SYSPATH') OR die('No direct script access.');

return array(
    /**
     *  Modules shipped with Koseven @see https://github.com/koseven/koseven
     */
    'auth'          => MODPATH . 'vendor/koseven/koseven/modules/auth',       // Basic authentication
    'cache'         => MODPATH . 'vendor/koseven/koseven/modules/cache',      // Caching with multiple backends
    'codebench'     => MODPATH . 'vendor/koseven/koseven/modules/codebench',  // Benchmarking tool
    'database'      => MODPATH . 'vendor/koseven/koseven/modules/database',   // Database access
    'encrypt'       => MODPATH . 'vendor/koseven/koseven/modules/encrypt',    // Encryption support
    'image'         => MODPATH . 'vendor/koseven/koseven/modules/image',      // Image manipulation
    'orm'           => MODPATH . 'vendor/koseven/koseven/modules/orm',        // Object Relationship Mapping
    'pagination'    => MODPATH . 'vendor/koseven/koseven/modules/pagination', // Pagination
    'unittest'      => MODPATH . 'vendor/koseven/koseven/modules/unittest',   // Unit testing
    'userguide'     => MODPATH . 'vendor/koseven/koseven/modules/userguide',  // User guide and API documentation

    /**
     *  SKE modules
     */
    'assets'		=> MODPATH.'vendor/seth-network/ske/modules/ske-assets',
    'identity'		=> MODPATH.'vendor/seth-network/ske/modules/ske-identity',
    'events'		=> MODPATH.'vendor/seth-network/ske/modules/ske-events',
    'cdi'			=> MODPATH.'vendor/seth-network/ske/modules/ske-cdi',
);