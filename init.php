<?php

$application = 'application';

// Set the full path to the docroot
define('DOCROOT', realpath('./').DIRECTORY_SEPARATOR);

if ( !file_exists(DOCROOT . $application) ) {
    echo 'Creating directory '. DOCROOT . $application ."\n";
    mkdir(DOCROOT . $application);
} else {
    echo "Application directory '". DOCROOT . $application ."' already exists\n";
}
define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);

foreach ( array('cache', 'classes', 'config', 'l18n',  'messages','logs', 'view') as $dir ) {
    if ( !file_exists(APPPATH . $dir) ) {
        echo 'Creating directory '. APPPATH . $dir ."\n";
        mkdir(APPPATH . $dir,0777, true);
    }else {
        echo "Directory '". APPPATH . $dir ."' already exists\n";
    }
}

$ske_home = dirname(__FILE__) . DIRECTORY_SEPARATOR .'modules/ske/';
$files_to_patch = include($ske_home .'assets/data/ske_files.php');

foreach ( $files_to_patch as $src => $dest ) {
    echo "Copy '". $ske_home . $src ."' -> '". $dest ."'\n";
    if ( !file_exists(dirname($dest)) ) {
        echo 'Creating directory '. dirname($dest) ."\n";
        mkdir(dirname($dest), 0777, true);
    }
    copy($ske_home . $src, $dest);
}
