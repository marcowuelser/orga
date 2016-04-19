<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;

require_once("classes/DbMapperAbs.php");

/**
 * Tables
 * s_user as user
 *
 * Fields:
 * id                 user.id                  GET
 * username           user.username            GET POST PATCH
 * name               user.name                GET POST PATCH
 * role_flags         user.role_flags          GET POST PATCH
 * roles                                       GET
 * created            player.                  GET
 * updated            player.                  GET
 * default_order      player.                  GET POST PATCH
 * active             player.                  GET POST PATCH
 * uri                                         GET
 */
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

    public function selectByName(string $name) : array
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

    public function selectByCredentials(string $username, string $password) : array
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

    public function selectByToken(string $username, string $token) : array
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

    public function setToken(int $id, string $token) : bool
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

    public function clearToken(int $id) : bool
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

    public function setPassword(int $id, array $data) : array
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

    protected function onInsert(array $data) : array
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

    protected function onUpdate(array $data) : array
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

    protected function onPatch(array $data) : array
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

    protected function toPublicData(array $data) : array
    {
        $id = intval($data["id"]);
        $role = intval($data["role_flags"]);
        $order = intval ($data["default_order"]);
        return array(
            "id" => $id,
            "username" => $data["username"],
            "name" => $data["name"],
            "role_flags" => $role,
            "roles" => UserRoleFlag::toString($role),
            "default_order" => $order,
            "active" => intval ($data["active"]) != 0,
            "uri" => $this->getEntryURI($id));
    }
}
