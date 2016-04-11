<?php
declare(strict_types=1);

class UserRoleFlag
{
    // Use flag type ids so the roles can be combined
    const RoleGuest = 1;
    const RoleUser = 2;
    const RoleAuthor = 4;
    const RoleAdmin = 8;

    public static function toString(int $value) : string
    {
        if (is_object($value))
        {
            return "Unknown";
        }

        switch ($value)
        {
            case UserRoleFlag::RoleGuest:
                return "Guest";
            case UserRoleFlag::RoleUser:
                return "User";
            case UserRoleFlag::RoleAuthor:
                return "Author";
            case UserRoleFlag::RoleAdmin:
                return "Admin";
        }
        return "Unknown";
    }

    public static function toList() : array
    {
        return array(
            UserRoleFlag::RoleGuest,
            UserRoleFlag::RoleUser,
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
