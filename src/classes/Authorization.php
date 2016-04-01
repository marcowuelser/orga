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

    public function isRoleEqualOrHigher($role)
    {
        return $this->currentUser['role_id'] >= $role;
    }

    public function isRoleInSelection($roles)
    {
        $currentRole = $this->currentUser['role_id'];
        foreach ($roles as $role)
        {
            if ($currentRole == $role)
            {
                return true;
            }
        }
        return $false;
    }

    public function validateToken($username, $token, $mapper)
    {
        if (!$username || !$token)
        {
            return false;
        }

        $response = $mapper->selectByToken($username, $token);
        if (isErrorResponse($response))
        {
            return false;
        }
        $this->setCurrentUser($response);
        return true;
    }

    public function loginUser($username, $password, $mapper)
    {
        $response = $mapper->selectByCredentials($username, $password);
        if (isErrorResponse($response))
        {
            return $response;
        }
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
        return createErrorResponse(3001, "Invalid Credentials");
    }

    public function logoutCurrentUser($mapper)
    {
        print_r2($this->currentUser);
        $id = $this->currentUser['id'];
        $mapper->clearToken($id);
        return;
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