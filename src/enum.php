<?php

class Match
{
    const EqualOrBetter = 1;
    const InSelection = 2;
}

class UserRole
{
    const RoleAdmin = 3;
    const RoleAuthor = 2;
    const RoleUser = 1;
    const RoleGuest = 0;

    public static function toString($value)
    {
        switch ($value)
        {
            case UserRole::RoleAdmin:
                return "Admin";
            case UserRole::RoleAuthor:
                return "Author";
            case UserRole::RoleUser:
                return "User";
            case UserRole::RoleGuest:
                return "Guest";
        }
        return "Unknown";
    }
}

?>