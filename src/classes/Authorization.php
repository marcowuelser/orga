<?php

class Authorization
{
    public function __construct()
    {
    }

    public function setCurrentUser($currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function isCurrentUser()
    {
        return $this->currentUser != null;
    }

    public function getCurrentUserId()
    {
        if (!$this->isCurrentUser())
        {
            return 0;
        }
        return $this->currentUser['id'];
    }

    public function getCurrentUsername()
    {
        if (!$this->isCurrentUser())
        {
            return "";
        }
        return $this->currentUser['username'];
    }

    public function isCurrentUserInRole($role)
    {
        $flag = new UserRoleFlag();
        return $flag->checkFlag($this->currentUser['role_flags'], $role);
    }

    public function validateToken($username, $token, $mapper)
    {
        if (!$username || !$token)
        {
            return false;
        }

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

    public function loginUser($username, $password, $mapper)
    {
        $response = $mapper->selectByCredentials($username, $password);
        $id = $response['id'];
        $username = $response['username'];
        $token = $this->generateNewToken();
        if ($mapper->setToken($id, $token))
        {
            return array(
                "auth" => base64_encode($username.":".$token),
                "token" => $token,
                "username" => $username,
                "id" => $id);
        }
        throw new Exception("Invalid Credentials", 3001);
    }

    public function logoutCurrentUser($mapper)
    {
        $id = $this->getCurrentUserId();
        $mapper->clearToken($id);
        return;
    }

    public function patchCurrentUser($data, $mapper)
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

    public function changeCurrentUserPassword($data, $mapper)
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

    public static function parseCredentials($request, &$username, &$password)
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

    private function generateNewToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    private $currentUser = null;
}

?>