<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesMessage(\Slim\App $app, array $config)
{
    $container = $app->getContainer();

    // Enable token authorization for all routes except for ../user/login.
    $authOn = $config["authenticationOn"];

    // Authorization
    $requireAdmin = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleAdmin : UserRoleFlag::RoleGuest);

    $requireScope = new ScopeAuthorizationMiddleware(
        $container->get('auth'),
        $container->get('scope'),
        $container->get('db'),
        $container->get('logger'));


    $app->get('/messages', function (Request $request, Response $response)
    {
        // Endpoint gets all messages in the system.
        // Only returns messages the current user is allowed to see.

        throw(new Exception("Unimplemented", 2002));

        $mapper = new MessageMapper($this->db, $this->logger);
        $rulesets = $mapper->selectAll();
        return responseWithJson($response, $rulesets);
    })->add($requireScope);

    $app->get('/messages/inbox', function (Request $request, Response $response)
    {
        // Endpoint gets all incomming messages for current user.
        // If no scope is passed, user is used.
        // If scope is player, the player id must be passed as reference
        // If the scope is character, the character id must be passed as reference
        $showInactive = false;

        $allGetVars = $request->getQueryParams();
        foreach($allGetVars as $key => $param)
        {
             if ($key == "show_deleted")
            {
                $showInactive = true;
            }
        }

        $scope = $this->scope->getScope();
        $where = array("scope_id" => $scope);
        if (!$showInactive)
        {
             $where["active"] = 1;
        }
        if ($scope == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["destination_id"] = $this->scope->getReferenceId();
        }
        if ($scope == ScopeEnum::ScopePlayer)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["destination_id"] = $this->scope->getReferenceId();
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["destination_id"] = $this->scope->getReferenceId();
        }

        $mapper = new MessageMapper($this->db, $this->logger);
        $exclude = array('content');
        $order = array("created" => false);
        $messages = $mapper->select($where, $order, 2, $exclude);
        return responseWithJson($response, $messages);
    })->add($requireScope);

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

        $user_id = $this->auth->getCurrentUserId();
        $mapper = new MessageMapper($this->db, $this->logger);
        $exclude = array('content');
        $order = array("created" => false);
        $messages = $mapper->select($where, $order, 2, $exclude);
        return responseWithJson($response, $messages);
    })->add($requireScope);

    $app->post('/message', function (Request $request, Response $response)
    {
        // Endpoint sends a message
        // TODO Check if the user is allowed to send the message with
        // the selected scope, origin and destination.

        $data = $request->getParsedBody();
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->insert($data);
        return responseWithJson($response, $message, 201);
    })->add($requireScope);

    $app->get('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint returns the details of a single message.

        $id = (int)$args['id'];
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->selectById($id);

        if ($this->scope->getScope() != $message["scope_id"])
        {
            throw new Exception("Message scope does not match", 1003);
        }

        if ($this->scope->getReferenceId() != $message["creator_id"] &&
            $this->scope->getReferenceId() != $message["destination_id"])
        {
            throw new Exception("Not allowed to access this message", 3002);
        }

        return responseWithJson($response, $message);
    })->add($requireScope);

    $app->put('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint edits a message.

        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new MessageMapper($this->db, $this->logger);

        $message = $mapper->selectById($id);
        if ($this->scope->getScope() != $message["scope_id"])
        {
            throw new Exception("Message scope does not match", 1003);
        }

        if ($this->scope->getReferenceId() != $message["creator_id"] &&
            $this->scope->getReferenceId() != $message["destination_id"])
        {
            throw new Exception("Not allowed to access this message", 3002);
        }

        $message = $mapper->update($id, $data);
        return responseWithJson($response, $message);
    })->add($requireScope);

    $app->patch('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint patches a message.

        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new MessageMapper($this->db, $this->logger);

        $message = $mapper->selectById($id);
        if ($this->scope->getScope() != $message["scope_id"])
        {
            throw new Exception("Message scope does not match", 1003);
        }

        if ($this->scope->getReferenceId() != $message["creator_id"] &&
            $this->scope->getReferenceId() != $message["destination_id"])
        {
            throw new Exception("Not allowed to access this message", 3002);
        }

        $message = $mapper->patch($id, $data);
        return responseWithJson($response, $message);
    })->add($requireScope);

    $app->delete('/message/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->delete($id);
        return responseWithJson($response, $message);
    })->add($requireAdmin);

}

?>