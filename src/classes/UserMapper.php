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
        $this->uriSingle = "system/user";
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
                throw new Exception("User with name $name not found", 1001);
            }
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), 2001);
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
                throw new Exception("Invalid credentials", 3001);
            }
            if ($stmt->rowCount() < 1)
            {
                throw new Exception("Invalid credentials", 3001);
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->toPublicData($data);
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), 2001);
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
                throw new Exception("Invalid token", 1001);
            }
            if ($stmt->rowCount() < 1)
            {
                throw new Exception("Invalid token", 1001);
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->toPublicData($data);
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), 2001);
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
            throw new Exception($ex->getMessage(), 2001);
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
            throw new Exception($ex->getMessage(), 2001);
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
                throw new Exception("Could not set password for user id $id", 2001);
            }
            else
            {
                return $this->selectById($id);
            }
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), 2001);
        }
    }

    // Query helpers

    protected function onInsert($data)
    {
        $fields = array();

        // user fields
        $this->requireString("name", $data, $fields);
        $this->requireString("username", $data, $fields);
        $this->requireInt("role_flags", $data, $fields);
        $this->optionalBool("default_order", $data, $fields);
        $this->optionalInt("active", $data, $fields);

        // system fields
        if (!isset($fields['default_order']))
        {
            $fields['default_order'] = 0;
        }
        if (!isset($fields['active']))
        {
            $fields['active'] = true;
        }
        return $fields;
    }

    protected function onUpdate($data)
    {
        $fields = array();

        // user fields
        $this->requireString("name", $data, $fields);
        $this->requireString("username", $data, $fields);
        $this->requireInt("role_flags", $data, $fields);
        $this->requireInt("default_order", $data, $fields);
        $this->requireBool("active", $data, $fields);

        return $fields;
    }

    protected function onPatch($data)
    {
        $fields = array();

        // user fields
        $this->optionalString("name", $data, $fields);
        $this->optionalString("username", $data, $fields);
        $this->optionalInt("role_flags", $data, $fields);
        $this->optionalInt("default_order", $data, $fields);
        $this->optionalBool("active", $data, $fields);
        if (empty($fields))
        {
            throw new Exception("No fields in patch request");
        }

        return $fields;
    }

    protected function toPublicData($data)
    {
        $id = $data["id"];
        return array(
            "id" => intval ($id),
            "username" => $data["username"],
            "name" => $data["name"],
            "role_flags" => intval ($data["role_flags"]),
            "role" => UserRoleFlag::toString($data["role_flags"]),
            "default_order" => intval ($data["default_order"]),
            "active" => intval ($data["active"]) != 0,
            "uri" => $this->getEntryURI($id));
    }
}
