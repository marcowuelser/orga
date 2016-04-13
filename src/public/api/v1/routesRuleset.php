<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesRuleset(\Slim\App $app, array $config)
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


    $app->get('/rulesets', function (Request $request, Response $response)
    {
        $mapper = new RulesetMapper($this->db, $this->logger);
        $rulesets = $mapper->selectAll();
        return responseWithJson($response, $rulesets);
    })->add($requireUser);

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
    })->add($requireUser);

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
}

?>
