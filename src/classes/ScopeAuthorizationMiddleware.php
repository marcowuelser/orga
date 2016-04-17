<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ScopeAuthorizationMiddleware
{
    public function __construct(
        Authorization $auth, ScopeService $scope, PDO $db, Logger $logger,
        $userRole = 0, $playerRole = 0)
    {
        $this->auth = $auth;
        $this->scope = $scope;
        $this->db = $db;
        $this->logger = $logger;
        $this->userRole = $userRole;
        $this->playerRole = $playerRole;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $this->scope->parseScope($request->getQueryParams(), $this->auth, $this->db, $this->logger);


        if (!$this->scope->isUserInRole($this->userRole))
        {
            $error = "User role ".UserRoleFlag::toString($this->userRole)." required";
            $response->getBody()->write($error);
            return $response->withStatus(403);
        }

        if (!$this->scope->isPlayerInRole($this->playerRole))
        {
            $error = "Player role ".PlayerRoleFlag::toString($this->playerRole)." required";
            $response->getBody()->write($error);
            return $response->withStatus(403);
        }

        $response = $next($request, $response);
        return $response;
    }

    private $auth;
    private $scope;
    private $db;
    private $logger;
    private $userRole;
    private $playerRole;
}

?>
