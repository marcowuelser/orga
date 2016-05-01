<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;
use \ORGA\Error\ErrorCode as ErrorCode;

class BoardEntryMapper extends DbMapperAbs
{
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "s_board_entry";
        $this->name_single = "entry";
        $this->name_multi = "entries";
        $this->uriSingle = "board/entry";
    }

    // Query helpers

    protected function onInsert(array $data) : array
    {
        $fields = array();

        // user fields
        $this->requireInt("board_id", $data, $fields);
        $this->requireInt("parent_id", $data, $fields);
        $this->requireInt("creator_id", $data, $fields);
        $this->requireInt("updater_id", $data, $fields);
        $this->requireString("caption", $data, $fields);
        $this->requireString("content", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['created'] = $now;
        $fields['updated'] = $now;
        $fields['locked'] = false;
        $fields['active'] = true;

        return $fields;
    }

    protected function onUpdate(array $data) : array
    {
        $fields = array();

        // user fields
        $this->requireInt("board_id", $data, $fields);
        $this->requireInt("parent_id", $data, $fields);
        $this->requireInt("creator_id", $data, $fields);
        $this->requireInt("updater_id", $data, $fields);
        $this->requireString("caption", $data, $fields);
        $this->requireString("content", $data, $fields);
        $this->requireBool("active", $data, $fields);
        $this->requireBool("locked", $data, $fields);

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['updated'] = $now;
        return $fields;
    }

    protected function onPatch(array $data) : array
    {
        $fields = array();

        // user fields
        $this->optionalInt("board_id", $data, $fields);
        $this->optionalInt("parent_id", $data, $fields);
        $this->optionalInt("creator_id", $data, $fields);
        $this->optionalInt("updater_id", $data, $fields);
        $this->optionalString("caption", $data, $fields);
        $this->optionalString("content", $data, $fields);
        $this->optionalBool("active", $data, $fields);
        $this->optionalBool("locked", $data, $fields);

        if (empty($fields))
        {
            throw new Exception("No fields in patch request", ErrorCode::INVALID_REQUEST);
        }

        // system fields
        $now = date('Y-m-d H:i:s');
        $fields['updated'] = $now;

        return $fields;
    }

    protected function toPublicData(array $data) : array
    {
        $id = intval($data["id"]);
        $boardId = intval($data["board_id"]);
        $parentId = intval($data["parent_id"]);
        $creatorId = intval($data["creator_id"]);
        $updaterId = intval($data["updater_id"]);

        $data['id'] = $id;
        $data["uri"] = $this->getEntryURI($id);
        $data['active'] = intval ($data["active"]) != 0;
        $data['locked'] = intval ($data["locked"]) != 0;

        $boardMapper = new BoardMapper($this->db, $this->logger);
        $board = $boardMapper->selectById($boardId);
        $scopeId = intval($board["scope_id"]);

        if ($scopeId == ScopeEnum::ScopeUser)
        {
            $userMapper = new UserMapper($this->db, $this->logger);

            $creator = $userMapper->selectById($creatorId);
            $data['creator'] = $creator["name"];

            if ($updaterId > 0)
            {
                $updater = $userMapper->selectById($updaterId);
                $data['updater'] = $destination["name"];
            }
        }
        if ($scopeId == ScopeEnum::ScopeGame)
        {
            $playerMapper = new PlayerMapper($this->db, $this->logger);
            $userMapper = new UserMapper($this->db, $this->logger);

            $creator = $playerMapper->selectById($creatorId);
            $creatorId = intval($creator["user_id"]);
            $creator = $userMapper->selectById($creatorId);
            $data['creator'] = $creator["name"];

            if ($updaterId > 0)
            {
                $updater = $playerMapper->selectById($updaterId);
                $updaterId = intval($updater["user_id"]);
                $updater = $userMapper->selectById($destinationId);
                $data['updater'] = $updater["name"];
            }
        }
        if ($scopeId == ScopeEnum::ScopeCharacter)
        {
            $characterMapper = new CharacterMapper($this->db, $this->logger);

            $creator = $characterMapper->selectById($creatorId);
            $data['creator'] = $creator["name_short"];

            if ($updaterId > 0)
            {
                $updater = $characterMapper->selectById($updaterId);
                $data['updater'] = $updater["name_short"];
            }
            else
            {
                $data['updater'] = "";
            }
        }
        return $data;
    }
}
