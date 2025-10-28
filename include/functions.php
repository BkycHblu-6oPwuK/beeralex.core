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
            $logger = service(\Beeralex\Core\Logger\LoggerFactoryContract::class)->channel();
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
if (!function_exists('isImport')) {
    /**
     * Обмен с 1с или нет
     */
    function isImport(): bool
    {
        return $_REQUEST['mode'] == 'import';
    }
}

if (!function_exists('service')) {
    /**
     * @template T
     * @param class-string<T> $class
     * @return T
     */
    function service(string $class)
    {
        return \Bitrix\Main\DI\ServiceLocator::getInstance()->get($class);
    }
}
