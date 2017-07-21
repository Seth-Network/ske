<?php

/**
 * Task to install additional packages
 *
 * Syntax:
 *  ./minion ske:packager [--list] [--search] [--download] [--dest=DESTINATION] [--add=PATH --version=VERSION] [--install] [--inspect] [--check] [--update] [--remove [--delete] [--force]] [--key=KEY] [--version=VERSION] [PACKAGE]
 *
 * PACKAGE: A package is defined by two parts separated by a slash. The first part is the vendor, the second part is the package's name.
 *      Example: seth-network/ske
 *
 * Available actions are
 *  - list: List all local packages
 *  - search: Searches for a package in the repositories
 *  - download: Downloads a package from the repositories, can be combined with --dest and --version
 *  - dest: Use this destination when downloading/installing new packages instead of the configured default one. Use $vendor and $package as placeholder
 *  - install: Downloads a new package and registers the package as an available module. Can be combined with --dest and --version
 *  - check: Checks for updates for a given package
 *  - inspect: Scans a downloaded package for valid Kohana modules
 *  - update: Updates a package to the latest version (or if --version given, to a specific version)
 *  - remove: Removes a disabled package. If --force is given, package will be disabled and removed instantly. If --delete is given, package's files will be deleted from disk - this can not be undone!
 *  - key: If required, provide an authorization key to access the remote repository
 *  - version: Define a target version e.g. when downloading/adding/installing or updating a package
 *
 * Examples:
 *  ./minion ske:packager --list                                    List all local packages
 *  ./minion ske:packager --search ske                              Searches the remote repositories for packages containing word 'ske'
 *  ./minion ske:packager --install seth-network/ske                Downloads package seth-network/ske
 *  ./minion ske:packager --update --version=1.2 seth-network/ske   Updating/Downgrading package ske to version 1.2
 *  ./minion ske:packager --remove --force seth-network/ske         Removes package ske even if it is currently enabled
 *
 *
 * @package    ske-packager
 * @category   Helpers
 * @author     eth4n
 * @copyright  (c) 2009-2017 eth4n
 * @license    http://seth-network.de/license
 */
class Task_Ske_Packager extends Minion_Task
{
    /**
     * @var Task_Modules
     */
    protected $modules;

    protected $_options = array(
        'search' => false,
        'list' => false,
        'download' => false,
        'install' => false,
        'enable' => false,
        'check' => false,
        'inspect' => false,
        'update' => false,
        'remove' => false,
        'force' => false,
        'delete' => false,
        'add' => false,
        'version' => false,
        'key' => '',
        'dest' => ''
    );


    protected function _execute(array $params)
    {

        $this->modules = new Task_Modules();

        if ($params['list'] === null) {
            $this->list_packages();
        } else if ($params['inspect'] === null && isset($params[1])) {
            $package = isset($params[1]) ? $params[1] : null;
            $this->inspect_package($this->find_package($package));
        } else if ($params['enable'] === null && isset($params[1]) && $params['add'] !== null) {
            $module = isset($params[1]) ? $params[1] : null;
            $this->enable_module($module);
        } else if ($params['remove'] === null && isset($params[1])) {
            $package = isset($params[1]) ? $params[1] : null;
            $delete = $params['delete'] === null ? true:false;
            $force = $params['force'] === null ? true:false;
            $this->remove_package($package, $delete, $force);
        } else if ($params['add'] != false) {
            $package = isset($params[1]) ? $params[1] : null;
            $this->add_package($package, $params['add'], $params['version']);
        } else if ($params['list'] == false && $params['add'] == false && $params['remove'] == false && $params['enable'] == false && $params['disable'] == false) {
            $this->list_modules();
        } else {
            $this->error("Unknown parameter combination. Use --help to see the task's help.");
        }
    }

    protected function add_package($name, $path, $version) {

        $n = "\n";
        $t = "\t";
        $packager = Kohana::$config->load('packager');
        $packages = $packager->as_array()['packages'];

        $path = str_replace('MODPATH/', str_replace('\\', '/', MODPATH), $path);

        if ( $name == '' ) {
            return $this->error('Please define a name for the package.');
        } else if ( strpos($name, '/') === false ) {
            return $this->error('Please define a name for the package in form "vendor/package".');
        } else  if ( $version == '' ) {
            return $this->error('Please define a version for the package.');
        } else  if ( !file_exists($path) || !is_dir($path) ) {
            return $this->error('Please define an existing directory for the package.');
        }

        if ( count(array_filter($packages, function($i) use($name, $path) {
            return $i['name'] == $name || $i['location'] == $path;
        }))) {
            $this->error("A package with this name or with this location already exists.");
            echo $n. "Use following command to list all packages:". $n . $n;
            echo $t . "./minion ske:packager --list". $n;
            return;
        }

        $packages[] = array(
            'name' => $name,
            'location' => $path,
            'version' => $version
        );

        $packager->set('packages', $packages);

        echo "Package '". $name ."' added. Use following command to list all packages:". $n . $n;
        echo $t . "./minion ske:packager --list". $n;
    }

    protected function remove_package($package, $delete=false, $force=false) {


        $n = "\n";
        $t = "\t";
        $package_data = $this->find_package($package);

        if ( $package_data === null) {
            return;
        }
        $package = $package_data;

        if ( !isset($package['location']) || !file_exists($package['location'])) {
            $this->error('Package location does not exist. Use --force instead:' . $n . $n . $t . (isset($package['location']) ? $this->path($package['location']):'n/a'));
            if ( !$force ) {
                return;
            }
        } else {

            $modules = $this->scan_for_modules($package['location']);
            $enabled_modules = Kohana::$config->load('modules');
            $registered_modules = Kohana::$config->load('installed_modules');
            $enabled_modules_in_package = array();
            $registered_modules_in_package = array();
            $max_module_name = 0;
            foreach ($modules as $module_path) {
                foreach ($registered_modules as $name => $p) {
                    if (strtolower($this->path($p)) == strtolower($this->path($module_path))) {
                        $registered_modules_in_package[$name] = $p;
                        break;
                    }
                }
                foreach ($enabled_modules as $name => $p) {
                    if (strtolower($this->path($p)) == strtolower($this->path($module_path))) {
                        $enabled_modules_in_package[$name] = $p;
                        if ( strlen($name) > $max_module_name ) {
                            $max_module_name = strlen($name);
                        }
                        break;
                    }
                }
            }
            if ( !empty($enabled_modules_in_package) ) {
                echo $n ."Package '". $package_data['name'] ."' does contain ". count($enabled_modules_in_package) ." enabled module(s):". $n . $n;
                $max_module_name += 10;
                $format = $t ."%-". $max_module_name ."s -> %s". $n;
                foreach ($enabled_modules_in_package as $name => $p) {
                    echo sprintf($format, $name, $this->path($p));
                }
                echo $n;

                if ( !$force ) {
                    $this->error("Can not remove package due to enabled modules. Use --force instead");
                    return;
                }

                // remove enabled modules from
                foreach ($enabled_modules_in_package as $name => $p) {
                    $this->modules->remove_module($name);
                }
            }
            // remove registered modules
            foreach ($registered_modules_in_package as $name => $p) {
                $this->modules->remove_module($name);
            }
        }

        // remove package from config
        echo $n . "Package '". $package['name'] ."' removed from configurations.". $n ;
        $location = $package['location'];
        $packager = Kohana::$config->load('packager');
        $packages = array_filter($packager->as_array()['packages'],
            function($i) use($package) {
                return $i['name'] != $package['name'];
            }
        );
        $packager->set('packages', null);
        $packager->set('packages', $packages);

        if ( $delete && file_exists($location)) {
            // remove from disk
            echo $n . "Deleting package's sources from path: ". $n .$n;
            echo $t . $this->path($location) . $n;
            if ( Kohana::$is_windows ){
                exec(sprintf("rd /s /q %s", escapeshellarg(realpath($location))));
            } else{
                exec(sprintf("rm -rf %s", escapeshellarg(realpath($location))));
            }
        } else if ( file_exists($location)) {
            echo $n . "Package's sources are kept in path and are not deleted:". $n . $n;
            echo $t . $this->path($location) . $n;
        }

    }

    protected function find_package($name)
    {
        $n = "\n";
        $t = "\t";
        $packager = Kohana::$config->load('packager');
        $packages_data = $packager->get('packages');

        foreach ($packages_data as $package) {
            if (trim(strtolower($package['name'])) == trim(strtolower($name))) {
                return $package;
            }
        }
        $this->error("Unknown package '" . $name . "'");
        echo "Use following command to list available packages:" . $n;
        echo $n . $t . "./minion ske:packager --list" . $n;
        return null;
    }

    protected function inspect_package(array $package = null)
    {
        if ($package === null) {
            return;
        }
        $n = "\n";
        $t = "\t";

        echo "Inspecting package " . $package['name'] . ":" . $n . $n;

        if (!file_exists($package['location'])) {
            $this->error('Package location does not exist:' . $n . $n . $this->path($package['location']));
            return;
        } else if (!is_dir($package['location'])) {
            $this->error('Package location is not a directory:' . $n . $n . $this->path($package['location']));
            return;
        }

        $modules = $this->scan_for_modules($package['location']);

        if (empty($modules)) {
            $this->warn("Package '" . $package['name'] . "' seems not to contain any Kohana modules");
        } else {
            $enabled_modules = Kohana::$config->load('modules');
            $registered_modules = Kohana::$config->load('installed_modules');

            $max_name = strlen('Module');
            foreach ($modules as $module) {
                if (strlen(basename($module)) > $max_name) {
                    $max_name = strlen(basename($module));
                }
            }
            $max_name += 5;

            $format = $t . '  %-' . $max_name . 's %-10s %-10s %s' . $n;
            echo sprintf($format, 'Module', 'Enabled', 'Registered', 'Location');
            echo "=======================================================================================================" . $n;

            foreach ($modules as $module_path) {
                $registered = false;
                $module_name = basename($module_path);
                $enabled = false;
                foreach ($registered_modules as $n => $p) {
                    if (strtolower($this->path($p)) == strtolower($this->path($module_path))) {
                        $registered = true;
                        $module_name = $n;
                        break;
                    }
                }
                foreach ($enabled_modules as $n => $p) {
                    if (strtolower($this->path($p)) == strtolower($this->path($module_path))) {
                        $enabled = $registered = true;
                        $module_name = $n;
                        break;
                    }
                }
                echo sprintf($format, $module_name, $enabled ? 'Yes' : 'No', $registered ? 'Yes' : 'No', $this->path($module_path));
            }
        }
    }

    protected function scan_for_modules($dir)
    {
        $dir = rtrim($dir, "/\\") . '/';
        $r = array();

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file{0} == ".") {
                        continue;
                    }
                    if (is_dir($dir . $file)) {
                        if (file_exists($dir . $file . DIRECTORY_SEPARATOR . 'init' . EXT)
                            || file_exists($dir . $file . DIRECTORY_SEPARATOR . 'classes')
                        ) {
                            $r[] = str_replace(array('\\'), '/', $dir . $file);
                        } else {
                            $r = array_merge($r, self::scan_for_modules($dir . $file . DIRECTORY_SEPARATOR));
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $r;
    }


    protected function list_packages()
    {
        $n = "\n";
        $t = "\t";

        $packager = Kohana::$config->load('packager');

        echo "Local packages:" . $n . $n;
        $packages_data = $packager->get('packages');
        $packages = array();
        $max_package_name = strlen('Package');
        $max_version = strlen('Version');
        foreach ($packages_data as $package) {
            if (strlen($package['name']) > $max_package_name) {
                $max_package_name = strlen($package['name']);
            }
            if (strlen($package['version']) > $max_version) {
                $max_version = strlen($package['version']);
            }
            $packages[$package['name']] = $package;
        }
        $max_package_name += 10;
        $max_version += 5;
        echo $n;
        $format = $t . '  %-' . $max_package_name . 's %-' . $max_version . 's %-10s %-10s %s' . $n;
        echo sprintf($format, 'Package', 'Version', 'Auth', 'Modules', 'Location');
        echo "=======================================================================================================" . $n;
        foreach ($packages as $name => $data) {
            $auth = isset($data['key']) && $data['key'] != '';
            echo sprintf($format, $name, $data['version'], $auth ? 'Yes' : 'No', count($this->scan_for_modules($data['location'])), $this->path($data['location']));
        }

        echo $n;
        echo "Use following command to list a package's modules, add or remove a package:" . $n . $n;
        echo $t . "./minion ske:packager --inspect PACKAGE" . $n;
        echo  $t . "./minion ske:packager --add=LOCATION --version=VERSION PACKAGE" . $n;
        echo  $t . "./minion ske:packager --remove PACKAGE (--force) (--delete)" . $n;
    }

    protected function error($msg, $new_line = "\n")
    {
        echo Minion_CLI::color("# Error: " . $msg . $new_line, 'red');
        return false;
    }

    protected function warn($msg, $new_line = "\n")
    {
        echo Minion_CLI::color("# Warning: " . $msg . $new_line, 'yellow');
    }


    protected function path($path, $substitute_constant = true)
    {
        $path = str_replace('\\', '/', $path);
        if ($substitute_constant) {
            return str_replace(array(
                str_replace('\\', '/', APPPATH),
                str_replace('\\', '/', MODPATH),
                str_replace('\\', '/', SYSPATH),
                str_replace('\\', '/', DOCROOT)
            ), array(
                'APPPATH/',
                'MODPATH/',
                'SYSPATH/',
                'DOCROOT/'
            ), $path);
        } else {
            return str_replace(str_replace('\\', '/', DOCROOT), 'DOCROOT/', $path);
        }
    }
}