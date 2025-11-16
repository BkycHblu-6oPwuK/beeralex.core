<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

class PathService
{
    /**
     * Вернёт директорию, где лежит указанный класс
     */
    public function classDir(string $className): string
    {
        $reflection = new \ReflectionClass($className);
        return dirname($reflection->getFileName());
    }

    /**
     * Вернёт полный путь к файлу класса
     */
    public function classFile(string $className): string
    {
        $reflection = new \ReflectionClass($className);
        return $reflection->getFileName();
    }

    /**
     * Вернёт текущую рабочую директорию (как shell `pwd`)
     */
    public function cwd(): string
    {
        return getcwd();
    }

    /**
     * Нормализация пути (уберёт `..`, `.` и лишние слэши)
     */
    public function normalize(string $path): string
    {
        return realpath($path) ?: $path;
    }

    public function getCurUri(): \Bitrix\Main\Web\Uri
    {
        $server = \Bitrix\Main\Context::getCurrent()->getServer();
        $host = $server->getHttpHost();
        $scheme = $server->getRequestScheme();
        return new \Bitrix\Main\Web\Uri($scheme . '://' . $host . $server->getRequestUri());
    }
}
