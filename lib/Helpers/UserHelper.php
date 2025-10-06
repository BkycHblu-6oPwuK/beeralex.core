<?php

namespace Beeralex\Core\Helpers;

class UserHelper
{
    private function __construct() {}
    
    public static function generatePassword(int $length = 8) : string
    {
        $passwordChars = [
            'abcdefghijklnmopqrstuvwxyz',
            'ABCDEFGHIJKLNMOPQRSTUVWXYZ',
            '0123456789',
        ];

        return \Bitrix\Main\Security\Random::getStringByArray($length, $passwordChars);
    }

    public static function generateLogin(string $email) : string
    {
        $login = mb_strstr($email, '@', true);
        $login = mb_substr($login, 0, 47);

        while (\CUser::GetByLogin($login)->Fetch()) {
            $login = $login . mt_rand(0, 99999);
        }

        return str_pad($login, 3, '_');
    }

    public static function getDefaultUserGroups() : array
    {
        $defaultGroups = \Bitrix\Main\Config\Option::get('main', 'new_user_registration_def_group', '');
        return $defaultGroups ? explode(',', $defaultGroups) : [];
    }
}