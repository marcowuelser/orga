<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once('version.php');
include_once('enum.php');
include_once('util/error.php');

function injectRoutes($app)
{
    $container = $app->getContainer();

    // Authorization
    $requireAdmin = new UserAuthorizationMiddleware(
        $container->get('auth'),
        Match::EqualOrBetter,
        UserRole::RoleAdmin);

    $requireAuthor = new UserAuthorizationMiddleware(
        $container->get('auth'),
        Match::EqualOrBetter,
        UserRole::RoleAuthor);

    // Setup routes

    $app->get('/', function () use($app)
    {
        echo "Welcome to the Slim 3.0 based ".Constants::ORGA_SERVER_NAME_FULL;
    });


    // System

    $app->get('/system', function (Request $request, Response $response)
    {
        $mapper = new SystemMapper($this->db, $this->logger);
        $system = $mapper->selectAll();
        return responseWithJson($response, $system, 200);
    });

    $app->patch('/system', function (Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $mapper = new SystemMapper($this->db, $this->logger);
        $system = $mapper->patch(1, $data);
        return responseWithJson($response, $system);
    })->add($requireAdmin);


    // User Authorization

    $app->get('/system/user/login', function (Request $request, Response $response)
    {
        // Only public endpoint, used to log in.
        $username = false;
        $password = false;
        if (!Authorization::parseCredentials($request, $username, $password))
        {
            return responseWithJsonError($response, 3001, "No credentials");
        }

        $this->logger->addInfo("Login user $username");
        $mapper = new UserMapper($this->db, $this->logger);
        $data = $this->auth->loginUser($username, $password, $mapper);
        return responseWithJson($response, $data);
    });

    $app->get('/system/user/logoff', function (Request $request, Response $response)
    {
        $this->logger->addInfo("Logoff user");
        $mapper = new UserMapper($this->db, $this->logger);
        $data = $this->auth->logoutCurrentUser($mapper);
        $response = $response->withStatus(204);
        return $response;
    });

    $app->get('/system/user', function (Request $request, Response $response)
    {
        $id = $this->auth->getCurrentUserId();
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->selectById($id);
        return responseWithJson($response, $users);
    });

    $app->patch('/system/user', function (Request $request, Response $response)
    {
        $id = $this->auth->getCurrentUserId();
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $user = $this->auth->patchCurrentUser($data, $mapper);
        return responseWithJson($response, $user);
    });

    $app->put('/system/user/password', function (Request $request, Response $response)
    {
        $id = $this->auth->getCurrentUserId();
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $user = $this->auth->changeCurrentUserPassword($data, $mapper);
        return responseWithJson($response, $user);
    });

    // User Management

    $app->get('/system/users', function (Request $request, Response $response)
    {
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->selectAll();
        return responseWithJson($response, $users);
    })->add($requireAdmin);

    $app->post('/system/user', function (Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->insert($data);
        return responseWithJson($response, $users);
    })->add($requireAdmin);

    $app->get('/system/user/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->selectById($id);
        return responseWithJson($response, $users);
    })->add($requireAdmin);

    $app->patch('/system/user/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->patch($id, $data);
        return responseWithJson($response, $users);
    })->add($requireAdmin);

    $app->put('/system/user/{id}/password', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->setPassword($id, $data);
        return responseWithJson($response, $users);
    })->add($requireAdmin);

    $app->get('/system/users/roles', function (Request $request, Response $response)
    {
        return responseWithJson($response, UserRole::toAssocArray());
    });

    // Ruleset Management

    $app->get('/rulesets', function (Request $request, Response $response)
    {
        $mapper = new RulesetMapper($this->db, $this->logger);
        $rulesets = $mapper->selectAll();
        return responseWithJson($response, $rulesets);
    });

    $app->post('/ruleset', function (Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $mapper = new RulesetMapper($this->db, $this->logger);
        $ruleset = $mapper->insert($data);
        return responseWithJson($response, $ruleset, 201);
    })->add($requireAuthor);

    $app->get('/ruleset/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $mapper = new RulesetMapper($this->db, $this->logger);
        $ruleset = $mapper->selectById($id);
        return responseWithJson($response, $ruleset);
    });

    $app->put('/ruleset/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new RulesetMapper($this->db, $this->logger);
        $ruleset = $mapper->update($id, $data);
        return responseWithJson($response, $ruleset);
    })->add($requireAuthor);

    $app->patch('/ruleset/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new RulesetMapper($this->db, $this->logger);
        $ruleset = $mapper->patch($id, $data);
        return responseWithJson($response, $ruleset);
    })->add($requireAuthor);

    $app->delete('/ruleset/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $mapper = new RulesetMapper($this->db, $this->logger);
        $ruleset = $mapper->delete($id);
        return responseWithJson($response, $ruleset);
    })->add($requireAdmin);


    // Game Management

    $app->get('/games', function (Request $request, Response $response)
    {
        $this->logger->addInfo("Get games list (UNIMPLEMENTED)");
        // $mapper = new GameMapper($this->db);
        $games = array(); // $mapper->getGames();
        return responseWithJson($response, $games);
    });

}

?>