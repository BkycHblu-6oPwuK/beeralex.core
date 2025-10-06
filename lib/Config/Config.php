<?php

namespace Beeralex\Core\Config;

final class Config extends AbstractOptions
{
    public readonly string $mode;
    public readonly string $viteBasePath;
    public readonly string $viteClientPath;
    public readonly string $vitePort;
    public readonly bool $isEnableSsr;
    public readonly string $viteSsrPort;
    public readonly string $viteSsrHost;
    public readonly string $pathToPublicImages;
    public readonly string $actionLoadItems;

    protected function mapOptions(array $options): void
    {
        $this->mode = $_ENV['MODE'] ?? 'production';
        $this->viteBasePath = $_ENV['VITE_BASE_PATH'] ?? '';
        $this->viteClientPath = $_ENV['VITE_CLIENT_PATH'] ?? '';
        $this->vitePort = $_ENV['VITE_PORT'] ?? '';
        $this->isEnableSsr = $_ENV['VITE_SSR_ENABLE'] == 1;
        $this->viteSsrPort = $_ENV['VITE_SSR_PORT'] ?? '';
        $this->viteSsrHost = $_ENV['VITE_SSR_HOST'] ?? '';
        $this->pathToPublicImages = "/{$this->viteBasePath}/public";
        $this->actionLoadItems = 'loadItems';
        foreach ($options as $key => $value) {
            $value = match ($value) {
                'Y' => true,
                'N' => false,
                default => $value
            };
            $this->{$key} = $value;
        }
    }

    public function isProduction(): bool
    {
        return $this->mode === 'production';
    }

    public function __get(string $property): mixed
    {
        return parent::__get($property) ?? $_ENV[$property] ?? null;
    }

    public function getImagePublicPath(string $path)
    {
        return $this->pathToPublicImages . $path;
    }

    public function getModuleId(): string
    {
        return 'beeralex.core';
    }
}
