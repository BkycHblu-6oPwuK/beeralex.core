<?php

declare(strict_types=1);

namespace Beeralex\Core\Traits;

use Bitrix\Main\Data\Cache;
use Beeralex\Core\Dto\CacheSettingsDTO;
use Beeralex\Core\Http\Resources\Resource;
use Bitrix\Main\Result;

trait Cacheable
{
    protected readonly Cache $cache;

    protected function initCacheInstance(): void
    {
        if (!isset($this->cache)) {
            $this->cache = Cache::createInstance();
        }
    }

    public function getCacheSettingsDto(
        int $time,
        string $key,
        string $dir = '',
        bool $public = false,
        bool $useEtag = false,
        bool $staleWhileRevalidate = false
    ): CacheSettingsDTO {
        return new CacheSettingsDTO(
            time: $time,
            key: $key,
            dir: $dir,
            public: $public,
            useEtag: $useEtag,
            staleWhileRevalidate: $staleWhileRevalidate
        );
    }

    /**
     * Получает данные из кеша или выполняет callback и кеширует результат. Bitrix кеширование.
     */
    protected function getCached(CacheSettingsDTO $cacheSettings, callable $callback)
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

    /**
     * Применяет HTTP кеширование через заголовки
     */
    protected function applyHttpCache(CacheSettingsDTO $cacheSettings): void
    {
        if ($cacheSettings->time <= 0) {
            header('Cache-Control: no-store');
            header('Pragma: no-cache');
            header('Expires: 0');
            return;
        }

        if (!$cacheSettings->public) {
            header('Cache-Control: private, no-store');
            header('Vary: ' . implode(', ', $cacheSettings->vary));
            return;
        }

        $directives = [
            'public',
            'max-age=' . $cacheSettings->time,
        ];

        if ($cacheSettings->staleWhileRevalidate) {
            $directives[] = 'stale-while-revalidate=' . min(300, $cacheSettings->time);
        }

        if ($cacheSettings->mustRevalidate) {
            $directives[] = 'must-revalidate';
        }

        if ($cacheSettings->immutable) {
            $directives[] = 'immutable';
        }

        header('Cache-Control: ' . implode(', ', $directives));
        header('Vary: ' . implode(', ', $cacheSettings->vary));
    }

    /**
     * Применяет ETag заголовок и пытается вернуть 304 Not Modified, вычисление хеша на основе полезной нагрузки, примерно в конце запроса. Экономит трафик при больших ответах.
     */
    protected function applyEtag(
        string|array|Result|Resource $payload,
        CacheSettingsDTO $cacheSettings
    ): void {
        if (!$cacheSettings->useEtag || !$cacheSettings->public) {
            return;
        }

        $body = match (true) {
            is_string($payload) => $payload,
            is_array($payload) => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $payload instanceof Result => json_encode($payload->getData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $payload instanceof Resource => json_encode($payload->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => null,
        };

        if ($body === null || $body === '') {
            return;
        }

        $etag = 'W/"' . sha1($body) . '"';
        header('ETag: ' . $etag);

        if (
            isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
            trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag
        ) {
            http_response_code(304);
            exit;
        }
    }

    /**
     * Пытается вернуть 304 Not Modified на основе ключа кеша, без вычисления всего ответа, примерно в начале запроса. Экономит ресурсы сервера при больших ответах. Нужно гарантировать уникальность ключа кеша, если данные меняются, иначе всегда будет 304.
     */
    protected function tryReturn304FromCacheKey(CacheSettingsDTO $cacheSettings): void
    {
        if (!$cacheSettings->useEtag || !$cacheSettings->public) {
            return;
        }

        $etag = 'W/"' . sha1($cacheSettings->key) . '"';
        header('ETag: ' . $etag);

        if (
            isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
            trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag
        ) {
            http_response_code(304);
            exit;
        }
    }
}
