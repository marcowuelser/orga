<?php

require_once("classes/DbMapperAbs.php");

class RulesetMapper extends DbMapperAbs
{
    public function __construct($db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "r_ruleset";
        $this->name_single = "ruleset";
        $this->name_multi = "rulesets";
        $this->uriSingle = "ruleset";
    }

    // Query helpers

    protected function onInsert($data)
    {
        $fields = array();

        // user fields
        $this->requireString("name", $data, $fields);
        $this->requireString("caption", $data, $fields);
        $this->requireString("description", $data, $fields);
        $this->optionalBool("active", $data, $fields);
        $this->optionalInt("defaultOrder", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['created'] = $now;
        $fields['updated'] = $now;
        if (!isset($fields['defaultOrder']))
        {
            $fields['defaultOrder'] = 0;
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
        $this->requireString("caption", $data, $fields);
        $this->requireString("description", $data, $fields);
        $this->requireBool("active", $data, $fields);
        $this->requireInt("defaultOrder", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['updated'] = $now;
        return $fields;
    }

    protected function onPatch($data)
    {
        $fields = array();

        // user fields
        $this->optionalString("name", $data, $fields);
        $this->optionalString("caption", $data, $fields);
        $this->optionalString("description", $data, $fields);
        $this->optionalBool("active", $data, $fields);
        $this->optionalInt("defaultOrder", $data, $fields);
        if (empty($fields))
        {
            throw new Exception("No fields in patch request");
        }

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['updated'] = $now;

        return $fields;
    }

    protected function toPublicData($data)
    {
        $id = $data["id"];
        $data['id'] = intval ($data['id']);
        $data["uri"] = $this->getEntryURI($id);
        $data['active'] = intval ($data["active"]) != 0;
        $data['defaultOrder'] = intval ($data["defaultOrder"]);
        return $data;
    }
}
