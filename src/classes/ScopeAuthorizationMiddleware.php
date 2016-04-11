<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ScopeAuthorizationMiddleware
{
    public function __construct(Authorization $auth, ScopeService $scope, PDO $db, Logger $logger)
    {
        $this->auth = $auth;
        $this->scope = $scope;
        $this->db = $db;
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Response $response, callable $next) : Response
    {
        $this->scope->parseScope($request->getQueryParams(), $this->auth, $this->db, $this->logger);

        $response = $next($request, $response);
        return $response;
    }

    private $auth;
    private $scope;
    private $db;
    private $logger;
}

?>
