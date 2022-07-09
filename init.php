<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Moscow');

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

define('ROOT', __DIR__);
define('START_TIME', microtime(true));

set_include_path(get_include_path().PATH_SEPARATOR.ROOT);

spl_autoload_register(function($class) {
    if (str_ends_with($class, 'Exception')) {
        $path = ROOT.'/engine/exceptions/'.$class.'.php';
    } else if (in_array($class, ['MySQLConnection', 'SQLiteConnection', 'CommonDatabase'])) {
        $path = ROOT.'/engine/database/'.$class.'.php';
    } else if (str_starts_with($class, 'handler\\')) {
        $path = ROOT.'/'.str_replace('\\', '/', $class).'.php';
    }

    if (isset($path)) {
        if (!is_file($path))
            return;
    } else {
        foreach (['engine', 'lib', 'model'] as $dir) {
            if (is_file($path = ROOT.'/'.$dir.'/'.$class.'.php'))
                break;
        }
    }

    require_once $path;
});

$config = require_once 'config.php';
if (file_exists(ROOT.'/config-local.php')) {
    $config = array_replace($config, require 'config-local.php');
}

// turn off errors output on production domains

require_once 'functions.php';

if (PHP_SAPI == 'cli') {
    $_SERVER['HTTP_HOST'] = $config['domain'];
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
} else {
    if (array_key_exists('HTTP_X_REAL_IP', $_SERVER))
        $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
}

if (!$config['is_dev']) {
    if (file_exists(ROOT.'/config-static.php'))
        $config['static'] = require_once 'config-static.php';
    else
        die('confic-static.php not found');
}

if (!$config['is_dev']) {
    error_reporting(0);
    ini_set('display_errors', 0);
}

logging::setLogFile($config['log_file']);
logging::enable();

require 'vendor/autoload.php';
