<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesCharacter(\Slim\App $app, array $config)
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

    $requireScopePlayer = new ScopeAuthorizationMiddleware(
        $container->get('auth'),
        $container->get('scope'),
        $container->get('db'),
        $container->get('logger'),
        0,
        PlayerRoleFlag::RolePlayer);

    $app->get('/game/{gameId}/characters', function (
        Request $request, Response $response, $args)
    {
        $gameId = (int)$args['gameId'];
        $showInactive = getShowInactiveParam($request);
        $maxCount = getMaxCountParam($request);

        $gameMapper = new GameMapper($this->db, $this->logger);
        $game = $gameMapper->selectById($gameId);

        $characterMapper = new CharacterMapper($this->db, $this->logger);

        $where = array("game_id" => $gameId);
        $where["active"] = $showInactive ? 0 : 1;
        $exclude = array();
        $order = array("default_order" => true);
        $characters = $characterMapper->select($where, $order, $maxCount, $exclude);

        return responseWithJson($response, $characters);
    })->add($requireUser)->add($requireScopeAny);

    $app->post('/character', function (Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $scope = $this->scope->getScope();

        $characterMapper = new CharacterMapper($this->db, $this->logger);
        $character = $characterMapper->insert($data);
        return responseWithJson($response, $character);
    })->add($requireUser)->add($requireScopeDM);

    $app->get('/character/{characterId}', function (
        Request $request, Response $response, $args)
    {
        $characterId = (int)$args['characterId'];

        $gameMapper = new GameMapper($this->db, $this->logger);
        $game = $gameMapper->selectById($gameId);

        $characterMapper = new CharacterMapper($this->db, $this->logger);
        $player = $characterMapper->selectById($playerId);

        if ($player["game_id"] != $gameId) 
        {
            throw new Exception("Character $characterId not in game $gameId", 1003);
        }

        return responseWithJson($response, $player);
    })->add($requireUser)->add($requireScopeAny);

    $app->put('/character/{characterId}', function (
        Request $request, Response $response, $args)
    {
        $characterId = intval((int)$args['characterId']);
        $data = $request->getParsedBody();
        $scope = $this->scope->getScope();

        $characterMapper = new CharacterMapper($this->db, $this->logger);
        $character = $characterMapper->update($characterId, $data);
        return responseWithJson($response, $character);
    })->add($requireUser)->add($requireScopeDM);

    $app->patch('/character/{characterId}', function (
        Request $request, Response $response, $args)
    {
        $characterId = intval((int)$args['characterId']);
        $data = $request->getParsedBody();
        $scope = $this->scope->getScope();

        $characterMapper = new CharacterMapper($this->db, $this->logger);
        $character = $characterMapper->patch($characterId, $data);
        return responseWithJson($response, $character);
    })->add($requireUser)->add($requireScopePlayer);

    $app->delete('/character/{characterId}', function (
        Request $request, Response $response, $args)
    {
        $characterId = (int)$args['characterId'];

        $characterMapper = new CharacterMapper($this->db, $this->logger);
        $player = $characterMapper->delete($playerId);
        return responseWithJson($response, $player);
    })->add($requireAdmin)->add($requireScopeDM);
}

?>
