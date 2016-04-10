<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class UserAuthorizationMiddleware
{
    public function __construct($auth, $required)
    {
        $this->auth = $auth;
        $this->required = $required;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $ok = false;
        $error = "";

        $ok = $this->auth->isCurrentUserInRole($this->required);
        $error = "Role ".UserRoleFlag::toString($this->required)." required";

        if ($ok)
        {
            $response = $next($request, $response);
            return $response;
        }
        else
        {
            $response->getBody()->write($error);
            return $response->withStatus(403);
        }
    }

    private $auth;

    private $required;
}

?>
