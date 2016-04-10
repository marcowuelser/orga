<?php

require_once("util/error.php");

abstract class DbMapperAbs
{
    public function select($where, $order, $limit, $removeFields = array())
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
            return createErrorResponse(2001, $ex->getMessage());
        }
    }

    public function selectAll()
    {
        $this->logger->addInfo("Get all $this->name_multi");

        try
        {
            $result = $this->db->query("SELECT * FROM $this->table WHERE 1=1;");

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
            return createErrorResponse(2001, $ex->getMessage());
        }
    }

    public function selectById($id)
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
                return createErrorResponse(1001, "Task with id $id not found");
            }
        }
        catch (PDOException $ex)
        {
            return createErrorResponse(2001, $ex->getMessage());
        }
    }

    public function insert($data)
    {
        $this->logger->addInfo("Add new $this->name_single");
        if ($data == null)
        {
            return createErrorResponse(1003, "HTTP body is empty");
        }

        try
        {
            $fields = $this->onInsert($data);
            $sql = $this->createSqlInsert($this->table, $fields);
            $stmt = $this->db->prepare($sql);
            $this->bindFields($stmt, $fields);
            $stmt->execute();

            return $this->selectById($this->db->lastInsertId());
        }
        catch (PDOException $ex)
        {
            return createErrorResponse(2001, $ex->getMessage());
        }
        catch (Exception $ex)
        {
            return createErrorResponse(1003, $ex->getMessage());
        }
    }

    public function update($id, $data)
    {
        if ($data == null)
        {
            return createErrorResponse(1003, "HTTP body is empty");
        }

        $this->logger->addInfo("Update $this->name_single with id $id");

        try
        {
            $fields = $this->onUpdate($data);
            $sql = $this->createSqlUpdate($this->table, $fields);
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":field_key", $id);
            $this->bindFields($stmt, $fields);
            $stmt->execute();
            return $this->selectById($id);
        }
        catch (PDOException $ex)
        {
            return createErrorResponse(2001, $ex->getMessage());
        }
        catch (Exception $ex)
        {
            return createErrorResponse(1003, $ex->getMessage());
        }
    }

    public function patch($id, $data)
    {
        if ($data == null)
        {
            return createErrorResponse(1003, "HTTP body is empty");
        }

        $this->logger->addInfo("Patch $this->name_single with id $id");

        try
        {
            $fields = $this->onPatch($data);
            if (empty($fields))
            {
                throw new Exception("No fields in patch request");
            }
            $sql = $this->createSqlUpdate($this->table, $fields);
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":field_key", $id);
            $this->bindFields($stmt, $fields);
            $stmt->execute();
            return $this->selectById($id);
        }
        catch (PDOException $ex)
        {
            return createErrorResponse(2001, $ex->getMessage());
        }
        catch (Exception $ex)
        {
            return createErrorResponse(1003, $ex->getMessage());
        }
    }

    public function delete($id)
    {
        $this->logger->addInfo("Delete $this->name_single with id $id");

        try
        {
            $entry = $this->selectById($id);
            if (isErrorResponse($entry))
            {
                return $entry;
            }
            $sql = $this->createSqlDelete($this->table);

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":field_key", $id);
            $stmt->execute();
            return $entry;
        }
        catch (PDOException $ex)
        {
            return createErrorResponse(2001, $ex->getMessage());
        }
        catch (Exception $ex)
        {
            return createErrorResponse(1003, $ex->getMessage());
        }
    }

    // Assertion helpers

    protected function requireString($field, $data, &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            throw new Exception("Field $field is missing");
            return;
        }
        $value = filter_var($data[$field], FILTER_SANITIZE_STRING);

        if (!is_string($value))
        {
            throw new Exception("Field $field is no string");
            return;
        }

        $fields[$field] = $value;
    }

    protected function requireBool($field, $data, &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            throw new Exception("Field $field is missing");
            return;
        }
        $value = $data[$field];
        if (!is_int($value) && !is_bool($value))
        {
            throw new Exception("Field $field is no bool");
            return;
        }
        $value = $value ? 1 : 0;

        $fields[$field] = $value;
    }

    protected function requireInt($field, $data, &$fields)
    {
        if (!array_key_exists($field, $data))
        {
            throw new Exception("Field $field is missing");
            return;
        }
        $value = $data[$field];
        if (!is_int($value))
        {
            throw new Exception("Field $field is no int");
            return;
        }

        $fields[$field] = $value;
    }

    protected function optionalString($field, $data, &$fields)
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

    protected function optionalBool($field, $data, &$fields)
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

    protected function optionalInt($field, $data, &$fields)
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

    protected function getEntryURI($id)
    {
       return $this->uriBase . $this->uriSingle . '/' . $id;
    }

    abstract protected function onInsert($data);
    abstract protected function onUpdate($data);
    abstract protected function onPatch($data);
    abstract protected function toPublicData($data);

    private function createSqlUpdate($table, $fields)
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

    private function createSqlInsert($table, $fields)
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

    private function createSqlSelect($table, $where = null, $order = null, $limit = 100, $offset = null)
    {
        //print_r2($where);
        //print_r2($order);
        $sql = "SELECT * FROM `$table`";
        $sql .= $this->createSqlWhereClause($where);
        $sql .= $this->createSqlOrderClause($order);
        $sql .= $this->createSqlLimitClause($limit, $offset);
        return $sql;
    }

    private function createSqlWhereClause($fields)
    {
        if ($fields == null)
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

    private function createSqlOrderClause($fields)
    {
        if ($fields == null)
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

    private function createSqlLimitClause($limit, $offset)
    {
        if ($limit == null)
        {
            return "";
        }
        $sql = " LIMIT $limit";

        if ($offset == null)
        {
            return $sql;
        }
        $sql = " OFFSET $offset";
        return $sql;
    }

    private function createSqlDelete($table)
    {
        $sql = "DELETE FROM `$table` WHERE `id` = :field_key LIMIT 1;";
        return $sql;
    }

    private function bindFields(&$stmt, $fields)
    {
        foreach ($fields as $field => $value)
        {
            $stmt->bindValue(":field_$field", $value);
        }
    }

    private function bindWhereFields(&$stmt, $fields)
    {
        foreach ($fields as $field => $value)
        {
            $stmt->bindValue(":whereField_$field", $value);
        }
    }

    private function removeFields(&$d, $fields)
    {
        foreach ($fields as $field)
        {
            if (isset($d[$field]))
            {
                unset($d[$field]);
            }
        }
    }

    protected $db = null;
    protected $logger = null;
    protected $table = '';
    protected $name_single = '';
    protected $name_multi = '';
    protected $uriBase = "http://localhost/src/orga_server/src/public/api/v1/";
    protected $uriSingle = '';
}
