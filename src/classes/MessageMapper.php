<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;

require_once("classes/DbMapperAbs.php");

class MessageMapper extends DbMapperAbs
{
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "s_message";
        $this->name_single = "message";
        $this->name_multi = "messages";
        $this->uriSingle = "message";
    }

    // Query helpers

    protected function onInsert(array $data) : array
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

    protected function onUpdate(array $data) : array
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

    protected function onPatch(array $data) : array
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

    protected function toPublicData(array $data) : array
    {
        $id = intval($data["id"]);
        $scope = intval($data["scope_id"]);
        $creatorId = intval($data["creator_id"]);
        $destinationId = intval($data["destination_id"]);

        $data['id'] = $id;
        $data["uri"] = $this->getEntryURI($id);
        $data['active'] = intval ($data["active"]) != 0;
        $data['default_order'] = intval ($data["default_order"]);

        if ($scope == ScopeEnum::ScopeUser)
        {
            $userMapper = new UserMapper($this->db, $this->logger);
            $creator = $userMapper->selectById($creatorId);
            $destination = $userMapper->selectById($destinationId);
            $data['creator'] = $creator["name"];
            $data['destination'] = $destination["name"];
        }
        if ($scope == ScopeEnum::ScopePlayer)
        {
            $playerMapper = new PlayerMapper($this->db, $this->logger);
            $creator = $playerMapper->selectById($creatorId);
            $destination = $playerMapper->selectById($destinationId);

            $userMapper = new UserMapper($this->db, $this->logger);
            $creatorId = intval($creator["user_id"]);
            $destinationId = intval($destination["user_id"]);
            $creator = $userMapper->selectById($creatorId);
            $destination = $userMapper->selectById($destinationId);

            $data['creator'] = $creator["name"];
            $data['destination'] = $destination["name"];
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $characterMapper = new CharacterMapper($this->db, $this->logger);
            $creator = $characterMapper->selectById($creatorId);
            $destination = $characterMapper->selectById($destinationId);
            $data['creator'] = $creator["name_short"];
            $data['destination'] = $destination["name_short"];
        }
        return $data;
    }
}
