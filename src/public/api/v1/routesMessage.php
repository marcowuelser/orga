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

        $showInactive = getShowInactiveParam($request);
        $maxCount = getMaxCountParam($request);

        $scope = $this->scope->getScope();
        $where = array("scope_id" => $scope);
        $where["active"] = $showInactive ? 0 : 1;

        $mapper = new MessageMapper($this->db, $this->logger);
        $exclude = array('content');
        $order = array("created" => false);

        $messages = $mapper->select($where, $order, $maxCount, $exclude);
        return responseWithJson($response, $messages);
    })->add($requireUser)->add($requireScope);

    $app->get('/messages/count', function (Request $request, Response $response)
    {
        // Endpoint gets the number of messages in the system.
        // Only counts messages the current user is allowed to see.

        $showInactive = getShowInactiveParam($request);
        $scope = $this->scope->getScope();
        $mapper = new MessageMapper($this->db, $this->logger);

        // inbox
        $where = array("scope_id" => $scope);
        $where["active"] = $showInactive ? 0 : 1;
        $where["sent"] = true;
        // TODO Move to ScopeService
        if ($scope == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["destination_id"] = $this->scope->getUserId();
        }
        if ($scope == ScopeEnum::ScopeGame)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["destination_id"] = $this->scope->getPlayerId();
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["destination_id"] = $this->scope->getCharacterId();
        }
        $countInbox = $mapper->selectCount($where);

        // inbox (new)
        $where = array("scope_id" => $scope);
        $where["active"] = $showInactive ? 0 : 1;
        $where["sent"] = true;
        $where["viewed"] = 0;
        // TODO Move to ScopeService
        if ($scope == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["destination_id"] = $this->scope->getUserId();
        }
        if ($scope == ScopeEnum::ScopeGame)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["destination_id"] = $this->scope->getPlayerId();
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["destination_id"] = $this->scope->getCharacterId();
        }
        $countInboxNew = $mapper->selectCount($where);

        // outbox
        $where = array("scope_id" => $scope);
        $where["active"] = $showInactive ? 0 : 1;
        $where["sent"] = true;
        // TODO Move to ScopeService
        if ($scope == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["creator_id"] = $this->scope->getUserId();
        }
        if ($scope == ScopeEnum::ScopeGame)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["creator_id"] = $this->scope->getPlayerId();
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["creator_id"] = $this->scope->getCharacterId();
        }
        $countOutbox = $mapper->selectCount($where);

        // outbox (unsent)
        $where = array("scope_id" => $scope);
        $where["active"] = $showInactive ? 0 : 1;
        $where["sent"] = 0;
        // TODO Move to ScopeService
        if ($scope == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["creator_id"] = $this->scope->getUserId();
        }
        if ($scope == ScopeEnum::ScopeGame)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["creator_id"] = $this->scope->currentPlayerId();
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["creator_id"] = $this->scope->currentCharacterId();
        }
        $countOutboxUnset = $mapper->selectCount($where);

        return responseWithJson($response,
            array(
                "inbox" => $countInbox["count"],
                "inbox_new" => $countInboxNew["count"],
                "outbox" => $countOutbox["count"],
                "drafts" => $countOutboxUnset["count"],
            ));
    })->add($requireUser)->add($requireScope);

    $app->get('/messages/inbox', function (Request $request, Response $response)
    {
        // Endpoint gets all incomming messages for current user.
        // If no scope is passed, user is used.
        // If scope is player, the player id must be passed as reference
        // If the scope is character, the character id must be passed as reference

        $showInactive = getShowInactiveParam($request);
        $maxCount = getMaxCountParam($request);

        $scope = $this->scope->getScope();
        $where = array("scope_id" => $scope);
        $where["active"] = $showInactive ? 0 : 1;

        // TODO Move to ScopeService
        if ($scope == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["destination_id"] = $this->scope->getUserId();
        }
        if ($scope == ScopeEnum::ScopeGame)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["destination_id"] = $this->scope->getPlayerId();
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["destination_id"] = $this->scope->currentCharacterId();
        }

        $mapper = new MessageMapper($this->db, $this->logger);
        $exclude = array('content');
        $order = array("created" => false);
        $messages = $mapper->select($where, $order, $maxCount, $exclude);
        return responseWithJson($response, $messages);
    })->add($requireUser)->add($requireScope);

    $app->get('/messages/outbox', function (Request $request, Response $response)
    {
        // TODO use ScopeFactory as in method above !

        // Endpoint gets all sent messages by the current user.
        // If no scope is passed, user is used.
        // If scope is player, the player id must be passed as reference
        // If the scope is character, the character id must be passed as reference

        $maxCount = getMaxCountParam($request);

        $scope = $this->scope->getScope();
        $where = array("scope_id" => $scope);

        // TODO Move to ScopeService
        if ($scope == ScopeEnum::ScopeUser)
        {
            // game_id is not relevant for user messages
            $where["creator_id"] = $this->scope->getUserId();
        }
        if ($scope == ScopeEnum::ScopeGame)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["creator_id"] = $this->scope->currentPlayerId();
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $where["game_id"] = $this->scope->getGameId();
            $where["creator_id"] = $this->scope->getCharacterId();
        }

        $mapper = new MessageMapper($this->db, $this->logger);
        $exclude = array('content');
        $order = array("created" => false);
        $messages = $mapper->select($where, $order, $maxCount, $exclude);
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
