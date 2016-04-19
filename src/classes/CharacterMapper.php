<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;

require_once("classes/DbMapperAbs.php");

/**
 * Tables
 * g_character as character, g_game as game, g_player as player, s_user as user
 *
 * Fields:
 * id                 game.id                  GET
 * game_id                                     POST
 * game_caption                                
 * player_id                                   POST PATCH
 * player_user_name
 * name_short         game.name_short          POST PATCH
 * name_full          game.name_full           POST PATCH
 * description        game.description         POST PATCH
 * created            game.                    
 * updated            game.                    
 * default_order      game.                    POST PATCH
 * active             game.                    POST PATCH
 * uri                                         
 */



/**
 * REST creation fields: (FIXED after creation)
 * - game_id
 * REST mutable fields:
 * - player_id (DM)
 * - name_short
 * - name_full
 * - description
 */
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

        // Check constraint for game_id
        $gameId = intval($data["game_id"]);
        $gameMapper = new GameMapper($this->db, $this->logger);
        $game = $gameMapper->selectById($gameId); // throws if no such game

        // Check constraint for player_id
        $playerId = intval($data["player_id"]);
        $playerMapper = new PlayerMapper($this->db, $this->logger);
        $player = $playerMapper->selectById($playerId); // throws if no such player

        // check for player in same game as character
        if (intval($player["game_id"]) != $gameId)
        {
            throw new Exception("Player $playerId not in game $gameId", 1003);
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
        $fields = array();
        // require DM


        // user fields
        //$this->requireInt("game_id", $data, $fields);
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
        //$this->optionalInt("game_id", $data, $fields);
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
        $playerId = intval($data["player_id"]);
        $gameId = intval($data["game_id"]);
        $gameMapper = new GameMapper($this->db, $this->logger);
        $playerMapper = new PlayerMapper($this->db, $this->logger);
        $game = $gameMapper->selectById($gameId);
        $player = $playerMapper->selectById($playerId);

        $data['id'] = $id;
        $data["uri"] = $this->getEntryURI($id);
        $data["game_id"] = $gameId;
        $data["player_id"] = $playerId;
        $data["user_name"] = $player["user_name"];
        $data["game_caption"] = $game["caption"];
        $data['active'] = intval ($data["active"]) != 0;
        $data['default_order'] = intval ($data["default_order"]);

        // TODO add 'scope_alowed' field, true if user is owner of character

        return $data;
    }
}
