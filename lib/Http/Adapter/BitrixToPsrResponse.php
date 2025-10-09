<?php

namespace Beeralex\Core\Http\Adapter;

use Beeralex\Core\Helpers\WebHelper;
use Bitrix\Main\HttpResponse;
use GuzzleHttp\Psr7\Response;
use Bitrix\Main\Context;

class BitrixToPsrResponse
{
    public static function convert(HttpResponse $response): Response
    {
        $serverProtocol = Context::getCurrent()->getServer()->get('SERVER_PROTOCOL') ?? 'HTTP/1.0';
        $statusCode = (int) preg_replace('/\D/', '', $response->getStatus() ?? '') ?: 200;

        return new Response(
            $statusCode,
            WebHelper::collectHttpHeaders($response->getHeaders()),
            $response->getContent(),
            WebHelper::parseHttpProtocolVersion($serverProtocol)
        );
    }
}