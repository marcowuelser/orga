<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

include_once('../../../enum.php');

class UserAuthorizationMiddleware
{
    public function __construct($auth, $mode, $required)
    {
        $this->auth = $auth;
        $this->mode = $mode;
        $this->required = $required;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $ok = false;
        $error = "";
        switch ($this->mode)
        {
            case Match::EqualOrBetter:
                $ok = $this->checkRoleEqualOrBetter();
                $error = "Role ".UserRole::toString($this->required)." required";
                break;

            case Match::InSelection:
                $ok = $this->checkRoleInSelection();
                $error = "One of Roles (";
                foreach ($this->required as $role)
                {
                    $error .= UserRole::toString($role)." ";
                }
                $error .= ") required";
                break;

            default:
                $ok = false;
                $error = "Unknwon Mode";
                break;
        }

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
