<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

class LanguageService
{
    /**
     * @param string[] $variants
     *
     * @example LanguageHelper::getPlural($periodTo, ['день', 'дня', 'дней'])
     */
    public function getPlural(int $number, array $variants): string
    {
        if ($number % 10 == 1 && $number % 100 != 11) {
            return $variants[0];
        }

        if ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20)) {
            return $variants[1];
        }

        return (string)$variants[2];
    }
}
