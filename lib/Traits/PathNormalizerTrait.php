<?php
declare(strict_types=1);
namespace Beeralex\Core\Traits;

trait PathNormalizerTrait
{
    /**
     * Нормализация пути к базовой директории (если путь указывает на файл, вернёт директорию файла)
     */
    protected function normalizeBaseDir(string $path): string
    {
        $real = realpath($path);
        if ($real !== false) {
            if (is_file($real)) {
                return dirname($real);
            }
            return $real;
        }
        $dir = dirname($path);
        if ($dir !== '.' && $dir !== $path) {
            $real = realpath($dir);
            if ($real !== false) {
                return $real;
            }
            return $dir;
        }
        return $path;
    }
}
