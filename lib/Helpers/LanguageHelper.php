<?php

namespace Beeralex\Core\Helpers;

class LanguageHelper
{
    private function __construct() {}

    /**
     * @param int $number
     * @param string[] $variants
     * @return string
     *
     * @example LanguageHelper::getPlural($periodTo, ['день', 'дня', 'дней'])
     */
    public static function getPlural(int $number, array $variants): string
    {
        if ($number % 10 == 1 && $number % 100 != 11) {
            return $variants[0];
        }

        if ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20)) {
            return $variants[1];
        }

        return (string)$variants[2];
    }

    /**
     * @param int $number
     * @param string[] $variants
     * @return string
     *
     * @example getPluralFrom от ['дня', 'дней'])
     */
    public static function getPluralFrom(int $number, array $variants): string
    {
        if ($number % 10 == 1 && $number % 100 != 11) {
            return $variants[0];
        }

        return $variants[1];
    }
}
