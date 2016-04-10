<?php

require_once("classes/DbMapperAbs.php");

class MessageMapper extends DbMapperAbs
{
    public function __construct($db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "s_message";
        $this->name_single = "message";
        $this->name_multi = "messages";
        $this->uriSingle = "message";
    }

    // Query helpers

    protected function onInsert($data)
    {
        $fields = array();

        // user fields
        $this->requireInt("game_id", $data, $fields);
        $this->requireInt("scope_id", $data, $fields);
        $this->requireInt("creator_id", $data, $fields);
        $this->requireInt("destination_id", $data, $fields);
        $this->requireString("caption", $data, $fields);
        $this->requireString("content", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['created'] = $now;
        $fields['updated'] = $now;
        $fields['default_order'] = 0;
        $fields['active'] = true;

        return $fields;
    }

    protected function onUpdate($data)
    {
        $fields = array();

        // user fields
        $this->requireInt("game_id", $data, $fields);
        $this->requireInt("scope_id", $data, $fields);
        $this->requireInt("creator_id", $data, $fields);
        $this->requireInt("destination_id", $data, $fields);
        $this->requireString("caption", $data, $fields);
        $this->requireString("content", $data, $fields);
        $this->requireBool("active", $data, $fields);
        $this->requireInt("default_order", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['updated'] = $now;
        return $fields;
    }

    protected function onPatch($data)
    {
        $fields = array();

        // user fields
        $this->optionalInt("game_id", $data, $fields);
        $this->optionalInt("scope_id", $data, $fields);
        $this->optionalInt("creator_id", $data, $fields);
        $this->optionalInt("destination_id", $data, $fields);
        $this->optionalString("caption", $data, $fields);
        $this->optionalString("content", $data, $fields);
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

    protected function toPublicData($data)
    {
        $id = $data["id"];
        $data['id'] = intval($data['id']);
        $data["uri"] = $this->getEntryURI($id);
        $data['active'] = intval ($data["active"]) != 0;
        $data['default_order'] = intval ($data["default_order"]);
        return $data;
    }
}
