<?php

if (!function_exists('firstNotEmpty')) {
    /**
     * Возвращает первое непустое значение или значение по умолчанию
     *
     * @param mixed $default
     * @param mixed ...$values
     * @return mixed
     */
    function firstNotEmpty(mixed $default, ...$values): mixed
    {
        foreach ($values as $value) {
            if (!empty($value)) {
                return $value;
            }
        }
        return $default;
    }
}
if (!function_exists('toFile')) {
    function toFile(mixed $data): void
    {
        static $logger = null;
        if ($logger === null) {
            $logger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get(\Beeralex\Core\Logger\LoggerFactoryContract::class)->channel();
        }
        if (!is_array($data)) {
            $data = [$data];
        }
        $logger->info('', $data);
    }
}
if (!function_exists('isLighthouse')) {
    function isLighthouse(): bool
    {
        return (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse') !== false);
    }
}
if (!function_exists('isLettersUppercase')) {
    /**
     * Проверяет являются ли символы строки заглавными, по умолчанию игнорирует цифры и прочие символы
     */
    function isLettersUppercase(string $str, bool $ignoreNonLetters = true): bool
    {
        if ($ignoreNonLetters) {
            $letters = preg_replace('/[^A-Za-zА-Яа-яЁё]/u', '', $str);
            return $letters !== '' && mb_strtoupper($letters) === $letters;
        } else {
            return preg_match('/^[A-ZА-ЯЁ]+$/u', $str);
        }
    }
}
if (!function_exists('containsOnlyLetters')) {
    /**
     * Проверяет является ли символы в строке только буквами
     */
    function containsOnlyLetters(string $str): bool
    {
        $str = trim($str);
        return $str !== '' && preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $str) === 1;
    }
}
if (!function_exists('isImport')) {
    /**
     * Обмен с 1с или нет
     */
    function isImport(): bool
    {
        return $_REQUEST['mode'] == 'import';
    }
}
