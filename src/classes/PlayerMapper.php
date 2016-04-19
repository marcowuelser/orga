<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;

require_once("classes/DbMapperAbs.php");

/**
 * Tables
 * g_player as player, g_game as game, s_user as user
 *
 * Fields:
 * id                 player.id                GET
 * game_id            player.game_id           GET POST(DM)
 * game_caption       game.caption             GET
 * user_id            player.user.id           GET POST(DM)
 * user_name          user.name                GET
 * role_flags         player.role_flags        GET POST(DM) PATCH(DM)
 * roles                                       GET
 * created            player.created           GET
 * updated            player.updated           GET
 * default_order      player.default_order     GET POST(DM) PATCH(DM)
 * active             player.active            GET PATCH(DM)
 * uri                                         GET
 */
class PlayerMapper extends DbMapperAbs
{
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "g_player";
        $this->name_single = "player";
        $this->name_multi = "players";
        $this->uriSingle = "player";
    }

    // Query helpers

    protected function onInsert(array $data) : array
    {
        $fields = array();

        // user fields
        $this->requireInt("game_id", $data, $fields);
        $this->requireInt("user_id", $data, $fields);
        $this->requireInt("role_flags", $data, $fields);

        // Check constraint for game_id
        $gameId = intval($data["game_id"]);
        $gameMapper = new GameMapper($this->db, $this->logger);
        $game = $gameMapper->selectById($gameId); // throws if no such game

        // Check constraint for user_id
        $userId = intval($data["user_id"]);
        $userMapper = new UserMapper($this->db, $this->logger);
        $user = $userMapper->selectById($userId); // throws if no such user

        // Check constraint for same user only once
        $where = array("user_id" => $userId, "game_id" => $gameId);
        $order = array();
        $duplicate = $this->select($where, $order, 1);
        if (count($duplicate) > 0)
        {
            throw new Exception("User $userId already in game $gameId", 1003);
        }

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
        throw new Exception("Update not permited, use patch", 1003);
    }

    protected function onPatch(array $data) : array
    {
        $fields = array();

        // user fields
        $this->optionalInt("role_flags", $data, $fields);

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
        $gameId = intval($data["game_id"]);
        $userId = intval($data["user_id"]);
        $roleFlags = intval($data["role_flags"]);

        $userMapper = new UserMapper($this->db, $this->logger);
        $user = $userMapper->selectById($userId);

        $gameMapper = new GameMapper($this->db, $this->logger);
        $game = $gameMapper->selectById($gameId);

        $data['id'] = $id;
        $data["uri"] = $this->getEntryURI($id);
        $data['active'] = intval ($data["active"]) != 0;
        $data['default_order'] = intval ($data["default_order"]);
        $data["game_id"] = $gameId;
        $data["user_id"] = $userId;
        $data['user_name'] = $user['name'];
        $data['game_caption'] = $game['caption'];
        $data['roles'] = PlayerRoleFlag::toString($roleFlags);
        return $data;
    }
}
