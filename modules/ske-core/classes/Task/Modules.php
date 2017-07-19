<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Task to administrate the configuration of modules.
 *
 * Syntax:
 *  ./minion modules [--list] [--add=PATH] [--remove] [--enable] [--disable [--missing]] [module]
 *
 * Available actions are
 *  - list: List all modules
 *  - add: Adds a new module with the modules path. You may use 'MODPATH' to reference the configure module's path. May be combined with '--enable' to directly enable the module
 *  - remove: Removes an existing module from the configuration. Can be combined with '--missing' to delete all modules whos path does not exists. Warning: There will be no further check.
 *  - enable: Enables an existing module. Can be combined with '--add' to directly enable a new module
 *  - disable: Disables an existing module.
 *
 *
 * Examples:
 *  ./minion modules --list                             List all modules
 *  ./minion modules --add=MODPATH/ske --enable ske     Add module named 'ske' with part 'MODPATH/ske'. Constant MODPATH will be replaced with actual module path. The module will directly be enabled.
 *  ./minion modules --disable ske                      Disables the ske module
 *  ./minion modules --remove ske                       Removes module ske
 *  ./minion modules --remove --missing                 Removes all missing modules
 *
 *
 * @package    ske
 * @category   Helpers
 * @author     eth4n
 * @copyright  (c) 2009-2017 eth4n
 * @license    http://seth-network.de/license
 */
class Task_Modules extends Minion_Task {

    protected $_options = array(
        'list' => false,
        'add' => false,
        'remove' => false,
        'enable' => false,
        'disable' => false,
        'missing' => false
    );

    protected function error($msg, $new_line = "\n") {
        echo Minion_CLI::color("# Error: " . $msg . $new_line, 'red');
        return false;
    }

    protected function warn($msg, $new_line = "\n") {
        echo Minion_CLI::color("# Warning: " . $msg . $new_line, 'yellow');
    }

    protected function _execute(array $params) {
        $n = "\n";
        $t = "\t";

        $modpath = str_replace('\\', '/', MODPATH);
        echo "MODPATH is set to:" . $n . $n;
        echo $t . $modpath . $n . $n;

        if ($params['list'] === null) {
            $this->list_modules();
        } else if ($params['disable'] === null && isset($params[1])) {
            $module = isset($params[1]) ? $params[1] : null;
            $this->disable_module($module);
        } else if ($params['enable'] === null && isset($params[1]) && $params['add'] !== null) {
            $module = isset($params[1]) ? $params[1] : null;
            $this->enable_module($module);
        } else if ($params['remove'] === null && isset($params[1])) {
            $module = isset($params[1]) ? $params[1] : null;
            $this->remove_module($module);
        } else if ($params['remove'] === null && $params['missing'] === null) {
            $this->remove_missing_modules();
        } else if ($params['add'] != false) {
            $module = isset($params[1]) ? $params[1] : null;
            $enable = $params['enable'] === null;
            $this->add_module($module, $params['add'], $enable);
        } else if ( $params['list'] == false && $params['add'] == false && $params['remove'] == false && $params['enable'] == false && $params['disable'] == false ) {
            $this->list_modules();
        } else {
            $this->error("Unknown parameter combination. Use --help to see the task's help.");
        }
    }

    protected function add_module($module, $path, $enable = false) {
        $n = "\n";
        $t = "\t";

        $path = str_replace('MODPATH/', str_replace('\\', '/', MODPATH), $path);

        if (!file_exists($path)) {
            return $this->error("Cannot add module: Path '" . $this->modpath($path) . "' does not exist.");
        } else if (!is_dir($path)) {
            return $this->error("Cannot add module: Path '" . $this->modpath($path) . "' is not a directory.");
        }

        if ($enable) {
            $active = Kohana::$config->load('modules');

            if ($active->get($module) !== null) {
                return $this->error("Cannot add module: A loaded module with this name already exists.");
            }
            $active->set($module, $path);
            echo "Module added successfully: " . $module . " -> " . $this->modpath($path) . $n . $n;
            echo "The module is already enabled and will be loaded. Use following command to disable module:" . $n . $n;
            echo $t . "./minion modules --disable " . $module . $n;
        } else {
            $available = Kohana::$config->load('installed_modules');

            if ($available->get($module) !== null) {
                $this->error("Cannot add module: A disabled module with this name already exists.");
                echo $n;
                echo "Use following command to enable module:" . $n . $n;
                echo $t . "./minion modules --enable " . $module . $n;
                return false;
            }
            $available->set($module, $this->modpath($path, false));
            echo "Module added successfully: " . $module . " -> " . $this->modpath($path) . $n . $n;
            echo "The module is not yet enabled and will not be loaded. Use following command to enable module:" . $n . $n;
            echo $t . "./minion modules --enable " . $module . $n;
        }
    }

    protected function modpath($path, $substitute_constant=true) {
        $modpath = str_replace('\\', '/', MODPATH);
        $path = str_replace('\\', '/', $path);
        if ( $substitute_constant ) {
            return str_replace($modpath, 'MODPATH/', $path);
        } else {
            return $path;
        }
    }

    protected function remove_missing_modules() {
        $n = "\n";
        $available = Kohana::$config->load('installed_modules');

        foreach ($available->as_array() as $name => $path) {
            if (!file_exists($path) || !is_dir($path)) {
                $available->set($name, null);
                echo "Missing module '" . $name . " -> " . $this->modpath($path) . "' has been removed." . $n;
            }
        }
    }

    protected function remove_module($module) {
        $n = "\n";
        $t = "\t";

        if ($this->disable_module($module)) {
            $available = Kohana::$config->load('installed_modules');

            if (isset($available->as_array()[$module])) {
                $path = $available->get($module);
                $available->set($module, null);
                echo "Module '" . $module . "' removed. Modules path has been: " . $n . $n;
                echo $t . $this->modpath($path) . $n;
            }
        }
    }

    protected function enable_module($module, $path = '') {
        $n = "\n";
        $t = "\t";
        $active = Kohana::$config->load('modules');
        $available = Kohana::$config->load('installed_modules');

        if (!isset($active->as_array()[$module]) && !isset($available->as_array()[$module])) {
            $this->error("Unknown module '" . $module . "'.");
            return;
        } else if (isset($available->as_array()[$module])) {
            $path = $available->get($module, '');
        } else if (isset($active->as_array()[$module])) {
            echo "Module '" . $module . "' is already active." . $n . $n;
            return;
        }

        if (!file_exists($path) || !is_dir($path)) {
            $this->error("Module's path is not a directory or does not exists, module can not be loaded:" . $n . $n . $t . $this->modpath($path));
        } else {
            $available->set($module, null);
            $active->set($module, $this->modpath($path, false));

            echo "Module '" . $module . " -> " . $this->modpath($path) . "' is now active." . $n . $n;
        }

    }

    protected function disable_module($module, $path = '') {
        $n = "\n";
        $active = Kohana::$config->load('modules');
        $available = Kohana::$config->load('installed_modules');

        if (!isset($active->as_array()[$module]) && !isset($available->as_array()[$module])) {
            $this->error("Unknown module '" . $module . "'.");
            return false;
        } else if (isset($active->as_array()[$module])) {
            $path = $active->get($module, '');
            $active->set($module, null);
            echo "Module '" . $module . " -> " . $this->modpath($path) . "' is now disabled." . $n . $n;
        }
        if (!isset($available->as_array()[$module])) {
            $available->set($module, $this->modpath($path, false));
        } else {
            echo "Module '" . $module . "' is already disabled." . $n . $n;
        }
        if ($path != '' && (!file_exists($path) || !is_dir($path))) {
            $this->warn("Path '" . $this->modpath($path) . "' does not exists or is not a directory.");
        }
        return true;
    }

    protected function list_modules() {
        $n = "\n";
        $t = "\t";
        $print_asterisk = false;

        $active = Kohana::$config->load('modules');
        $available = Kohana::$config->load('installed_modules');

        echo "Active and loaded modules:" . $n . $n;
        $active_modules = $active->as_array();
        ksort($active_modules);
        foreach ($active_modules as $name => $path) {
            $path_exists = file_exists($path);

            if ($path_exists) {
                echo Minion_CLI::color(sprintf($t . "%-20s -> %s" . $n, $name, $this->modpath($path)), 'green');
            } else {
                $print_asterisk = true;
                echo Minion_CLI::color(sprintf($t . "%-20s -> %s *" .$n, $name, $this->modpath($path)), 'red');
            }

        }
        echo $n;
        echo "Disabled modules:" . $n . $n;
        $available_modules = $available->as_array();
        ksort($available_modules);
        foreach ($available_modules as $name => $path) {
            if (!isset($active->as_array[$name])) {
                $path_exists = file_exists($path);

                if ($path_exists) {
                    echo sprintf($t . "%-20s -> %s" . $n, $name, $this->modpath($path));
                } else {
                    $print_asterisk = true;
                    echo Minion_CLI::color(sprintf($t . "%-20s -> %s *" .$n, $name, $this->modpath($path)), 'red');
                }
            }
        }
        $sample_enable = ( empty($available_modules) ) ? 'MODULE':current(array_keys($available_modules));
        $sample_disable = ( empty($active_modules) ) ? 'MODULE':current(array_keys($active_modules));

        echo $n .'Use following command to enable or disable a module:'. $n . $n;
        echo $t . "./minion modules --enable ". $sample_enable . $n;
        echo $t . "./minion modules --disable ". $sample_disable . $n;
        if ( $print_asterisk ) {
            echo $n . $n ;
            echo "* This module's path does not exists" . $n;
        }
    }
}
