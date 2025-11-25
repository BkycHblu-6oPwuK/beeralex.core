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

    public function transliterate(string $str, string $lang = 'en', array $params = [])
    {
        if ($lang === 'en' && LANGUAGE_ID === 'ru') {
            $translit = [
                'q' => 'й',
                'w' => 'ц',
                'e' => 'у',
                'r' => 'к',
                't' => 'е',
                'y' => 'н',
                'u' => 'г',
                'i' => 'ш',
                'o' => 'щ',
                'p' => 'з',
                '[' => 'х',
                ']' => 'ъ',
                'a' => 'ф',
                's' => 'ы',
                'd' => 'в',
                'f' => 'а',
                'g' => 'п',
                'h' => 'р',
                'j' => 'о',
                'k' => 'л',
                'l' => 'д',
                ';' => 'ж',
                "'" => 'э',
                'z' => 'я',
                'x' => 'ч',
                'c' => 'с',
                'v' => 'м',
                'b' => 'и',
                'n' => 'т',
                'm' => 'ь',
                ',' => 'б',
                '.' => 'ю',
                '/' => '.',
                ' ' => ' '
            ];

            $transliterated = '';
            $length = strlen($str);
            for ($i = 0; $i < $length; $i++) {
                $char = strtolower($str[$i]);
                if (isset($translit[$char])) {
                    if ($str[$i] === strtoupper($str[$i])) {
                        $transliterated .= strtoupper($translit[$char]);
                    } else {
                        $transliterated .= $translit[$char];
                    }
                } else {
                    $transliterated .= $char;
                }
            }

            return $transliterated;
        }
        return \CUtil::translit($str, $lang, $params);
    }
}
