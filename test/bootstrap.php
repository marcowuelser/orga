<?php
declare(strict_types=1);

// Settings to make all errors more obvious during testing
error_reporting(-1);
ini_set('display_errors', "1");
ini_set('display_startup_errors', "1");
date_default_timezone_set('UTC');

define('PROJECT_ROOT', realpath(__DIR__ . '/../'));

//set_include_path(get_include_path() . PATH_SEPARATOR . './src/');

// Perform autoload of slim classes
require_once(PROJECT_ROOT.'/src/vendor/autoload.php');

// Autoload classes
spl_autoload_register(function ($classname)
{
    require (PROJECT_ROOT."/src/classes/" . $classname . ".php");
});

require_once(PROJECT_ROOT.'/src/util/util.php');