<?php

namespace Beeralex\Core\Service\Api;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Uri;
use Beeralex\Core\Enum\Method;
use Beeralex\Core\Exceptions\ApiClientException;
use Beeralex\Core\Exceptions\ApiClientUnauthorizedException;
use Beeralex\Core\Exceptions\ApiTooManyRequestsException;

class ClientService extends HttpClient
{
    protected mixed $postData = null;

    public function __construct(?array $options = null)
    {
        parent::__construct($options);
    }

    public function setPostData(mixed $data)
    {
        $this->postData = $data;
        return $this;
    }

    public function getPostData(): mixed
    {
        return $this->postData;
    }

    /**
     * @throws \Throwable;
     */
    public function request(Method $method, Uri $uri): static
    {
        match ($method) {
            Method::GET => $this->get($uri->getLocator()),
            Method::POST => $this->post($uri->getLocator(), $this->getPostData()),
            default => null
        };
        $this->handleResult();
        return $this;
    }

    /**
     * @throws \Throwable
     * @throws ApiClientUnauthorizedException
     * @throws ApiTooManyRequestsException
     * @throws ApiClientException
     */
    protected function handleResult(): void
    {
        $status = $this->getStatus();
        if ($status === 401) throw new ApiClientUnauthorizedException('Client unauthorized');
        if ($status === 429) throw new ApiTooManyRequestsException("rate limit exceeded");
        if (!$this->isSuccess()) throw new ApiClientException('HTTP Request Failed');
    }

    public function isSuccess(): bool
    {
        $status = $this->getStatus();
        return $status > 0 && $status < 300;
    }
}
