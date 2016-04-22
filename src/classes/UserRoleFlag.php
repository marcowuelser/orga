<?php
declare(strict_types=1);

class UserRoleFlag
{
    // Use flag type ids so the roles can be combined
    const RoleUser = 1;
    const RoleOrganisator = 2;
    const RoleAuthor = 4;
    const RoleAdmin = 8;

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
            return "Guest";
        }

        $str = "";
        if ($value & UserRoleFlag::RoleUser)
        {
            $str = concatenate($str, "User");
        }
        if ($value & UserRoleFlag::RoleOrganisator)
        {
            $str = concatenate($str, "Organisator");
        }
        if ($value & UserRoleFlag::RoleAuthor)
        {
            $str = concatenate($str, "Author");
        }
        if ($value & UserRoleFlag::RoleAdmin)
        {
            $str = concatenate($str, "Admin");
        }

        return $str;
    }

    public static function toList() : array
    {
        return array(
            UserRoleFlag::RoleUser,
            UserRoleFlag::RoleOrganisator,
            UserRoleFlag::RoleAuthor,
            UserRoleFlag::RoleAdmin,
        );
    }

    public static function toArray() : array
    {
        $array = array();
        foreach (UserRoleFlag::toList() as $id)
        {
            $array[$id] = UserRoleFlag::toString($id);
        }
        return $array;
    }

    public static function toAssocArray() : array
    {
        $array = array();
        foreach (UserRoleFlag::toList() as $id)
        {
            $array[] = array("id" => $id, "name" => UserRoleFlag::toString($id));
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
