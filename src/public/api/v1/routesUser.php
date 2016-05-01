<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ORGA\Error\ErrorCode as ErrorCode;

function injectRoutesUser(\Slim\App $app, array $config)
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


    $app->get('/user/roles', function (Request $request, Response $response)
    {
        return responseWithJson($response, UserRoleFlag::toAssocArray());
    })->add($requireUser);

    $app->get('/user/login', function (Request $request, Response $response)
    {
        // Only public endpoint, used to log in.
        $username = "";
        $password = "";
        if (!Authorization::parseCredentials($request, $username, $password))
        {
            return $this->errorList->createResponse(
                $response, ErrorCode::AUTHENTICATION_FAILED, "No credentials");
        }

        $this->logger->addInfo("Login user $username");
        $mapper = new UserMapper($this->db, $this->logger);
        $data = $this->auth->loginUser($username, $password, $mapper);
        $this->logger->addInfo("Login is valid");
        return responseWithJson($response, $data);
    });

    $app->get('/user/logoff', function (Request $request, Response $response)
    {
        $this->logger->addInfo("Logoff user");
        $mapper = new UserMapper($this->db, $this->logger);
        $data = $this->auth->logoutCurrentUser($mapper);
        $response = $response->withStatus(204);
        return $response;
    })->add($requireUser);

    $app->get('/user', function (Request $request, Response $response)
    {
        $id = $this->auth->getCurrentUserId();
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->selectById($id);
        return responseWithJson($response, $users);
    })->add($requireUser);

    $app->patch('/user', function (Request $request, Response $response)
    {
        $id = $this->auth->getCurrentUserId();
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $user = $this->auth->patchCurrentUser($data, $mapper);
        return responseWithJson($response, $user);
    })->add($requireUser);

    $app->put('/user/password', function (Request $request, Response $response)
    {
        $id = $this->auth->getCurrentUserId();
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $user = $this->auth->changeCurrentUserPassword($data, $mapper);
        return responseWithJson($response, $user);
    })->add($requireUser);

    // User Management

    $app->get('/users', function (Request $request, Response $response)
    {
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->selectAll();
        return responseWithJson($response, $users);
    })->add($requireUser);

    $app->post('/user', function (Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->insert($data);
        return responseWithJson($response, $users);
    })->add($requireAdmin);

    $app->get('/user/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->selectById($id);
        return responseWithJson($response, $users);
    })->add($requireUser);

    $app->patch('/user/{id}', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->patch($id, $data);
        return responseWithJson($response, $users);
    })->add($requireAdmin);

    $app->put('/user/{id}/password', function (Request $request, Response $response, $args)
    {
        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $mapper = new UserMapper($this->db, $this->logger);
        $users = $mapper->setPassword($id, $data);
        return responseWithJson($response, $users);
    })->add($requireAdmin);
}

?>
