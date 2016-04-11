<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;

class ScopeService
{
    public function __construct()
    {
    }

    public function getScope() : int
    {
        return $this->currentScope;
    }

    public function getReferenceId() : int
    {
        return $this->currentReferenceId;
    }

    public function getGameId() : int
    {
        return $this->currentGameId;
    }

    public function parseScope(array $params, Authorization $auth, PDO $db, Logger $logger)
    {
        $playerMapper = new PlayerMapper($db, $logger);
        $characterMapper = new CharacterMapper($db, $logger);
        $gameMapper = new GameMapper($db, $logger);

        $hasScope = false;
        $hasReference = false;
        foreach($params as $key => $param)
        {
            if ($key == "scope")
            {
                $this->currentScope = intval($param);
                $hasScope = true;
            }
            if ($key == "reference")
            {
                $this->currentReferenceId = intval($param);
                $hasReference = true;
            }
        }

        if (!ScopeEnum::isValid($this->currentScope))
        {
            throw new Exception("Invalid scope $this->currentScope", 1003);
        }

        if (!$hasScope)
        {
            throw new Exception("No scope", 1003);
        }

        switch ($this->currentScope)
        {
            case ScopeEnum::ScopeUser:
                if (!$hasReference)
                {
                    // no reference in user scope is fine, we use current one.
                    $this->currentReferenceId = $auth->getCurrentUserId();
                }
                if ($this->currentReferenceId != $auth->getCurrentUserId())
                {
                    // assert that the current user has the rights to do that
                    if (!$auth->isCurrentUserInRole(UserRoleFlag::RoleAdmin))
                    {
                        throw new Exception("Admin privileges required", 1003);
                    }
                }
                $this->currentGameId = -1;
                break;

            case ScopeEnum::ScopePlayer:
                if (!$hasReference)
                {
                    throw new Exception("No player reference", 1003);
                }
                $player = $playerMapper->selectById($this->currentReferenceId);

                // get game ID from player entry.
                $this->currentGameId = $player["game_id"];

                // assert that the player matches the gurrent user.
                $player["user_id"];

                -1;
                break;

            case ScopeEnum::ScopeCharacter:
                if (!$hasReference)
                {
                    throw new Exception("No character reference", 1003);
                }
                $character = $characterMapper->selectById($this->currentReferenceId);

                // get game ID from character entry.
                $this->currentGameId = intval($character["game_id"]);

                // assert that the character matches the gurrent user,
                $player = $playerMapper->selectById(intval($character["player_id"]));
                $player["game_id"];
                $player["user_id"];

                // or that the current user has the DM role.
                break;
        }
    }

    // Helper

    private $currentScope = ScopeEnum::ScopeUser;
    private $currentReferenceId = -1;
    private $currentGameId = -1;
}

?>