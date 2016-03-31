<?php

require_once("util/error.php");

class UserMapper
{
	public function UserMapper($db)
	{
		$this->db = $db;
	}

	public function getUsers()
	{
		try
		{
			$result = $this->db->query("SELECT * FROM s_user WHERE 1=1;");

            $data = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($data)
            {
				foreach($data as &$d)
				{
					$d = $this->toPublicData($d);
				}
				return $data;
            }
            else
            {
			    return createErrorResponse(1002, "No user found");
            }
		}
		catch (PDOException $ex)
		{
			return createErrorResponse(2001, $ex);
		}
	}

	public function getUserById($id)
	{
		try
		{
			$result = $this->db->query("SELECT * FROM s_user WHERE id=$id;");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            if ($data)
            {
				return $this->toPublicData($data);
            }
            else
            {
				return createErrorResponse(1001, "User with id $id not found");
            }
		}
		catch (PDOException $ex)
		{
			return createErrorResponse(2001, $ex);
		}
	}

	public function getUserByName($name)
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

	public function getUserIdByCredentials($username, $password)
	{
		try
		{
			$sql = "SELECT * FROM s_user WHERE `username`='$username' AND `password`=PASSWORD('$password');";
			$stmt = $this->db->prepare($sql);
			if (!$stmt->execute())
			{
				return -1;
			}
			if ($stmt->rowCount() < 1)
			{
				return -1;
			}
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data["id"];
		}
		catch (PDOException $ex)
		{
			echo $ex;
			return -1;
		}
	}

	public function createUser($data)
	{
		try
		{
			$sql = "INSERT INTO s_user(username, password, name) VALUES (:username, PASSWORD(:password), :name);";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":username", $data["username"]);
			$stmt->bindParam(":password", $data["password"]);
			$stmt->bindParam(":name", $data["name"]);
			$stmt->execute();

			return $this->getTaskById($this->db->lastInsertId());
		}
		catch (PDOException $ex)
		{
			return createErrorResponse(2001, $ex);
		}
	}

	public function updateUser($id, $data)
	{
		try
		{
			$fields = array();
			$ok = false;
			if (array_key_exists("username", $data))
			{
				$ok = true;
				$fields[] = "username";
			}

			if (array_key_exists("password", $data))
			{
				$ok = true;
				$fields[] = "password";
			}

			if (array_key_exists("name", $data))
			{
				$ok = true;
				$fields[] = "name";
			}

			if (!$ok)
			{
				return createErrorResponse(1003, "No fields for update user");
			}

			$sql = createSqlUpdate("s_user", $fields);
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":field_key", $id);
			bindFields($stmt, $fields, $data);
			$stmt->execute();
			return $this->getUserById($id);
		}
 		catch (PDOException $ex)
		{
			return createErrorResponse(2001, $ex);
		}
	}

	public function removeUser($id)
	{
		try
		{
			$user = $this->getUserById($id);
			if (isErrorResponse($user))
			{
				return $user;
			}
			$sql = "DELETE FROM s_user WHERE id = :id LIMIT 1;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":id", $id);
			$stmt->execute();
			return $task;
		}
		catch (PDOException $ex)
		{
			return createErrorResponse(2001, $ex);
		}
	}

	public function loginUser($username, $password)
	{
		$id = $this->getUserIdByCredentials($username, $password);
		if ($id < 0)
		{
			// user not found
			return createErrorResponse(3001, "Credentials for user $username not valid");
		}
		$token = $this->generateUserToken($id);
		if (isErrorResponse($token))
		{
			return $token;
		}
		return array(
			"auth" => base64_encode($username.":".$token),
	        "token" => $token,
			"username" => $username,
		    "id" => $id);
	}

	public function logoutUserById($id)
	{
		return $this->invalidateUserToken($id);
	}

	public function isTokenValid($username, $token)
	{
		return true;
	}

	// Helpers

	private function generateUserToken($id)
	{
		// generate token
		$token = bin2hex(openssl_random_pseudo_bytes(16));
		$tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

		// Update db with token
		$sql = "UPDATE s_user SET token='$token', token_expire=:field_expire WHERE id=:field_id;";
		try
		{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":field_expire", $tokenExpiration);
			$stmt->bindParam(":field_id", $id, PDO::PARAM_INT);
			if (!$stmt->execute())
			{
				return createErrorResponse(2001, "Could not set token for user id $id");
			}
			else
			{
				return $token;
			}
		}
		catch (PDOException $ex)
		{
			echo $ex;
			return createErrorResponse(2001, $ex);
		}
	}

	private function updateUserToken($id)
	{
		$tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

		$fields = array("token_expire");
		$data = array($tokenExpiration);
		$sql = createSqlUpdate("s_user", $fields);
		echo $token;
		echo $tokenExpiration;
		try
		{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":field_key", $id, PDO::PARAM_INT);
			bindFields($stmt, $fields, $data);
			$stmt->execute();
			return true;
		}
		catch (PDOException $ex)
		{
			return createErrorResponse(2001, $ex);
		}
	}

	private function invalidateUserToken($id)
	{
		$sql = "UPDATE s_user () SET token = NULL, token_expire = NULL WHERE id = :field_key;";
		try
		{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":field_key", $id, PDO::PARAM_INT);
			$stmt->execute();
			return true;
		}
		catch (PDOException $ex)
		{
			return createErrorResponse(2001, $ex);
		}
	}

	private function toPublicData($data)
	{
		$id = $data["id"];
		return array(
		    "id" => $id,
			"username" => $data["username"],
			"name" => $data["name"],
			"uri" => "/src/slim_test/src/public/api/v1/user/$id");
	}

	private $db = null;
}