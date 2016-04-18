<?php
declare(strict_types=1);

class PlayerRoleFlag
{
    // Use flag type ids so the roles can be combined
    const RolePlayer = 1;
    const RoleExtra = 2;
    const RoleDM = 4;

    public static function toString(int $value) : string
    {
        if (is_object($value))
        {
            return "Unknown";
        }
        if ($value < 0)
        {
            return "Unknown";
        }

        if ($value == 0)
        {
            return "Observer";
        }

        $first = true;
        $str = "";
        if ($value & PlayerRoleFlag::RolePlayer)
        {
            $str = concatenate($str, "Player", $first);
        }
        if ($value & PlayerRoleFlag::RoleExtra)
        {
            $str = concatenate($str, "Extra", $first);
        }
        if ($value & PlayerRoleFlag::RoleDM)
        {
            $str = concatenate($str, "DM", $first);
        }

        return $str;
    }

    public static function toList() : array
    {
        return array(
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
