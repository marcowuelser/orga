<?php

require_once("classes/DbMapperAbs.php");

class UserMapper extends DbMapperAbs
{
    public function __construct($db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "s_user";
        $this->name_single = "user";
        $this->name_multi = "users";
    }

    public function selectByName($name)
    {
        try
        {
            $result = $this->db->query("SELECT * FROM s_user WHERE name='$name';");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            if ($data)
            {
                return $this->toPublicData($data);
            }
            else
            {
                return createErrorResponse(1001, "User with name $name not found");
            }
        }
        catch (PDOException $ex)
        {
            return createErrorResponse(2001, $ex);
        }
    }

    public function selectByCredentials($username, $password)
    {
        try
        {
            $sql = "SELECT * FROM s_user WHERE `username`='$username' AND `password`=PASSWORD('$password');";
            $stmt = $this->db->prepare($sql);
            if (!$stmt->execute())
            {
                return createErrorResponse(3001, "Invalid credentials");
            }
            if ($stmt->rowCount() < 1)
            {
                return createErrorResponse(3001, "Invalid credentials");
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->toPublicData($data);
        }
        catch (PDOException $ex)
        {
            return createErrorResponse(2001, $ex);
        }
    }

    public function selectByToken($username, $token)
    {
        try
        {
            $sql = "SELECT * FROM s_user WHERE `username`='$username' AND `token`='$token';";
            $stmt = $this->db->prepare($sql);
            if (!$stmt->execute())
            {
                return createErrorResponse(1001, "Invalid token");
            }
            if ($stmt->rowCount() < 1)
            {
                return createErrorResponse(1001, "Invalid token");
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->toPublicData($data);
        }
        catch (PDOException $ex)
        {
            return createErrorResponse(2001, $ex);
        }
    }

    // Utility

    public function setToken($id, $token)
    {
        $tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE s_user SET token='$token', token_expire=:field_expire WHERE id=:field_id;";
        try
        {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":field_expire", $tokenExpiration);
            $stmt->bindValue(":field_id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch (PDOException $ex)
        {
            return false;
        }
    }

    public function clearToken($id)
    {
        $sql = "UPDATE s_user SET token=NULL, token_expire=NULL WHERE id=:field_id;";
        try
        {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":field_id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch (PDOException $ex)
        {
            return false;
        }
    }

    public function setPassword($id, $data)
    {
        $sql = "UPDATE s_user SET password=PASSWORD(:field_password) WHERE id=:field_id;";
        try
        {
            $fields = array();
            $this->requireString("password", $data, $fields);
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":field_password", $fields['password']);
            $stmt->bindValue(":field_id", $id, PDO::PARAM_INT);
            if (!$stmt->execute())
            {
                return createErrorResponse(2001, "Could not set password for user id $id");
            }
            else
            {
                return $this->selectById($id);
            }
        }
        catch (PDOException $ex)
        {
            echo $ex;
            return createErrorResponse(2001, $ex);
        }
    }

    // Query helpers

    protected function onInsert($data)
    {
        $fields = array();
        $this->requireString("name", $data, $fields);
        $this->requireString("username", $data, $fields);
        $this->requireInt("role_id", $data, $fields);
        return $fields;
    }

    protected function onUpdate($data)
    {
        $fields = array();
        $this->requireString("name", $data, $fields);
        $this->requireString("username", $data, $fields);
        $this->requireInt("role_id", $data, $fields);
        return $fields;
    }

    protected function onPatch($data)
    {
        $fields = array();
        $this->optionalString("name", $data, $fields);
        $this->optionalString("username", $data, $fields);
        $this->optionalInt("role_id", $data, $fields);
        return $fields;
    }

    protected function toPublicData($data)
    {
        $id = $data["id"];
        return array(
            "id" => intval ($id),
            "username" => $data["username"],
            "name" => $data["name"],
            "role_id" => intval ($data["role_id"]),
            "uri" => "/src/slim_test/src/public/api/v1/system/user/$id");
    }
}
