<?php

namespace Beeralex\Core\Service\Api;

use Beeralex\Core\Config\AbstractOptions;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Beeralex\Core\Dto\CacheSettingsDto;
use Beeralex\Core\Enum\Method;
use Beeralex\Core\Exceptions\ApiClientException;
use Beeralex\Core\Traits\Cacheable;

/**
 * Базовый класс для API сервисов
 * 
 * @template T of AbstractOptions
 * @template U of ClientService
 */
abstract class ApiService
{
    use Cacheable;
    
    /** @var U */
    protected readonly ClientService $clientService;
    
    /** @var T */
    protected readonly AbstractOptions $options;

    /**
     * @param T $moduleOptions
     * @param U $clientService
     */
    public function __construct(AbstractOptions $moduleOptions, ClientService $clientService)
    {
        $this->clientService = $clientService;
        $this->options = $moduleOptions;
    }

    /**
     * @param Uri $uri адрес запроса
     * @param null|array $data ключ-значение
     * @param null|array $headers ключ-значение
     */
    protected function get(Uri $uri, ?array $data = null, ?array $headers = null, ?CacheSettingsDto $cacheSettings = null): array
    {
        if ($data) $uri->addParams($data);
        if ($headers) $this->clientService->setHeaders($headers);
        return $this->request(Method::GET, $uri, $cacheSettings);
    }

    /**
     * @param Uri $uri адрес запроса
     * @param mixed $data
     * @param null|array $headers ключ-значение
     */
    protected function post(Uri $uri, mixed $data = null, ?array $headers = null, ?CacheSettingsDto $cacheSettings = null): array
    {
        $this->clientService->setPostData($data);
        if ($headers) $this->clientService->setHeaders($headers);
        return $this->request(Method::POST, $uri, $cacheSettings);
    }

    private function request(Method $method, Uri $uri, ?CacheSettingsDto $cacheSettings = null): array
    {
        try {
            $cacheSettings ??= new CacheSettingsDto;

            $result = $this->getCached($cacheSettings, function () use ($method, $uri) {
                return $this->handleResult($this->clientService->request($method, $uri)->getResult());
            });

            return $result;
        } catch (ApiClientException $e) {
            $error = $this->clientService->getError();
            $result = $this->handleResult($this->clientService->getResult());
            if (!empty($result)) {
                $error = [
                    'http_error' => $error,
                    'api_error' => $result,
                ];
            }
            $error['status'] = $this->clientService->getStatus();
            $this->log($e->getMessage() . ' ' . Json::encode($error));
            throw $e;
        } catch (\Throwable $e) {
            $this->log($e->getMessage());
            throw $e;
        }
    }

    public function log(string $text, int $traceDepth = 6, bool $showArgs = false): void
    {
        if ($this->logsEnabled()) {
            coreLog($text, $traceDepth, $showArgs);
        }
    }

    public function logsEnabled(): bool
    {
        return false;
    }

    protected function handleResult(mixed $result): array
    {
        try {
            return Json::decode($result);
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }
        return [];
    }
}
