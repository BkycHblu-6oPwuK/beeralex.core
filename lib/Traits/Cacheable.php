<?php
declare(strict_types=1);
namespace Beeralex\Core\Traits;

use Bitrix\Main\Data\Cache;
use Beeralex\Core\Dto\CacheSettingsDto;

trait Cacheable
{
    protected readonly Cache $cache;

    protected function initCacheInstance(): void
    {
        if (!isset($this->cache)) {
            $this->cache = Cache::createInstance();
        }
    }

    protected function getCached(CacheSettingsDto $cacheSettings, callable $callback)
    {
        $this->initCacheInstance();

        try {
            $cacheSettings->fromCache = false;
            if ($cacheSettings->time > 0) {
                if ($this->cache->initCache($cacheSettings->time, $cacheSettings->key, $cacheSettings->dir)) {
                    $cacheSettings->fromCache = true;
                    return $this->cache->getVars();
                } elseif ($this->cache->startDataCache()) {
                    $result = $callback();
                    if (empty($result)) {
                        throw new \RuntimeException('Error getting data when requesting API');
                    }
                    if ($cacheSettings->abortCache) {
                        $cacheSettings->abortCache = false;
                        $this->cache->abortDataCache();
                        return $result;
                    }
                    $this->cache->endDataCache($result);

                    return $result;
                }
            }

            $result = $callback();
            if (empty($result)) {
                throw new \RuntimeException('Error getting data when requesting API');
            }
            return $result;
        } catch (\Exception $e) {
            $this->cache->abortDataCache();
            throw $e;
        }
    }
}
