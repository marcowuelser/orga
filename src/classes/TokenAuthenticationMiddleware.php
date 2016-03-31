<?php

use \Psr\Http\Message\ServerRequestInterface as RequestInterface;
use \Psr\Http\Message\ResponseInterface as ResponseInterface;

class TokenAuthenticationMiddleware
{
    public function __construct($db, $options = array())
    {
        $this->db = $db;
        $this->hydrate($options);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $host = $request->getUri()->getHost();
        $scheme = $request->getUri()->getScheme();
        $server_params = $request->getServerParams();
        $route = $request->getAttribute('route');
        if ($this->isPublicRoute($route->getPattern()))
        {
            return $next($request, $response);
        }

        // do actual auth
        $mapper = new UserMapper($this->db);
        $auth = $request->getHeader("Authorization")[0];
        $username = false;
        $token = false;
        if (preg_match("/Bearer\s+(.*)$/i", $auth, $matches))
        {
            $decoded = base64_decode($matches[1]);
            list($username, $token) = explode(":", $decoded);
        }

        if (!$username || !$token)
        {
            $response = $response->withStatus(401);
            return $response;
        }
        if (!$mapper->IsTokenValid($username, $token))
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

    public function setRealm($realm)
    {
        $this->options["realm"] = $realm;
    }

    public function setEnviroment($enviroment)
    {
        $this->options["enviroment"] = $envirmoment;
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

    private $db;

    private $options = array (
        "exclude" => "",
        "realm" => "Protected",
        "environment" => "HTTP_AUTHORIZATION",
    );
}

?>
