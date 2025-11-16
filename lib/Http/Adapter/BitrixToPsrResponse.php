<?php
declare(strict_types=1);
namespace Beeralex\Core\Http\Adapter;

use Beeralex\Core\Service\WebService;
use Bitrix\Main\HttpResponse;
use GuzzleHttp\Psr7\Response;
use Bitrix\Main\Context;

class BitrixToPsrResponse
{
    public function __construct(
        protected WebService $webService
    ){}

    public function convert(HttpResponse $response): Response
    {
        $serverProtocol = Context::getCurrent()->getServer()->get('SERVER_PROTOCOL') ?? 'HTTP/1.0';
        $statusCode = (int) preg_replace('/\D/', '', (string)$response->getStatus() ?? '') ?: 200;

        return new Response(
            $statusCode,
            $this->webService->collectHttpHeaders($response->getHeaders()),
            $response->getContent(),
            $this->webService->parseHttpProtocolVersion($serverProtocol)
        );
    }
}