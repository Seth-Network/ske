<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Task to validate the installation of Kohana
 *
 * Syntax:
 *  ./minion kohana:install
 *
 *
 *
 * @package    ske
 * @category   Helpers
 * @author     eth4n
 * @copyright  (c) 2009-2017 eth4n
 */
class Task_Kohana_Install extends Minion_Task {

    protected $_options = array(
    );

    protected function _execute(array $params) {
        $n = "\n";
        $t = "\t";

        echo "Performing required checks for Kohana/Koseven:". $n;
        echo "===================================================". $n . $n;
        $fails = $this->print_checks(array(
            'PHP short tags enabled' => ((bool) ini_get('short_open_tag')),
            'PHP version 7+' => array((version_compare(PHP_VERSION, '7', '>=')), PHP_VERSION, PHP_VERSION),
            'System path exists and is directory' => array((is_dir(SYSPATH) AND is_file(SYSPATH.'classes/Kohana'.EXT)), $this->path(SYSPATH, false)),
            'Application path exists and is directory' => array((is_dir(APPPATH) AND is_file(APPPATH.'bootstrap'.EXT)), $this->path(APPPATH, false)),
            'Cache directory exists' => array((is_dir(APPPATH) AND is_dir(APPPATH.'cache')), $this->path(APPPATH.'cache')),
            'Cache directory is writable' => array(is_writable(APPPATH.'cache'), "Yes", "No"),
            'Log directory exists' => array(is_dir(APPPATH) AND is_dir(APPPATH.'logs'), $this->path(APPPATH.'logs')),
            'Log directory is writable' => array(is_writable(APPPATH.'logs'), "Yes", "No"),
            'PCRE is compiled with UTF-8 support' => array(@preg_match('/^.$/u', 'ñ'), "Yes", "No"),
            'PCRE is compiled with unicode support' => array(@preg_match('/^\pL$/u', 'ñ'), "Yes", "No"),
            'SPL support is loaded' => array(function_exists('spl_autoload_register'), "Yes", "Not available"),
            'Reflection is loaded and available' => array(class_exists('ReflectionClass'), "Yes", "Not available"),
            'Filter extension is loaded' => array(function_exists('filter_list'), "Yes", "Not loaded"),
            'IconV extension is loaded' => array(extension_loaded('iconv'), "Yes", "Not loaded"),
            'MbString does not overload native functions' => array(!extension_loaded('mbstring')|| (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING), ( extension_loaded('mbstring') ? "Yes":"Not loaded"), "mbstring does overloading"),
            'Library ctype is enabled' => function_exists('ctype_digit'),

        ));

        echo $n . "Performing optional checks for Kohana/Koseven:". $n;
        echo "===================================================". $n . $n;
        $warnings = $this->print_checks(array(
            'PECL Http extension' => extension_loaded('http'),
            'cURL extension' => extension_loaded('curl'),
            'mcrypt extension' => extension_loaded('mcrypt'),
            'GD library' => function_exists('gd_info'),
            'MySQLi extension to support MySQL databases' => array(function_exists('mysqli_connect'), "Available", "Not available"),
            'PDO to support other databases' => array(class_exists('PDO'), "Available", "Not available"),
            'install.php removed' => array(!is_file('./public/install.php'), 'File removed', $this->path(realpath('./public/install.php'), false)),
        ), "green", "yellow");


        echo $n . "Summary: ". $n;
        echo "===================================================". $n . $n;
        if ( $fails > 0 ) {
            echo Minion_CLI::color($t. "✘ Kohana/Koseven may not work correctly with your environment.". $n, "red");
        } else if ( $warnings > 0 ) {
            echo Minion_CLI::color( $t. "✔ Your environment passed all requirements but you may miss some optional functionality. ". $n, "yellow");

        } else {
            echo Minion_CLI::color( $t. "✔ Your environment passed all requirements.". $n,"green");
        }
    }

    protected function path($path, $substitute_constant=true) {
        $path = str_replace('\\', '/', $path);
        if ( $substitute_constant ) {
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


    protected function print_checks(array $checks, $pass_color='green', $failed_color='red') {
        $fails = 0;
        $max_text_length = 0;
        foreach ( $checks as $text => $val ) {
            if ( strlen($text) > $max_text_length ) {
                $max_text_length = strlen($text);
            }
        }

        $max_text_length += 15;
        foreach ( $checks as $text => $val ) {
            $val = ( is_array($val) ) ? $val:array($val, 'Enabled', 'Disabled');
            $pass = $val[0];
            $value = $pass === null || $pass || !isset($val[2]) ? $val[1]:$val[2];

            $fails += $pass === null || $pass ? 0:1;
            $this->print_check($text, $max_text_length, $pass, $value, $pass_color, $failed_color);
        }

        return $fails;
    }

    protected function print_check($text,  $padding_size, $pass, $value, $pass_color, $failed_color) {
        $n = "\n";
        $t = "\t";
        if ( $pass === null) {
            echo sprintf($t. '  %s %s'. $n, $text, $value);
        } else if ( !$pass ) {
            echo Minion_CLI::color(sprintf($t. '✘ %-'.  $padding_size.'s %s'. $n, $text, $value), $failed_color);
        } else {
            echo Minion_CLI::color(sprintf($t. '✔ %-'.  $padding_size.'s %s'. $n, $text, $value), $pass_color);
        }
    }

}
