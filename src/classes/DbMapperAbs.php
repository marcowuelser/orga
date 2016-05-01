<?php
declare(strict_types=1);

use \ORGA\Error\ErrorCode as ErrorCode;

abstract class DbMapperAbs
{
    public function setBaseURI(string $uri)
    {
        self::$baseUri = $uri;
    }

    public function select(array $where, array $order, int $limit, array $removeFields = array()) : array
    {
        $this->logger->addInfo("Get $this->name_multi");

        try
        {
            $sql = $this->createSqlSelect($this->table, $where, $order, $limit);
            $stmt = $this->db->prepare($sql);
            $this->bindWhereFields($stmt, $where);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($data)
            {
                foreach($data as &$d)
                {
                    $this->removeFields($d, $removeFields);
                    $d = $this->toPublicData($d);
                }
                return $data;
            }
            else
            {
                return array();
            }
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), ErrorCode::DB_ERROR);
        }
    }

    public function selectCount(array $where) : array
    {
        $this->logger->addInfo("Get $this->name_multi");

        try
        {
            $sql = $this->createSqlSelectCount($this->table, $where);
            $stmt = $this->db->prepare($sql);
            $this->bindWhereFields($stmt, $where);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data)
            {
                return $data;
            }
            else
            {
                return array();
            }
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), ErrorCode::DB_ERROR);
        }
    }

    public function selectAll() : array
    {
        $this->logger->addInfo("Get all $this->name_multi");

        try
        {
            $result = $this->db->query("SELECT * FROM $this->table WHERE 1=1 ORDER BY default_order ASC;");

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
                return array();
            }
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), ErrorCode::DB_ERROR);
        }
    }

    public function selectById(int $id) : array
    {
        $this->logger->addInfo("Get $this->name_single with id $id");

        try
        {
            $result = $this->db->query("SELECT * FROM $this->table WHERE id=$id;");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            if ($data)
            {
                return $this->toPublicData($data);
            }
            else
            {
                throw new Exception("$this->name_single with id $id not found", ErrorCode::NOT_FOUND);
            }
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), ErrorCode::DB_ERROR);
        }
    }

    public function insert(array $data) : array
    {
        $this->logger->addInfo("Add new $this->name_single");
        if ($data == null)
        {
            throw new Exception("HTTP body is empty", ErrorCode::INVALID_REQUEST);
        }

        try
        {
            $fields = $this->onInsert($data);
            $sql = $this->createSqlInsert($this->table, $fields);
            $stmt = $this->db->prepare($sql);
            $this->bindFields($stmt, $fields);
            $stmt->execute();

            $id = intval($this->db->lastInsertId());
            return $this->selectById($id);
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), ErrorCode::DB_ERROR);
        }
    }

    public function update(int $id, array $data) : array
    {
        if ($data == null)
        {
            throw new Exception("HTTP body is empty", ErrorCode::INVALID_REQUEST);
        }

        $this->logger->addInfo("Update $this->name_single with id $id");

        try
        {
            $fields = $this->onUpdate($data);
            $sql = $this->createSqlUpdate($this->table, $fields);
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":field_key", $id);
            $this->bindFields($stmt, $fields);
            $stmt->execute();
            return $this->selectById($id);
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), ErrorCode::DB_ERROR);
        }
    }

    public function patch(int $id, array $data) : array
    {
        if ($data == null)
        {
            throw new Exception("HTTP body is empty", ErrorCode::INVALID_REQUEST);
        }

        $this->logger->addInfo("Patch $this->name_single with id $id");

        try
        {
            $fields = $this->onPatch($data);
            if (empty($fields))
            {
                throw new Exception("No fields in patch request", ErrorCode::INVALID_REQUEST);
            }
            $sql = $this->createSqlUpdate($this->table, $fields);
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":field_key", $id);
            $this->bindFields($stmt, $fields);
            $stmt->execute();
            return $this->selectById($id);
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), ErrorCode::DB_ERROR);
        }
    }

    public function delete(int $id) : array
    {
        $this->logger->addInfo("Delete $this->name_single with id $id");

        try
        {
            $entry = $this->selectById($id);
            $sql = $this->createSqlDelete($this->table);

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":field_key", $id);
            $stmt->execute();
            return $entry;
        }
        catch (PDOException $ex)
        {
            throw new Exception($ex->getMessage(), ErrorCode::DB_ERROR);
        }
    }

    // Assertion helpers

    protected function requireString(string $field, array $data, array &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            throw new Exception("Field $field is missing", ErrorCode::INVALID_REQUEST);
        }
        $value = filter_var($data[$field], FILTER_SANITIZE_STRING);

        if (!is_string($value))
        {
            throw new Exception("Field $field is no string", ErrorCode::INVALID_REQUEST);
        }

        $fields[$field] = $value;
    }

    protected function requireBool(string $field, array $data, array &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            throw new Exception("Field $field is missing", ErrorCode::INVALID_REQUEST);
        }
        $value = $data[$field];
        if (!is_int($value) && !is_bool($value))
        {
            throw new Exception("Field $field is no bool", ErrorCode::INVALID_REQUEST);
        }
        $value = $value ? 1 : 0;

        $fields[$field] = $value;
    }

    protected function requireInt(string $field, array $data, array &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            throw new Exception("Field $field is missing", ErrorCode::INVALID_REQUEST);
        }
        $value = $data[$field];
        if (!is_int($value))
        {
            throw new Exception("Field $field is no int", ErrorCode::INVALID_REQUEST);
        }

        $fields[$field] = $value;
    }

    protected function optionalString(string $field, array $data, array &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            return;
        }
        $value = filter_var($data[$field], FILTER_SANITIZE_STRING);

        if (!is_string($value))
        {
            return;
        }

        $fields[$field] = $value;
    }

    protected function optionalBool(string $field, array $data, array &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            return;
        }
        $value = $data[$field];
        if (!is_int($value) && !is_bool($value))
        {
            return;
        }
        $value = $value ? 1 : 0;

        $fields[$field] = $value;
    }

    protected function optionalInt(string $field, array $data, array &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            return;
        }
        $value = $data[$field];

        if (!is_int($value))
        {
            return;
        }

        $fields[$field] = $value;
    }

    protected function getEntryURI(int $id) : string
    {
        $protocol = $_SERVER['REQUEST_SCHEME'];
        $server = $_SERVER['SERVER_NAME'];
        return  self::$baseUri . $this->uriSingle . '/' . $id;
    }

    abstract protected function onInsert(array $data) : array;
    abstract protected function onUpdate(array $data) : array;
    abstract protected function onPatch(array $data) : array;
    abstract protected function toPublicData(array $data) : array;

    private function createSqlUpdate(string $table, array $fields) : string
    {
        $sql = "UPDATE $table SET";
        $notFirst = false;
        foreach ($fields as $field => $value)
        {
            if ($notFirst)
            {
                $sql .= ",";
            }
            $notFirst = true;

            $sql .= " `".$field."` = :field_".$field;
        }
        $sql .= " WHERE `id` = :field_key LIMIT 1;";
        return $sql;
    }

    private function createSqlInsert(string $table, array $fields) : string
    {
        $sql = "INSERT INTO `$table` (";
        $notFirst = false;
        foreach ($fields as $field => $value)
        {
            if ($notFirst)
            {
                $sql .= ",";
            }
            $notFirst = true;

            $sql .= " `".$field."` ";
        }
        $sql .= ") VALUES (";
        $notFirst = false;
        foreach ($fields as $field => $value)
        {
            if ($notFirst)
            {
                $sql .= ",";
            }
            $notFirst = true;

            $sql .= " :field_".$field;
        }
        $sql .= ");";
        return $sql;
    }

    private function createSqlSelect(string $table, array $where = array(), array $order = array(), int $limit = 100, int $offset = -1) : string
    {
        $sql = "SELECT * FROM `$table`";
        $sql .= $this->createSqlWhereClause($where);
        $sql .= $this->createSqlOrderClause($order);
        $sql .= $this->createSqlLimitClause($limit, $offset);
        return $sql;
    }

    private function createSqlSelectCount(string $table, array $where = array(), array $order = array(), int $limit = 100, int $offset = -1) : string
    {
        $sql = "SELECT count(id) as `count` FROM `$table`";
        $sql .= $this->createSqlWhereClause($where);
        return $sql;
    }

    private function createSqlWhereClause($fields) : string
    {
        if (count($fields) == 0)
        {
            return " WHERE 1=1";
        }

        $sql = " WHERE ";
        $notFirst = false;
        foreach ($fields as $field => $value)
        {
            if ($notFirst)
            {
                $sql .= " AND ";
            }
            $notFirst = true;

            $sql .= " `".$field."` = ";
            $sql .= " :whereField_".$field;
        }
        return $sql;
    }

    private function createSqlOrderClause(array $fields) : string
    {
        if (count($fields) == 0)
        {
            return "";
        }

        $sql = " ORDER BY ";
        $notFirst = false;
        foreach ($fields as $field => $ascending)
        {
            if ($notFirst)
            {
                $sql .= ",";
            }
            $notFirst = true;

            $sql .= " `".$field."` ";
            if ($ascending)
            {
                $sql .= " ASC";
            }
            else
            {
                $sql .= " DESC";
            }
        }
        return $sql;
    }

    private function createSqlLimitClause(int $limit, int $offset) : string
    {
        if ($limit < 0)
        {
            return "";
        }
        $sql = " LIMIT $limit";

        if ($offset < 0)
        {
            return $sql;
        }
        $sql = " OFFSET $offset";
        return $sql;
    }

    private function createSqlDelete(string $table) : string
    {
        $sql = "DELETE FROM `$table` WHERE `id` = :field_key LIMIT 1;";
        return $sql;
    }

    private function bindFields(PDOStatement &$stmt, array $fields)
    {
        foreach ($fields as $field => $value)
        {
            $stmt->bindValue(":field_$field", $value);
        }
    }

    private function bindWhereFields(PDOStatement &$stmt, array $fields)
    {
        foreach ($fields as $field => $value)
        {
            $stmt->bindValue(":whereField_$field", $value);
        }
    }

    private function removeFields(array &$d, array $fields)
    {
        foreach ($fields as $field)
        {
            if (isset($d[$field]))
            {
                unset($d[$field]);
            }
        }
    }

    private static $baseUri;

    protected $db = null;
    protected $logger = null;
    protected $table = '';
    protected $name_single = '';
    protected $name_multi = '';
    protected $uriBase = '';
    protected $uriSingle = '';
}
