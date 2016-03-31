<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Middleware\HttpBasicAuthentication\AuthenticatorInterface;

set_include_path(get_include_path() . PATH_SEPARATOR . '../../../');

// Perform autoload of slim classes
require_once('vendor/autoload.php');
require_once('../../../config.php');
include_once('util/util.php');

// Adjust url for subdirectory. This is required for slim routing to work peroperly
// $_SERVER['REQUEST_URI'] = str_replace('/api/v1/', '/', $_SERVER['REQUEST_URI']);

// Autoload classes
spl_autoload_register(function ($classname)
{
    require ("../../../classes/" . $classname . ".php");
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
    return $pdo;
};


// Setup middleware

if ($config["authorizationOn"])
{
    $app->add(new TokenAuthenticationMiddleware(
        $container['db'],
        [
            "exclude" => "\/user\/login",
            "realm" => "ORGA API"
        ]
    ));
}

// Setup routes
$app->get('/', function () use($app)
{
    echo "Welcome to Slim 3.0 based ORGA API - Version 1.0";
});


// System

$app->get('/system', function (Request $request, Response $response)
{
    $this->logger->addInfo("Get system information (UNIMPLEMENTED)");
    //$mapper = new UserMapper($this->db);
    //$users = $mapper->getUsers();
    $system = array();
    return responseWithJson($response, array("system" => $system), 200);
});


// User Authorization

$app->get('/system/user/login', function (Request $request, Response $response)
{
    // Only public endpoint, used to log in.
    $username = false;
    $password = false;
    $server_params = $request->getServerParams();

    /* If using PHP in CGI mode. */
    if (preg_match("/Basic\s+(.*)$/i", $server_params["HTTP_AUTHORIZATION"], $matches))
    {
       list($username, $password) = explode(":", base64_decode($matches[1]));
    }
    else
    {
        if (isset($server_params["PHP_AUTH_USER"])) {
           $username = $server_params["PHP_AUTH_USER"];
        }
        if (isset($server_params["PHP_AUTH_PW"])) {
           $password = $server_params["PHP_AUTH_PW"];
        }
    }
    if (!$username || !$password)
    {
        return $response->withStatus(401);
    }

    $this->logger->addInfo("Login user $username");
    $mapper = new UserMapper($this->db);
    $data = $mapper->loginUser($username, $password);
    if (isErrorResponse($data))
    {
        return responseWithJson($response, $data, 401);
    }
    return responseWithJson($response, $data);
});

$app->get('/system/user/logoff', function (Request $request, Response $response)
{
     $response = $response->withHeader("WWW-Authenticate", 'Basic realm="Protected"');
     $response = $response->withStatus(401);
     return $response;
});


// User Management

$app->get('/system/users', function (Request $request, Response $response)
{
    $this->logger->addInfo("Get user list");
    $mapper = new UserMapper($this->db);
    $users = $mapper->getUsers();
    return responseWithJson($response, array("users" => $users));
});


// Ruleset Management

$app->get('/rulesets', function (Request $request, Response $response)
{
    $mapper = new RulesetMapper($this);
    $rulesets = $mapper->selectAll();
    return responseWithJson($response, array("rulesets" => $rulesets));
});

$app->post('/ruleset', function (Request $request, Response $response)
{
    $data = $request->getParsedBody();
    $mapper = new RulesetMapper($this);
    $ruleset = $mapper->insert($data);
    return responseWithJson($response, array("ruleset" => $ruleset), 201);
});

$app->get('/ruleset/{id}', function (Request $request, Response $response, $args)
{
    $id = (int)$args['id'];
    $mapper = new RulesetMapper($this);
    $ruleset = $mapper->selectById($id);
    return responseWithJson($response, array ("ruleset" => $ruleset));
});

$app->put('/ruleset/{id}', function (Request $request, Response $response, $args)
{
    $id = (int)$args['id'];
    $data = $request->getParsedBody();
    $mapper = new RulesetMapper($this);
    $ruleset = $mapper->update($id, $data);
    return responseWithJson($response, array("ruleset" => $ruleset));
});

$app->patch('/ruleset/{id}', function (Request $request, Response $response, $args)
{
    $id = (int)$args['id'];
    $data = $request->getParsedBody();
    $mapper = new RulesetMapper($this);
    $ruleset = $mapper->patch($id, $data);
    return responseWithJson($response, array("ruleset" => $ruleset));
});

$app->delete('/ruleset/{id}', function (Request $request, Response $response, $args)
{
    $id = (int)$args['id'];
    $mapper = new RulesetMapper($this);
    $ruleset = $mapper->delete($id);
    return responseWithJson($response, $ruleset);
});


// Game Management

$app->get('/games', function (Request $request, Response $response)
{
    $this->logger->addInfo("Get games list (UNIMPLEMENTED)");
    // $mapper = new GameMapper($this->db);
    $games = array(); // $mapper->getGames();
    return responseWithJson($response, array("games" => $games));
});



// Run App
$app->run();

?>
