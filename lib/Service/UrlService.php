<?php

declare(strict_types=1);

namespace Beeralex\Core\Service;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Loader;

class UrlService
{
    protected readonly array $removeParts;
    protected readonly ?bool $trailingSlash;

    public function __construct()
    {
        Loader::requireModule('iblock');
        $this->removeParts = static::getRemoveParts();
        $this->trailingSlash = static::getTrailingSlash();
    }

    /**
     * Получает из конфигурации части URL, которые нужно удалять
     */
    public static function getRemoveParts(): array
    {
        $config = Configuration::getInstance()->get('beeralex.core');
        return $config['url_remove_parts'] ?? Configuration::getInstance('beeralex.core')->get('url_remove_parts') ?? [];
    }

    /**
     * Получает из конфигурации поведение слеша на конце URL по умолчанию.
     * true — добавлять, false — удалять, null — авто (сохраняет слеш оригинального URL)
     */
    public static function getTrailingSlash(): ?bool
    {
        $config = Configuration::getInstance()->get('beeralex.core');
        $value = $config['url_trailing_slash'] ?? Configuration::getInstance('beeralex.core')->get('url_trailing_slash');
        return isset($value) ? (bool)$value : null;
    }

    /**
     * Удаляет указанные части из URL
     *
     * @param string $url исходный URL
     * @param ?bool $trailingSlash слеш на конце:
     *   null — использует значение из конфига (trailing_slash), а если оно не задано — авто (сохраняет слеш оригинального URL);
     *   true — принудительно добавить; false — принудительно удалить
     */
    public function cleanUrl(string $url, ?bool $trailingSlash = null): string
    {
        $hasTrailingSlash = $trailingSlash ?? $this->trailingSlash ?? str_ends_with($url, '/');

        foreach ($this->removeParts as $part) {
            $url = preg_replace('#(^|/)' . preg_quote($part, '#') . '(/|$)#', '/', $url);
        }

        $url = preg_replace('#/+#', '/', $url);
        $url = '/' . trim($url, '/');

        if ($url === '/') {
            return '/';
        }

        return $hasTrailingSlash ? $url . '/' : $url;
    }

    /**
     * Возвращает URL раздела инфоблока
     * @param array{
     *     CODE: string,
     *     ID: string
     * } $sectionFields
     * @return array{
     *     url: string,
     *     clean_url: string,
     * }
     */
    public function getSectionUrl(array $sectionFields, string $template, bool $serverName = false, string $arrType = 'S'): array
    {
        $url = \CIBlock::ReplaceSectionUrl($template, $sectionFields, $serverName, $arrType);
        return ['url' => $url, 'clean_url' => $this->cleanUrl($url)];
    }

    /**
     * Возвращает URL элемента инфоблока
     * @param array{
     *     CODE: string,
     *     ID: string
     *     IBLOCK_SECTION_ID: int
     * } $elementFields
     * @return array{
     *     url: string,
     *     clean_url: string,
     * }
     */
    public function getDetailUrl(array $elementFields, string $template, bool $serverName = false, string $arrType = 'E'): array
    {
        $url = \CIBlock::ReplaceDetailUrl($template, $elementFields, $serverName, $arrType);
        return ['url' => $url, 'clean_url' => $this->cleanUrl($url)];
    }
}
