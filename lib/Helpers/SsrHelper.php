<?php

namespace Beeralex\Core\Helpers;

use Beeralex\Core\Config\Config;

class SsrHelper
{
    private function __construct() {}
    
    /**
     * получает html переданной страницы с сервера node для ssr, VITE_SSR_ENABLE должно быть установлено в значение 1
     * @throws InvalidArgumentException если не объявлены VITE_SSR_HOST, VITE_SSR_PORT и MODE 
     */
    public static function getSsrContent(string $page, ?array $data = null): ?string
    {
        $config = Config::getInstance();
        if (!$config->isEnableSsr || !$config->isProduction()) return null;
        $httpClient = new \Bitrix\Main\Web\HttpClient();
        $httpClient->setHeader('Content-Type', 'application/json', true);
        $response = $httpClient->post(self::getSsrServerUrl() . "/{$page}", $data ? \Bitrix\Main\Web\Json::encode(['data' => $data]) : $data);
        if ($response && $httpClient->getStatus() === 200) {
            return $response;
        } else {
            return null;
        }
    }

    /**
     * Возвращает url ssr сервера с протоколом http
     * @throws InvalidArgumentException если не объявлены VITE_SSR_HOST и VITE_SSR_PORT
     */
    public static function getSsrServerUrl(): string
    {
        static $ssrServerUrl = null;
        if ($ssrServerUrl === null) {
            $config = Config::getInstance();
            $host = $config->viteSsrHost;
            $port = $config->viteSsrPort;
            $ssrServerUrl = "http://{$host}:{$port}";
        }
        return $ssrServerUrl;
    }

    /**
     * Доступен ли node js сервер для ssr
     * @throws InvalidArgumentException если не объявлены VITE_SSR_HOST и VITE_SSR_PORT
     */
    public static function ssrServerIsAvailable(): bool
    {
        $httpClient = new \Bitrix\Main\Web\HttpClient();
        return $httpClient->get(self::getSsrServerUrl()) !== false;
    }
}
