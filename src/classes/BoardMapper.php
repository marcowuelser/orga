<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;

require_once("classes/DbMapperAbs.php");

class BoardMapper extends DbMapperAbs
{
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "s_board";
        $this->name_single = "board";
        $this->name_multi = "boards";
        $this->uriSingle = "board";
    }

    // Query helpers

    protected function onInsert(array $data) : array
    {
        $fields = array();

        // user fields
        $this->requireInt("scope_id", $data, $fields);
        $this->requireInt("relation_id", $data, $fields);
        $this->requireInt("parent_id", $data, $fields);
        $this->requireString("name", $data, $fields);
        $this->requireString("caption", $data, $fields);
        $this->requireString("description", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['created'] = $now;
        $fields['updated'] = $now;
        $fields['default_order'] = 0;
        $fields['active'] = true;

        return $fields;
    }

    protected function onUpdate(array $data) : array
    {
        $fields = array();

        // user fields
        $this->requireInt("scope_id", $data, $fields);
        $this->requireInt("relation_id", $data, $fields);
        $this->requireInt("parent_id", $data, $fields);
        $this->requireString("name", $data, $fields);
        $this->requireString("caption", $data, $fields);
        $this->requireString("description", $data, $fields);
        $this->requireBool("active", $data, $fields);
        $this->requireInt("default_order", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['updated'] = $now;
        return $fields;
    }

    protected function onPatch(array $data) : array
    {
        $fields = array();

        // user fields
        $this->optionalInt("scope_id", $data, $fields);
        $this->optionalInt("relation_id", $data, $fields);
        $this->optionalInt("parent_id", $data, $fields);
        $this->optionalString("name", $data, $fields);
        $this->optionalString("caption", $data, $fields);
        $this->optionalString("description", $data, $fields);
        $this->optionalBool("active", $data, $fields);
        $this->optionalInt("default_order", $data, $fields);

        if (empty($fields))
        {
            throw new Exception("No fields in patch request");
        }

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['updated'] = $now;

        return $fields;
    }

    protected function toPublicData(array $data) : array
    {
        $id = intval($data["id"]);
        $scopeId = intval($data["scope_id"]);
        $relationId = intval($data["relation_id"]);
        $parentId = intval($data["parent_id"]);

        $data['id'] = $id;
        $data["uri"] = $this->getEntryURI($id);
        $data['active'] = intval ($data["active"]) != 0;
        $data['default_order'] = intval ($data["default_order"]);

        return $data;
    }
}
