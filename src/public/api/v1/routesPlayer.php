<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesPlayer(\Slim\App $app, array $config)
{
    $container = $app->getContainer();
    $authOn = $config["authenticationOn"];

    // Authorization
    $requireAdmin = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleAdmin : 0);

    $requireAuthor = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleAuthor : 0);

    $requireUser = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleUser : 0);

    $requireScopeAny = new ScopeAuthorizationMiddleware(
        $container->get('auth'),
        $container->get('scope'),
        $container->get('db'),
        $container->get('logger'));

    $requireScopeDM = new ScopeAuthorizationMiddleware(
        $container->get('auth'),
        $container->get('scope'),
        $container->get('db'),
        $container->get('logger'),
        0,
        PlayerRoleFlag::RoleDM);

    $app->get('/game/{gameId}/players', function (Request $request, Response $response, $args)
    {
        $gameId = (int)$args['gameId'];
        $showInactive = getShowInactiveParam($request);
        $maxCount = getMaxCountParam($request);

        $gameMapper = new GameMapper($this->db, $this->logger);
        $game = $gameMapper->selectById($gameId);

        $playerMapper = new PlayerMapper($this->db, $this->logger);

        $where = array("game_id" => $gameId);
        $where["active"] = $showInactive ? 0 : 1;
        $exclude = array();
        $order = array("default_order" => true);
        $players = $playerMapper->select($where, $order, $maxCount, $exclude);

        return responseWithJson($response, $players);
    })->add($requireUser)->add($requireScopeAny);

    $app->post('/player', function (Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $scope = $this->scope->getScope();

        $playerMapper = new PlayerMapper($this->db, $this->logger);
        $player = $playerMapper->insert($data);
        return responseWithJson($response, $player);
    })->add($requireUser)->add($requireScopeDM);

    $app->get('/player/{playerId}', function (Request $request, Response $response, $args)
    {
        $playerId = (int)$args['playerId'];

        $gameMapper = new GameMapper($this->db, $this->logger);
        $game = $gameMapper->selectById($gameId);

        $playerMapper = new PlayerMapper($this->db, $this->logger);
        $player = $playerMapper->selectById($playerId);

        if ($player["game_id"] != $gameId) 
        {
            throw new Exception("Player $playerId not in game $gameId", 1003);
        }

        return responseWithJson($response, $player);
    })->add($requireUser)->add($requireScopeAny);

    $app->patch('/player/{playerId}', function (Request $request, Response $response, $args)
    {
        $playerId = intval((int)$args['playerId']);
        $data = $request->getParsedBody();
        $scope = $this->scope->getScope();

        $playerMapper = new PlayerMapper($this->db, $this->logger);
        $player = $playerMapper->patch($playerId, $data);
        return responseWithJson($response, $player);
    })->add($requireUser)->add($requireScopeDM);

    $app->delete('/player/{playerId}', function (Request $request, Response $response, $args)
    {
        $playerId = (int)$args['playerId'];

        $playerMapper = new PlayerMapper($this->db, $this->logger);
        $player = $playerMapper->delete($playerId);
        return responseWithJson($response, $player);
    })->add($requireUser)->add($requireScopeDM);
}

?>
