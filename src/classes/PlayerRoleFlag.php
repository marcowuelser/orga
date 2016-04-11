<?php
declare(strict_types=1);

class PlayerRoleFlag
{
    // Use flag type ids so the roles can be combined
    const RoleObserver = 1;
    const RolePlayer = 2;
    const RoleExtra = 4;
    const RoleDM = 8;

    public static function toString(int $value) : string
    {
        if (is_object($value))
        {
            return "Unknown";
        }

        switch ($value)
        {
            case PlayerRoleFlag::RoleObserver:
                return "Observer";
            case PlayerRoleFlag::RolePlayer:
                return "Player";
            case PlayerRoleFlag::RoleExtra:
                return "Extra";
            case PlayerRoleFlag::RoleDM:
                return "DM";
        }
        return "Unknown";
    }

    public static function toList() : array
    {
        return array(
            PlayerRoleFlag::RoleObserver,
            PlayerRoleFlag::RolePlayer,
            PlayerRoleFlag::RoleExtra,
            PlayerRoleFlag::RoleDM,
        );
    }

    public static function toArray() : array
    {
        $array = array();
        foreach (PlayerRoleFlag::toList() as $id)
        {
            $array[$id] = PlayerRoleFlag::toString($id);
        }
        return $array;
    }

    public static function toAssocArray() : array
    {
        $array = array();
        foreach (PlayerRoleFlag::toList() as $id)
        {
            $array[] = array("id" => $id, "name" => PlayerRoleFlag::toString($id));
        }
        return $array;
    }

    public static function checkFlag(int $value, int $flag) : bool
    {
        return ($value & $flag) > 0;
    }

    public static function setFlag(int $value, int $flag) : int
    {
        return ($value | $flag);
    }

    public static function clearFlag(int $value, int $flag) : int
    {
        return ($value & ~$flag);
    }
}

?>
