<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Configuration for the packager module. Packager is used to provide a
 * private repository for kohana modules which are not public available.
 *
 */
return array(
    /**
     * Array of repositories used to find packages. Index is the repositories name (for readability),
     * the value is the URL.
     * First come first serve.
     *
     * @var Array(String=>String)
     */
    'repos' => array(
        'seth-network' => 'http://seth-network.de/packager/'
    ),
    /**
     * Local path to store packages after downloading. Use placeholder
     * $vendor and $package.
     *
     * @var String
     */
    'destination' => MODPATH .'vendor/$vendor/$package',

    /**
     * Array of downloaded packages and where they are located.
     * If a package has been downloaded with an authorization key, the key's SHA512 is
     * also saved
     */
    'packages' => array(
      /*  array(
            'name' => 'seth-network/ske',
            'location' => MODPATH .'vendor/seth-network/ske',
            'version' => '1.0.0',
            'key' => ''
        ) */
    )
);