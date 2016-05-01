<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \ORGA\Error\ErrorCode as ErrorCode;

class Authorization
{
    public function __construct()
    {
    }

    public function setCurrentUser(array $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function isCurrentUser() : bool
    {
        return $this->currentUser != null;
    }

    public function getCurrentUserId() : int
    {
        if (!$this->isCurrentUser())
        {
            return 0;
        }
        return $this->currentUser['id'];
    }

    public function getCurrentUsername() : string
    {
        if (!$this->isCurrentUser())
        {
            return "";
        }
        return $this->currentUser['username'];
    }

    public function isCurrentUserInRole(int $role) : bool
    {
        if ($role == 0)
        {
            return true;
        }
        return UserRoleFlag::checkFlag(intval($this->currentUser['role_flags']), $role);
    }

    public function validateToken(string $username, string $token, UserMapper $mapper) : bool
    {
        try
        {
            $response = $mapper->selectByToken($username, $token);
        }
        catch (Exception $ex)
        {
            return false;
        }
        $this->setCurrentUser($response);
        return true;
    }

    public function loginUser(string $username, string $password, UserMapper $mapper) : array
    {
        $response = $mapper->selectByCredentials($username, $password);
        $id = $response['id'];
        $username = $response['username'];
        $token = $this->generateNewToken();
        $roles = intval($response["role_flags"]);
        if ($mapper->setToken($id, $token))
        {
            return array(
                "auth" => base64_encode($username.":".$token),
                "token" => $token,
                "username" => $username,
                "role_flags" => $roles,
                "id" => $id);
        }
        throw new Exception("Invalid Credentials", ErrorCode::AUTHENTICATION_FAILED);
    }

    public function logoutCurrentUser(UserMapper $mapper)
    {
        $id = $this->getCurrentUserId();
        $mapper->clearToken($id);
    }

    public function patchCurrentUser(array $data, UserMapper $mapper) : array
    {
        $id = $this->getCurrentUserId();
        $filtered = array();
        if (isset($data['name']))
        {
            $filtered['name'] = $data['name'];
        }
        if (isset($data['username']))
        {
            $filtered['username'] = $data['username'];
        }
        return $mapper->patch($id, $filtered);
    }

    public function changeCurrentUserPassword(array $data, UserMapper $mapper) : array
    {
        $passwordOld = '';
        $passwordNew = '';
        if (isset($data['password_old']))
        {
            $passwordOld = $data['password_old'];
        }
        $id = $this->getCurrentUserId();

        $user = $mapper->selectByCredentials($this->getCurrentUsername(), $passwordOld);
        return $mapper->setPassword($id, $data);
    }

    // static util

    public static function parseCredentials(Request $request, string &$username, string &$password) : bool
    {
        $username = false;
        $password = false;
        $server_params = $request->getServerParams();

        /* If using PHP in CGI mode. */
        if (preg_match("/Basic\s+(.*)$/i", $server_params["HTTP_AUTHORIZATION"], $matches))
        {
           list($username, $password) = explode(":", base64_decode($matches[1]));
        }
        else
        {
            if (isset($server_params["PHP_AUTH_USER"]))
            {
               $username = $server_params["PHP_AUTH_USER"];
            }
            if (isset($server_params["PHP_AUTH_PW"]))
            {
               $password = $server_params["PHP_AUTH_PW"];
            }
        }
        return $username != false && $password != false;
    }

    // Helper

    private function generateNewToken() : string
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    private $currentUser = null;
}

?>