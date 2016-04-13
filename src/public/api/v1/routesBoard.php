<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesBoard(\Slim\App $app, array $config)
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


    $app->get('/boards', function (Request $request, Response $response)
    {
        // Endpoint gets all boards in the system.
        // Only returns boards the current user is allowed to see.

        $showInactive = getShowInactiveParam($request);
        $maxCount = getMaxCountParam($request);
        $parent = getParentParam($request);

        $scope = $this->scope->getScope();
        $where = array(
            "scope_id" => $scope,
            "active" => ($showInactive ? 0 : 1)
        );
        if ($parent > 0)
        {
            $where["parent_id"] = $parent;
        }
        else
        {
            $where["parent_id"] = 0;
        }

        $mapper = new BoardMapper($this->db, $this->logger);
        $exclude = array();
        $order = array("default_order" => true);

        $boards = $mapper->select($where, $order, $maxCount, $exclude);
        return responseWithJson($response, $boards);
    })->add($requireUser)->add($requireScope);

    $app->post('/board', function (Request $request, Response $response)
    {
        throw(new Exception("Unimplemented", 2002));
    })->add($requireUser)->add($requireScope);

    $app->get('/board/{id}', function (Request $request, Response $response, $args)
    {
        throw(new Exception("Unimplemented", 2002));
    })->add($requireUser)->add($requireScope);

    $app->put('/board/{id}', function (Request $request, Response $response, $args)
    {
        throw(new Exception("Unimplemented", 2002));
    })->add($requireUser)->add($requireScope);

    $app->patch('/board/{id}', function (Request $request, Response $response, $args)
    {
        throw(new Exception("Unimplemented", 2002));
    })->add($requireUser)->add($requireScope);

    $app->delete('/board/{id}', function (Request $request, Response $response, $args)
    {
        throw(new Exception("Unimplemented", 2002));
    })->add($requireAdmin);

    $app->get('/board/{id}/threads', function (Request $request, Response $response, $args)
    {
        // Endpoint gets all threads in a board.
        // Only returns boards the current user is allowed to see.
        $boardId = (int)$args['id'];
        $showInactive = getShowInactiveParam($request);
        $maxCount = getMaxCountParam($request);
        $scope = $this->scope->getScope();

        $boardMapper = new BoardMapper($this->db, $this->logger);
        $entryMapper = new BoardEntryMapper($this->db, $this->logger);

        $board = $boardMapper->selectById($boardId);
        if ($scope != intval($board["scope_id"]))
        {
            throw new Exception("Scope does not match", 1003);
        }

        $where = array(
            "parent_id" => -1,
            "board_id" => $boardId,
            "active" => ($showInactive ? 0 : 1)
        );
        $exclude = array('content');
        $order = array("updated" => false);

        $threads = $entryMapper->select($where, $order, $maxCount, $exclude);
        return responseWithJson($response, $threads);
    })->add($requireUser)->add($requireScope);

    $app->get('/board/thread/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint gets all entries in a thread.
        // TODO Only returns entries the current user is allowed to see.
        $threadId = (int)$args['id'];
        $showInactive = getShowInactiveParam($request);
        $maxCount = getMaxCountParam($request);
        $scope = $this->scope->getScope();

        $boardMapper = new BoardMapper($this->db, $this->logger);
        $entryMapper = new BoardEntryMapper($this->db, $this->logger);

        $thread = $entryMapper->selectById($threadId);
        $boardId = intval($thread["board_id"]);
        $board = $boardMapper->selectById($boardId);
        if ($scope != intval($board["scope_id"]))
        {
            throw new Exception("Scope does not match", 1003);
        }

        $where = array(
            "parent_id" => $threadId,
            "active" => ($showInactive ? 0 : 1)
        );
        $exclude = array();
        $order = array("created" => true);

        $entries = $entryMapper->select($where, $order, $maxCount, $exclude);
        return responseWithJson($response, array_merge(array($thread), $entries));
    })->add($requireUser)->add($requireScope);

    $app->get('/board/entry/{id}', function (Request $request, Response $response, $args)
    {
        // Endpoint gets a single board entry.
        // TODO Only returns entries the current user is allowed to see.
        $entryId = (int)$args['id'];
        $scope = $this->scope->getScope();

        $boardMapper = new BoardMapper($this->db, $this->logger);
        $entryMapper = new BoardEntryMapper($this->db, $this->logger);

        // get entry
        $entry = $entryMapper->selectById($entryId);

        // get thread (top entry)
        $threadId = intval($entry["parent_id"]);
        if ($threadId <= 0)
        {
            $thread = $entry;
        }
        else
        {
            $thread = $entryMapper->selectById($threadId);
        }

        // get board
        $boardId = intval($thread["board_id"]);
        $board = $boardMapper->selectById($boardId);

        if ($scope != intval($board["scope_id"]))
        {
            throw new Exception("Scope does not match", 1003);
        }

        return responseWithJson($response, $entry);
    })->add($requireUser)->add($requireScope);
}

?>
