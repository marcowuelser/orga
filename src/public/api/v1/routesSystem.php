<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesSystem(\Slim\App $app, array $config)
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


    $app->get('/system', function (Request $request, Response $response)
    {
        $mapper = new SystemMapper($this->db, $this->logger);
        $system = $mapper->selectAll();
        return responseWithJson($response, $system, 200);
    })->add($requireUser);

    $app->patch('/system', function (Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $mapper = new SystemMapper($this->db, $this->logger);
        $system = $mapper->patch(1, $data);
        return responseWithJson($response, $system);
    })->add($requireAdmin);
}

?>
