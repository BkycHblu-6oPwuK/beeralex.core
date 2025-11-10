<?php

namespace Beeralex\Core\Helpers;

class PathHelper
{
    private function __construct() {}

    /**
     * Вернёт директорию, где лежит указанный класс
     */
    public static function classDir(string $className): string
    {
        $reflection = new \ReflectionClass($className);
        return dirname($reflection->getFileName());
    }

    /**
     * Вернёт полный путь к файлу класса
     */
    public static function classFile(string $className): string
    {
        $reflection = new \ReflectionClass($className);
        return $reflection->getFileName();
    }

    /**
     * Вернёт текущую рабочую директорию (как shell `pwd`)
     */
    public static function cwd(): string
    {
        return getcwd();
    }

    /**
     * Нормализация пути (уберёт `..`, `.` и лишние слэши)
     */
    public static function normalize(string $path): string
    {
        return realpath($path) ?: $path;
    }

    public static function getCurUri(): \Bitrix\Main\Web\Uri
    {
        $server = \Bitrix\Main\Context::getCurrent()->getServer();
        $host = $server->getHttpHost();
        $scheme = $server->getRequestScheme();
        return new \Bitrix\Main\Web\Uri($scheme . '://' . $host . $server->getRequestUri());
    }
}
