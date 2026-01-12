<?php

declare(strict_types=1);

namespace Beeralex\Core\Dto;

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

    public bool $public = false;
    public bool $useEtag = false;
    public bool $staleWhileRevalidate = false;

    public bool $mustRevalidate = false;
    public bool $immutable = false;

    /** @var string[] */
    public array $vary = ['Accept-Encoding'];

    public function __construct(
        int $time = 0,
        string $key = '',
        string $dir = '',
        bool $public = false,
        bool $useEtag = false,
        bool $staleWhileRevalidate = false
    ) {
        $this->time = $time;
        $this->key = $key;
        $this->dir = $dir;
        $this->public = $public;
        $this->useEtag = $useEtag;
        $this->staleWhileRevalidate = $staleWhileRevalidate;
    }
}
