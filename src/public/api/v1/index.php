<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

set_include_path(get_include_path() . PATH_SEPARATOR . '../../../');

// Perform autoload of slim classes
require_once('vendor/autoload.php');

// Project
require_once('../../../config.php');
require_once('version.php');
require_once('util/util.php');

// Local
require_once('./routesSystem.php');
require_once('./routesUser.php');
require_once('./routesMessage.php');
require_once('./routesBoard.php');
require_once('./routesGame.php');
require_once('./routesPlayer.php');
require_once('./routesCharacter.php');
require_once('./routesRuleset.php');
require_once('./routesScope.php');

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
DbMapperAbs::setBaseURI($config["baseURI"]);

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

// Error list
$container['errorList'] = function ($c)
{
    $errorCodeList = new \ORGA\Error\ErrorCodeList();
    return $errorCodeList;
};

// Error handler
$container['errorHandler'] = function ($c)
{
    return function ($request, $response, $exception) use ($c)
    {
        $errorCodeList = $c['errorList'];
        $logger = $c['logger'];
        $error = $errorCodeList->getData($exception->getCode());
        $data = $error->toResponseData($exception->getMessage());
        $response = responseWithJson($response, $data);
        $logger->addError(
            "Exception: ".$error->code." (".$error->text."): '".$exception->getMessage()."' returned HTTP status code ".$error->httpStatusCode);
        return $response;
    };
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

// ScopeService
$container['scope'] = function($c)
{
    $scope = new ScopeService();
    return $scope;
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

$app->get('/', function (Request $request, Response $response) use($app)
{
    return responseWithJson($response, array(
        "message" =>
        "Welcome to the Slim 3.0 based ".Constants::ORGA_SERVER_NAME_FULL));
});

injectRoutesSystem($app, $config);
injectRoutesUser($app, $config);
injectRoutesMessage($app, $config);
injectRoutesBoard($app, $config);
injectRoutesGame($app, $config);
injectRoutesPlayer($app, $config);
injectRoutesCharacter($app, $config);
injectRoutesRuleset($app, $config);
injectRoutesScope($app, $config);

// CORS
$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array(
        "Content-Type",
        "X-Requested-With",
        "X-authentication",
        "X-client"),
    "allowMethods" => array(
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'OPTIONS')
);
$cors = new \CorsSlim\CorsSlim($corsOptions);
$app->add($cors);

// Run App
$app->run();

?>
