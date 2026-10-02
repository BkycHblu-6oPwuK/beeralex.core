<?php
declare(strict_types=1);

namespace Beeralex\Core\Dto;

use Bitrix\Main\Application;
/**
 *  DTO для хранения настроек кэширования
 *  Используется для передачи параметров кэширования между сервисами
 */
class CacheSettingsDTO
{
    public int $time;
    public readonly string $key;
    public readonly string $dir;

    public bool $fromCache = false;
    public bool $abortCache = false;
    public readonly bool $useTagCache;

    /**
     * @param string $dir Директория для кэширования относительно корня сайта, id сайта будет добавлен автоматически
     */
    public function __construct(
        int $time = 0,
        string $key = '',
        string $dir = '',
        bool $useTagCache = false
    ) {
        $siteId = Application::getInstance()->getContext()->getSite() ?: 's1';
        $this->time = $time;
        $this->key = $key;
        $this->dir = $this->normalizeDir($dir, $siteId);
        $this->useTagCache = $useTagCache;
    }

    private function normalizeDir(string $dir, string $siteId): string
    {
        if (!str_starts_with($dir, '/')) {
            $dir = '/' . $dir;
        }
        if (!str_starts_with($dir, '/' . $siteId . '/')) {
            $dir = '/' . $siteId . $dir;
        }
        return $dir;
    }
}
