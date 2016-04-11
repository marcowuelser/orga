<?php

use \Monolog\Logger as Logger;

require_once("classes/DbMapperAbs.php");

class RulesetMapper extends DbMapperAbs
{
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "r_ruleset";
        $this->name_single = "ruleset";
        $this->name_multi = "rulesets";
        $this->uriSingle = "ruleset";
    }

    // Query helpers

    protected function onInsert(array $data) : array
    {
        $fields = array();

        // user fields
        $this->requireString("name", $data, $fields);
        $this->requireString("caption", $data, $fields);
        $this->requireString("description", $data, $fields);
        $this->optionalBool("active", $data, $fields);
        $this->optionalInt("default_order", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['created'] = $now;
        $fields['updated'] = $now;
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
        $id = $data["id"];
        $data['id'] = intval ($data['id']);
        $data["uri"] = $this->getEntryURI($id);
        $data['active'] = intval ($data["active"]) != 0;
        $data['default_order'] = intval ($data["default_order"]);
        return $data;
    }
}
