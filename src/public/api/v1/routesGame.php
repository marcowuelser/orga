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


    $app->get('/games', function (Request $request, Response $response)
    {
        $this->logger->addInfo("Get games list (UNIMPLEMENTED)");
        // $mapper = new GameMapper($this->db);
        $games = array(); // $mapper->getGames();
        return responseWithJson($response, $games);
    })->add($requireUser);

}

?>
