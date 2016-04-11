<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;

require_once("classes/DbMapperAbs.php");

class CharacterMapper extends DbMapperAbs
{
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "g_character";
        $this->name_single = "character";
        $this->name_multi = "characters";
        $this->uriSingle = "character";
    }

    // Query helpers

    protected function onInsert(array $data) : array
    {
        $fields = array();

        // user fields
        $this->requireInt("game_id", $data, $fields);
        $this->requireInt("player_id", $data, $fields);
        $this->requireString("name_short", $data, $fields);
        $this->requireString("name_full", $data, $fields);
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
        $this->requireInt("game_id", $data, $fields);
        $this->requireInt("player_id", $data, $fields);
        $this->requireString("name_short", $data, $fields);
        $this->requireString("name_full", $data, $fields);
        $this->requireString("description", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['updated'] = $now;
        return $fields;
    }

    protected function onPatch(array $data) : array
    {
        $fields = array();

        // user fields
        $this->optionalInt("game_id", $data, $fields);
        $this->optionalInt("player_id", $data, $fields);
        $this->otionalString("name_short", $data, $fields);
        $this->otionalString("name_full", $data, $fields);
        $this->otionalString("description", $data, $fields);

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

        $data['id'] = $id;
        $data["uri"] = $this->getEntryURI($id);
        $data['active'] = intval ($data["active"]) != 0;
        $data['default_order'] = intval ($data["default_order"]);
        return $data;
    }
}
