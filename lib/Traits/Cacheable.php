<?php

declare(strict_types=1);

namespace Beeralex\Core\Traits;

use Bitrix\Main\Data\Cache;
use Beeralex\Core\Dto\CacheSettingsDTO;
use Bitrix\Main\Application;

trait Cacheable
{
    protected readonly Cache $cachebleInstance;
    private bool $tagCacheStarted = false;

    protected function initCacheInstance(): void
    {
        if (!isset($this->cachebleInstance)) {
            $this->cachebleInstance = Application::getInstance()->getCache();
        }
    }

    /**
     * @param string $dir Директория для кэширования относительно корня сайта, id сайта будет добавлен автоматически
     */
    public function getCacheSettingsDto(
        int $time,
        string $key,
        string $dir = '',
        bool $useTagCache = false
    ): CacheSettingsDTO {
        return new CacheSettingsDTO(
            time: $time,
            key: $key,
            dir: $dir,
            useTagCache: $useTagCache
        );
    }

    public function generateCacheId(mixed ...$keys): string
    {
        return md5(serialize($keys));
    }

    /**
     * Получает данные из кеша или выполняет callback и кеширует результат. Bitrix кеширование.
     *
     * @param CacheSettingsDTO $cacheSettings Настройки кеша. Если задан tagCachePath (или dir),
     *        вокруг записи данных открывается тегированный кеш.
     * @param callable $callback fn(): mixed — получение данных.
     * @param callable|null $onCache fn(mixed $result, \Bitrix\Main\Data\TaggedCache $taggedCache): void
     *        Вызывается если useTagCache === true в CacheSettingsDTO и после получения $result и ДО endTagCache. Здесь вызывающий код сам регистрирует нужные теги.
     */
    protected function getCached(CacheSettingsDTO $cacheSettings, callable $callback, ?callable $onCache = null)
    {
        $this->initCacheInstance();

        try {
            $cacheSettings->fromCache = false;
            if ($cacheSettings->time > 0) {
                if ($this->cachebleInstance->initCache($cacheSettings->time, $cacheSettings->key, $cacheSettings->dir)) {
                    $cacheSettings->fromCache = true;
                    return $this->cachebleInstance->getVars();
                } elseif ($this->cachebleInstance->startDataCache()) {
                    $this->startTagCache($cacheSettings);
                    $result = $callback();
                    if (empty($result)) {
                        throw new \RuntimeException('Error getting data when requesting API');
                    }
                    if ($cacheSettings->abortCache) {
                        $cacheSettings->abortCache = false;
                        $this->abortTagCache($cacheSettings);
                        $this->cachebleInstance->abortDataCache();
                        return $result;
                    }

                    if ($cacheSettings->useTagCache && $onCache !== null && $this->tagCacheStarted) {
                        $onCache($result, Application::getInstance()->getTaggedCache());
                    }

                    $this->cachebleInstance->endDataCache($result);
                    $this->endTagCache($cacheSettings);

                    return $result;
                }
            }

            $result = $callback();
            if (empty($result)) {
                throw new \RuntimeException('Error getting data when requesting API');
            }
            return $result;
        } catch (\Exception $e) {
            $this->cachebleInstance->abortDataCache();
            $this->abortTagCache($cacheSettings);
            throw $e;
        }
    }

    private function startTagCache(CacheSettingsDTO $cacheSettings): void
    {
        if (!$cacheSettings->useTagCache || $cacheSettings->dir === '') {
            return;
        }
        Application::getInstance()->getTaggedCache()->startTagCache($cacheSettings->dir);
        $this->tagCacheStarted = true;
    }

    private function endTagCache(CacheSettingsDTO $cacheSettings): void
    {
        if (!$cacheSettings->useTagCache || !$this->tagCacheStarted) {
            return;
        }
        Application::getInstance()->getTaggedCache()->endTagCache();
        $this->tagCacheStarted = false;
    }

    private function abortTagCache(CacheSettingsDTO $cacheSettings): void
    {
        if (!$cacheSettings->useTagCache || !$this->tagCacheStarted) {
            return;
        }
        Application::getInstance()->getTaggedCache()->abortTagCache();
        $this->tagCacheStarted = false;
    }
}
