<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;
use \ORGA\Error\ErrorCode as ErrorCode;

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

    public function getUserId() : int
    {
        return $this->currentUserId;
    }

    public function getPlayerId() : int
    {
        return $this->currentPlayerId;
    }

    public function getGameId() : int
    {
        return $this->currentGameId;
    }

    public function getCharacterId() : int
    {
        return $this->currentCharacterId;
    }

    public function isUserInRole($userRole): bool
    {
        if ($userRole == 0)
        {
            return true;
        }
        return UserRoleFlag::checkFlag($this->currentUserRole, $userRole);
    }

    public function isPlayerInRole($playerRole): bool
    {
        if ($playerRole == 0)
        {
            return true;
        }

        if ($this->currentScope == ScopeEnum::ScopeUser)
        {
            return false;
        }

        return PlayerRoleFlag::checkFlag($this->currentPlayerRole, $playerRole);
    }

    public function parseScope(array $params, Authorization $auth, PDO $db, Logger $logger)
    {
        $userMapper = new UserMapper($db, $logger);
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
            throw new Exception("Invalid scope $this->currentScope", ErrorCode::INVALID_REQUEST);
        }

        if (!$hasScope)
        {
            throw new Exception("No scope", ErrorCode::INVALID_REQUEST);
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
                        throw new Exception("Admin privileges required", ErrorCode::INVALID_REQUEST);
                    }
                }
                $user = $userMapper->selectById($this->currentReferenceId);
                $this->currentUserId = $this->currentReferenceId;
                $this->currentUserRoleFlags = intval($user["role_flags"]);
                $this->currentGameId = -1;
                $this->currentPlayerId = -1;
                $this->currentCharacterId = -1;
                break;

            case ScopeEnum::ScopeGame:
                if (!$hasReference)
                {
                    throw new Exception("No player reference", ErrorCode::INVALID_REQUEST);
                }

                // get game from player entry
                $game = $gameMapper->selectById($this->currentReferenceId);
                $this->currentGameId = intval($game["id"]);

                // user current user
                $userId = $auth->getCurrentUserId();
                $this->currentUserId = $userId;
                $user = $userMapper->selectById($this->currentUserId);
                $this->currentUserRoleFlags = intval($user["role_flags"]);

                // find player for current user
                $where = array("game_id" => $this->currentGameId, "user_id" => $userId);
                $players = $playerMapper->select($where, array(), 10);
                if (count($players) < 1)
                {
                    throw new Exception("User $userId has no player entry in game $this->currentGameId", ErrorCode::INVALID_REQUEST);
                }
                $player = $players[0];
                $this->currentPlayerId = intval($player["id"]);
                $this->currentPlayerRoleFlags = intval($player["role_flags"]);

                // assert that the player matches the gurrent user.
                if ($this->currentUserId != $auth->getCurrentUserId())
                {
                    throw new Exception("Player not equal current user", ErrorCode::INVALID_REQUEST);
                }

                $this->currentCharacterId = -1;
                break;

            case ScopeEnum::ScopeCharacter:
                if (!$hasReference)
                {
                    throw new Exception("No character reference", ErrorCode::INVALID_REQUEST);
                }
                $character = $characterMapper->selectById($this->currentReferenceId);
                $this->currentCharacterId = $this->currentReferenceId;

                // get game from character
                $this->currentGameId = intval($character["game_id"]);
                $game = $gameMapper->selectById($this->currentGameId);

                // get player from character
                $this->currentPlayerId = intval($character["player_id"]);
                $player = $playerMapper->selectById($this->currentPlayerId);
                $this->currentPlayerRoleFlags = intval($player["role_flags"]);

                // get user from player entry
                $this->currentUserId = intval($player["user_id"]);
                $user = $userMapper->selectById($this->currentUserId);
                $this->currentUserRoleFlags = intval($user["role_flags"]);

                // assert that the player matches the current user,
                if ($this->currentUserId != $auth->getCurrentUserId())
                {
                    throw new Exception("Character does not belong to current user", ErrorCode::INVALID_REQUEST);
                }

                $gameId = intval($player["game_id"]);
                if ($gameId != $this->currentGameId)
                {
                    throw new Exception("User is not player in this game", ErrorCode::INVALID_REQUEST);
                }
                break;
        }
    }

    // Helper

    private $currentScope = ScopeEnum::ScopeUser;
    private $currentReferenceId = -1;
    private $currentUserId = -1;
    private $currentUserRoleFlags = 0;
    private $currentGameId = -1;
    private $currentPlayerId = -1;
    private $currentPlayerRoleFlags = 0;
    private $currentCharacterId = -1;
}

?>