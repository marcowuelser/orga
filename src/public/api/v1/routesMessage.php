<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesMessage(\Slim\App $app, array $config)
{
    $container = $app->getContainer();
    $authOn = $config["authenticationOn"];

    // Authorization
    $requireAdmin = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleAdmin : 0);

    $requireUser = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleUser : 0);

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
        // TODO define correct filter for select statement !
        $rulesets = $mapper->selectAll();
        return responseWithJson($response, $rulesets);
    })->add($requireUser)->add($requireScope);

    $app->get('/messages/inbox', function (Request $request, Response $response)
    {
        // Endpoint gets all incomming messages for current user.
        // If no scope is passed, user is used.
        // If scope is player, the player id must be passed as reference
        // If the scope is character, the character id must be passed as reference

        // TODO Move to helper function
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
        $where["active"] = $showInactive ? 0 : 1;

        // TODO Move to ScopeService
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
    })->add($requireUser)->add($requireScope);

    $app->get('/messages/outbox', function (Request $request, Response $response)
    {
        // TODO use ScopeFactory as in method above !

        // Endpoint gets all sent messages by the current user.
        // If no scope is passed, user is used.
        // If scope is player, the player id must be passed as reference
        // If the scope is character, the character id must be passed as reference

        $scope = $this->scope->getScope();
        $where = array("scope_id" => $scope);

        // TODO Move to ScopeService
        if ($scope == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["creator_id"] = $this->scope->getReferenceId();
        }
        if ($scope == ScopeEnum::ScopePlayer)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["creator_id"] = $this->scope->getReferenceId();
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["creator_id"] = $this->scope->getReferenceId();
        }

        $mapper = new MessageMapper($this->db, $this->logger);
        $exclude = array('content');
        $order = array("created" => false);
        $messages = $mapper->select($where, $order, 2, $exclude);
        return responseWithJson($response, $messages);
    })->add($requireUser)->add($requireScope);

    $app->post('/message', function (Request $request, Response $response)
    {
        // Endpoint sends a message

        // TODO Check if the user is allowed to send the message with
        // the selected scope, origin and destination and throw if not.

        // TODO Do not throw if admin ? Or add special endpoint for admin ? 
        //      Sounds better, but need to think it over.

        $data = $request->getParsedBody();
        $scope = $this->scope->getScope();
        $data["scope_id"] = $scope;

echo "user=".$this->scope->getReferenceId();
        // TODO move to ScopeFactory, refactor to where statement ...
        if ($scope == ScopeEnum::ScopeUser)
        {
            $data["game_id"] = -1;
        }
        else
        {
            if ($this->scope->getGameId() != $data["game_id"])
            {
                throw new Exception("Game does not match", 1003);
            }
        }

        if ($this->scope->getReferenceId() != $data["creator_id"])
        {
            throw new Exception("Not allowed to create this message", 3002);
        }

        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->insert($data);
        return responseWithJson($response, $message, 201);
    })->add($requireUser)->add($requireScope);

    $app->get('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint returns the details of a single message.

        $id = (int)$args['id'];
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->selectById($id);
        $scope = $this->scope->getScope();
        $reference = $this->scope->getReferenceId();

        // TODO move to ScopeFactory, refactor to where statement ...
        if ($scope != $message["scope_id"])
        {
            throw new Exception("Message scope does not match", 1003);
        }

        if ($reference != $message["creator_id"] &&
            $reference != $message["destination_id"])
        {
            throw new Exception("Not allowed to access this message", 3002);
        }

        // TODO Do not throw if admin ? Or add special endpoint for admin ? 
        //      Sounds better, but need to think it over.

        return responseWithJson($response, $message);
    })->add($requireUser)->add($requireScope);

    $app->put('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint edits a message.

        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new MessageMapper($this->db, $this->logger);
        $scope = $this->scope->getScope();
        $reference = $this->scope->getReferenceId();

        // TODO move to ScopeFactory, refactor to where statement ...
        $message = $mapper->selectById($id);
        if ($scope != $message["scope_id"])
        {
            throw new Exception("Message scope does not match", 1003);
        }

        if ($reference != $message["creator_id"] &&
            $reference != $message["destination_id"])
        {
            throw new Exception("Not allowed to access this message", 3002);
        }

        // TODO Do not throw if admin ? Or add special endpoint for admin ? 
        //      Sounds better, but need to think it over.

        $message = $mapper->update($id, $data);
        return responseWithJson($response, $message);
    })->add($requireUser)->add($requireScope);

    $app->patch('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint patches a message.

        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new MessageMapper($this->db, $this->logger);

        // TODO move to ScopeFactory, refactor to where statement ...
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
        // TODO Do not throw if admin ? Or add special endpoint for admin ? 
        //      Sounds better, but need to think it over.

        $message = $mapper->patch($id, $data);
        return responseWithJson($response, $message);
    })->add($requireUser)->add($requireScope);

    $app->delete('/message/{id}', function (Request $request, Response $response, $args)
    {
        // Permanent delete of a message. Admin only, no scope checks required.

        // TODO move to /admin ?

        $id = (int)$args['id'];
        $mapper = new MessageMapper($this->db, $this->logger);
        $message = $mapper->delete($id);
        return responseWithJson($response, $message);
    })->add($requireAdmin);

}

?>