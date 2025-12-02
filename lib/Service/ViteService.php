<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Main\Page\Asset;
use Beeralex\Core\Config\Config;
use Bitrix\Main\Web\Json;

/**
 * Класс для подключения js и css из vite
 */
class ViteService
{
    protected static $instance;
    protected string $manifestPath;
    protected string $basePath;
    protected array $manifest;
    protected string $clientDirectoryPath = '';
    protected string $localhostBasePath = '';
    protected bool $viteClientIsIncluded = false;
    protected readonly Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $clientDirectoryPath = $config->viteClientPath;
        if ($clientDirectoryPath) $this->clientDirectoryPath = $clientDirectoryPath . '/';
        $this->basePath = '/' . $config->viteBasePath . '/';
        $this->manifestPath = "{$_SERVER['DOCUMENT_ROOT']}{$this->basePath}{$this->clientDirectoryPath}.vite/manifest.json";

        if ($config->isProduction()) {
            $this->loadManifest();
        } else {
            $vitePort = $config->vitePort;
            $this->localhostBasePath = "http://localhost:{$vitePort}{$this->basePath}";
        }
    }

    /**
     * Загружает manifest.json
     *
     * @return void
     */
    protected function loadManifest(): void
    {
        if (!file_exists($this->manifestPath)) {
            throw new \Exception("Manifest file not found: " . $this->manifestPath);
        }

        $this->manifest = Json::decode(file_get_contents($this->manifestPath) ?: '');
        if ($this->manifest === null) {
            throw new \Exception("Failed to decode manifest file: " . json_last_error_msg());
        }
    }

    /**
     * Рекурсивно обходит импорты и заносит пути до css файлов в массив
     */
    protected function collectCssImports(string $entry, array &$cssFiles): void
    {
        if (isset($this->manifest[$entry])) {
            $asset = $this->manifest[$entry];
            if (isset($asset['css'])) {
                foreach ($asset['css'] as $cssFile) {
                    $cssFiles[] = $this->basePath . $this->clientDirectoryPath . $cssFile;
                }
            }
            if (isset($asset['imports'])) {
                foreach ($asset['imports'] as $import) {
                    $this->collectCssImports($import, $cssFiles);
                }
            }
        }
    }

    /**
     * Получает пути до js и css файлов. Для prod среды js и css. Для dev только js, css импортируем в js
     */
    protected function getAssetPaths(array $entries): array
    {
        $assets = [
            'js' => [],
            'css' => []
        ];

        if ($this->config->isProduction()) {
            foreach ($entries as $entry) {
                if (isset($this->manifest[$entry])) {
                    $asset = $this->manifest[$entry];
                    $assets['js'][] = [
                        'file' => $this->basePath . $this->clientDirectoryPath . $asset['file'],
                        'issetImports' => isset($asset['imports'])
                    ];
                    $this->collectCssImports($entry, $assets['css']);
                }
            }
        } else {
            if (!$this->viteClientIsIncluded) {
                $assets['js'][]['file'] = $this->localhostBasePath . '@vite/client';
                $this->viteClientIsIncluded = true;
            }
            foreach ($entries as $entry) {
                $assets['js'][]['file'] = $this->localhostBasePath . $entry;
            }
        }
        return $assets;
    }

    /**
     * @param string $entry src/js/main.js
     */
    public function include(string $entry) : void
    {
        $this->includeAssets([$entry]);
    }

    /**
     * Подключает js через Asset::addJs, js type module через Asset::addString если есть импорты, и css через Asset::addCss. Для prod среды js и css. Для dev только js, css импортируем в js
     *
     * @param string[] $entries относительно директории в которой расположен vite - src/js/main.js
     * @return void
     */
    public function includeAssets(array $entries): void
    {
        $assets = $this->getAssetPaths($entries);
        $bitrixAssetObj = Asset::getInstance();
        foreach ($assets['js'] as $jsInfo) {
            $jsFile = htmlspecialchars($jsInfo['file'], ENT_QUOTES);
            $bitrixAssetObj->addString("<script type='module' src='{$jsFile}'></script>", true);
        }
        if ($this->config->isProduction() && !empty($assets['css'])) {
            foreach ($assets['css'] as $cssFile) {
                $bitrixAssetObj->addCss(htmlspecialchars($cssFile, ENT_QUOTES), true);
            }
        }
    }
}
