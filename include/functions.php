<?php

declare(strict_types=1);

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
            $configService = service(\Beeralex\Core\Config\Config::class);
            $moduleId = $configService->getModuleId();

            $logger = \Bitrix\Main\Diag\Logger::create($moduleId);
            if ($logger === null) {
                $logFile = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                    . 'local' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'log.log';
                $logger = new \Bitrix\Main\Diag\FileLogger($logFile);
            }
        }

        if (!is_array($data)) {
            $data = [$data];
        }
        $logger->info(print_r($data, true));
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

if (!function_exists('coreLog')) {
    function coreLog(string $message, int $traceDepth = 6, bool $showArgs = false): void
    {
        \AddMessage2Log($message, service(\Beeralex\Core\Config\Config::class)->getModuleId(), $traceDepth, $showArgs);
    }
}

if (!function_exists('isCli')) {
    /**
     * Проверяет, что скрипт запущен из-под cron
     *
     * @return bool
     */
    function isCli(): bool
    {
        if (defined('BX_CRONTAB') && BX_CRONTAB === true) {
            return true;
        }

        if (php_sapi_name() === 'cli') {
            return true;
        }

        return false;
    }
}
