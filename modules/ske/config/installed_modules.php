<?php defined('SYSPATH') OR die('No direct script access.');

return array(
    'encrypt'       => MODPATH . 'vendor/koseven/koseven/modules/encrypt',    // Encryption supprt
    'auth'          => MODPATH . 'vendor/koseven/koseven/modules/auth',       // Basic authentication
    'cache'         => MODPATH . 'vendor/koseven/koseven/modules/cache',      // Caching with multiple backends
    'codebench'     => MODPATH . 'vendor/koseven/koseven/modules/codebench',  // Benchmarking tool
    'database'      => MODPATH . 'vendor/koseven/koseven/modules/database',   // Database access
    'image'         => MODPATH . 'vendor/koseven/koseven/modules/image',      // Image manipulation
    'orm'           => MODPATH . 'vendor/koseven/koseven/modules/orm',        // Object Relationship Mapping
    'pagination'    => MODPATH . 'vendor/koseven/koseven/modules/pagination', // Pagination
    'unittest'      => MODPATH . 'vendor/koseven/koseven/modules/unittest',   // Unit testing
    'userguide'     => MODPATH . 'vendor/koseven/koseven/modules/userguide',  // User guide and API documentation
);