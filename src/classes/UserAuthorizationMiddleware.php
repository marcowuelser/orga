<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class UserAuthorizationMiddleware
{
    public const RoleEqualOrBetter = 1;
    public const RoleInSelection = 2;

    public function __construct($auth, $mode, $required)
    {
        $this->auth = $auth;
        $this->mode = $mode;
        $this->requiredRole = $required;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $ok = false;
        switch ($this->mode)
        {
            case RoleEqualOrBetter:
                $ok = checkRoleEqualOrBetter();
                break;

            case RoleInSelection:
                $ok = checkRoleInSelection();
                break;

            default:
                $ok = false;
                break;
        }

        if ($ok)
        {
            $response = $next($request, $response);
            return $response;
        }
        else
        {
            return $response->withStatus(403);
        }
    }

    private function checkRoleEqualOrBetter()
    {
        if ($this->auth->isRoleEqualOrHigher($this->required))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function checkRoleInSelection()
    {
        if ($this->auth->isRoleInSelection($this->required))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private $auth;

    private $mode;

    private $required;
}

?>
