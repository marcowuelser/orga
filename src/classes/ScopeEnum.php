<?php

class ScopeEnum
{
    const ScopeUser = 1;
    const ScopePlayer = 2;
    const ScopeCharacter = 4;

    public static function toString($value)
    {
        if (is_object($value))
        {
            return "Unknown";
        }

        switch ($value)
        {
            case ScopeEnum::ScopeUser:
                return "User";
            case ScopeEnum::ScopePlayer:
                return "Player";
            case ScopeEnum::ScopeCharacter:
                return "Character";
        }
        return "Unknown";
    }

    public static function toList()
    {
        return array(
            ScopeEnum::ScopeUser,
            ScopeEnum::ScopePlayer,
            ScopeEnum::ScopeCharacter,
        );
    }

    public static function toArray()
    {
        $array = array();
        foreach (ScopeEnum::toList() as $id)
        {
            $array[$id] = ScopeEnum::toString($id);
        }
        return $array;
    }

    public static function toAssocArray()
    {
        $array = array();
        foreach (ScopeEnum::toList() as $id)
        {
            $array[] = array("id" => $id, "name" => ScopeEnum::toString($id));
        }
        return $array;
    }

    public static function isValid($value)
    {
        foreach (ScopeEnum::toList() as $v)
        {
            if ($v == $value)
            {
                return true;
            }
        }
        return false;
    }
}

?>
