<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesGame(\Slim\App $app, array $config)
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

    $requireScope = new ScopeAuthorizationMiddleware(
        $container->get('auth'),
        $container->get('scope'),
        $container->get('db'),
        $container->get('logger'));

    $app->get('/games', function (Request $request, Response $response)
    {
        // TODO Filter by player asignment ? Or is it ok for all
        // user to see all games ?

        $mapper = new GameMapper($this->db, $this->logger);
        $games = $mapper->selectAll();
        return responseWithJson($response, $games);
    })->add($requireUser)->add($requireScope);

    $app->post('/game', function (Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $scope = $this->scope->getScope();

        // Check constraint for id_ruleset
        $rulesetId = intval($data["ruleset_id"]);
        $rulesetMapper = new RulesetMapper($this->db, $this->logger);
        $ruleset = $rulesetMapper->selectById($rulesetId);

        $gameMapper = new GameMapper($this->db, $this->logger);
        $game = $gameMapper->insert($data);
        return responseWithJson($response, $game);
    })->add($requireAuthor)->add($requireScope);

    $app->get('/game/{id}', function (Request $request, Response $response, $args)
    {
        // TODO Filter by player asignment ? Or is it ok for all
        // user to see all games ?

        $id = (int)$args['id'];
        $mapper = new GameMapper($this->db, $this->logger);
        $game = $mapper->selectById($id);
        return responseWithJson($response, $game);
    })->add($requireUser)->add($requireScope);

    // patch game

    // delete game
}

?>
