<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once('version.php');
include_once('util/error.php');

function injectRoutes($app, $config)
{
    $container = $app->getContainer();

    // Enable token authorization for all routes except for ../user/login.
    $authOn = $config["authenticationOn"];

    // Authorization
    $requireAdmin = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleAdmin : UserRoleFlag::RoleGuest);

    $requireAuthor = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleAuthor : UserRoleFlag::RoleGuest);

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
        return responseWithJson($response, UserRoleFlag::toAssocArray());
    });

    // Messages

    $app->get('/messages', function (Request $request, Response $response)
    {
        // Endpoint gets all messages in the system.
        // Only returns messages the current user is allowed to see.

        throw(new Exception("Unimplemented", 2002));

        $mapper = new MessageMapper($this->db, $this->logger);
        $rulesets = $mapper->selectAll();
        return responseWithJson($response, $rulesets);
    });

    $app->get('/messages/inbox', function (Request $request, Response $response)
    {
        // Endpoint gets all incomming messages for current user.
        // If no scope is passed, user is used.
        // If scope is player, the player id must be passed as reference
        // If the scope is character, the character id must be passed as reference

        $userId = $this->auth->getCurrentUserId();
        $scopeId = ScopeEnum::ScopeUser;
        $reference = -1;
        $showInactive = false;

        // TODO check if only the supported parameters are passed and the values make sense
        $allGetVars = $request->getQueryParams();
        foreach($allGetVars as $key => $param)
        {
            //GET parameters list
            if ($key == "scope")
            {
                $scopeId = $param;
            }
            if ($key == "reference")
            {
                $reference = $param;
            }
            if ($key == "show_deleted")
            {
                $showInactive = true;
            }
        }

        // TODO Check if the current user is allowed to get the selected informations
        $where = array("scope_id" => $scopeId);
        if (!$showInactive)
        {
             $where["active"] = 1;
        }
        if ($scopeId == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["destination_id"] = $userId;
        }
        if ($scopeId == ScopeEnum::ScopePlayer)
        {
            $gameId = $reference;
            $playerId = $userId; // TODO get player id from userId and gameId
            $where["game_id"] = $gameId;
            $where["destination_id"] = $playerId;
        }
        if ($scopeId == ScopeEnum::ScopeCharacter)
        {
            $characterId = $reference;
            $gameId = 1; // Get gameId from characterId
            $where["game_id"] = $gameId;
            $where["destination_id"] = $characterId;
        }
        print_r2($where);
        $mapper = new MessageMapper($this->db, $this->logger);
        $exclude = array('content');
        $order = array("created" => false);
        $messages = $mapper->select($where, $order, 2, $exclude);
        return responseWithJson($response, $messages);
    });

    $app->get('/messages/outbox', function (Request $request, Response $response)
    {
        // Endpoint gets all sent messages by the current user.
        // If no scope is passed, user is used.
        // If scope is player, the player id must be passed as reference
        // If the scope is character, the character id must be passed as reference
        $userId = $this->auth->getCurrentUserId();
        $scopeId = ScopeEnum::ScopeUser;
        $reference = -1;
        $showActive = true;

        // TODO check if only the supported parameters are passed and the values make sense
        $allGetVars = $request->getQueryParams();
        foreach($allGetVars as $key => $param)
        {
            //GET parameters list
            if ($key == "scope")
            {
                $scopeId = $param;
            }
            if ($key == "reference")
            {
                $reference = $param;
            }
            if ($key == "show_deleted")
            {
                $showActive = false;
            }
        }

        // TODO Check if the current user is allowed to get the selected informations
        $where = array("scope_id" => $scopeId);
        $where["active"] = $showActive ? 1 : 0;
        if ($scopeId == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["creator_id"] = $userId;
        }
        if ($scopeId == ScopeEnum::ScopePlayer)
        {
            $gameId = $reference;
            $playerId = $userId; // TODO get player id from userId and gameId
            $where["game_id"] = $gameId;
            $where["creator_id"] = $playerId;
        }
        if ($scopeId == ScopeEnum::ScopeCharacter)
        {
            $characterId = $reference;
            $gameId = 1; // Get gameId from characterId
            $where["game_id"] = $gameId;
            $where["creator_id"] = $characterId;
        }
        print_r2($where);

        $user_id = $this->auth->getCurrentUserId();
        $mapper = new MessageMapper($this->db, $this->logger);
        $exclude = array('content');
        $order = array("created" => false);
        $messages = $mapper->select($where, $order, 2, $exclude);
        return responseWithJson($response, $messages);
    });

    $app->post('/message', function (Request $request, Response $response)
    {
        // Endpoint sends a message
        // TODO Check if the user is allowed to send the message with
        // the selected scope, origin and destination.

        $data = $request->getParsedBody();
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->insert($data);
        return responseWithJson($response, $message, 201);
    });

    $app->get('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint returns the details of a single message.

        $id = (int)$args['id'];
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->selectById($id);
        return responseWithJson($response, $message);
    });

    $app->put('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint edits a message.

        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->update($id, $data);
        return responseWithJson($response, $message);
    });

    $app->patch('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint patches a message.

        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->patch($id, $data);
        return responseWithJson($response, $message);
    });

    $app->delete('/message/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->delete($id);
        return responseWithJson($response, $message);
    })->add($requireAdmin);

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