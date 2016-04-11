<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;
use \Psr\Http\Message\ServerRequestInterface as RequestInterface;
use \Psr\Http\Message\ResponseInterface as ResponseInterface;

class TokenAuthenticationMiddleware
{
    public function __construct(Authorization $auth, PDO $db, Logger $logger, array $options = array())
    {
        $this->auth = $auth;
        $this->db = $db;
        $this->logger = $logger;
        $this->hydrate($options);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        //$host = $request->getUri()->getHost();
        //$scheme = $request->getUri()->getScheme();
        $server_params = $request->getServerParams();
        $route = $server_params['REQUEST_URI'];
        if ($this->isPublicRoute($route))
        {
            return $next($request, $response);
        }

        // do actual auth
        $mapper = new UserMapper($this->db, $this->logger);
        $authorization = $request->getHeader("Authorization")[0];
        $username = false;
        $token = false;
        if (preg_match("/Bearer\s+(.*)$/i", $authorization, $matches))
        {
            $decoded = base64_decode($matches[1]);
            list($username, $token) = explode(":", $decoded);
        }
        if (!$username || !$token)
        {
            $response = $response->withStatus(401);
            return $response;
        }

        if (!$this->auth->validateToken($username, $token, $mapper))
        {
            $response = $response->withStatus(401);
            return $response;
        }
        return $next($request, $response);
    }

    public function setExclude($path)
    {
        $this->options["exclude"] = $path;
    }

    private function isPublicRoute($url)
    {
        $matches = null;
        preg_match('/' . $this->options["exclude"] . '/', $url, $matches);
        return (count($matches) > 0);
    }

    private function hydrate($data = array())
    {
        foreach ($data as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method), $value);
            }
        }
    }

    private $auth;

    private $db;

    private $logger;

    private $options = array (
        "exclude" => ""
    );
}

?>
