<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

set_include_path(get_include_path() . PATH_SEPARATOR . '../../../');

// Perform autoload of slim classes
require_once('vendor/autoload.php');

// Project
require_once('../../../config.php');
require_once('version.php');
include_once('util/util.php');

// Local
include_once('./routes.php');

mb_language("uni");
mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8");
setlocale (LC_ALL, 'de_CH.utf8');


// Adjust url for subdirectory. This is required for slim routing to work properly 
// $_SERVER['REQUEST_URI'] = str_replace('/api/v1/', '/', $_SERVER['REQUEST_URI']);

// Autoload classes
spl_autoload_register(function ($classname)
{
    require ("classes/" . $classname . ".php");
});

$config = getConfig();

// Create App
$app = new \Slim\App(
[
    "settings" => $config
]);

// Create containers
$container = $app->getContainer();

// Logger
$container['logger'] = function($c)
{
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

// Database access
$container['db'] = function ($c)
{
    $db = $c['settings']['db'];
    $pdo = new PDO(
         "mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
         $db['user'],
         $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES `utf8`");
    $pdo->exec("SET CHARACTER SET utf8");
    return $pdo;
};

// Authorization
$container['auth'] = function($c)
{
    $auth = new Authorization();
    return $auth;
};

// Setup middleware
$tokenAuth = new TokenAuthenticationMiddleware(
    $container->get('auth'),
    $container->get('db'),
    $container->get('logger'),
    [
        "exclude" => "\/user\/login"
    ]
);

// Enable token authorization for all routes except for ../user/login.
if ($config["authenticationOn"])
{
    $app->add($tokenAuth);
}

injectRoutes($app, $config);

// CORS
$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
    "allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$cors = new \CorsSlim\CorsSlim($corsOptions);
$app->add($cors);

// Run App
$app->run();

?>
