<?php
declare(strict_types=1);
namespace Beeralex\Core\Http;

use Beeralex\Core\Http\Adapter\BitrixToPsrRequest;
use Beeralex\Core\Http\Adapter\BitrixToPsrResponse;
use Beeralex\Core\Http\Adapter\PsrToBitrixRequest;
use Beeralex\Core\Http\Adapter\PsrToBitrixResponse;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\HttpResponse;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Класс-фасад для преобразования HTTP-сущностей между Bitrix и PSR.
 */
class HttpFactory
{
    /**
     * Преобразовать Bitrix HttpRequest → PSR ServerRequestInterface
     */
    public function fromBitrixRequest(HttpRequest $request): ServerRequestInterface
    {
        return service(BitrixToPsrRequest::class)->convert($request);
    }

    /**
     * Преобразовать Bitrix HttpResponse → PSR ResponseInterface
     */
    public function fromBitrixResponse(HttpResponse $response): ResponseInterface
    {
        return service(BitrixToPsrResponse::class)->convert($response);
    }

    /**
     * Преобразовать PSR ServerRequestInterface → Bitrix HttpRequest
     */
    public function toBitrixRequest(ServerRequestInterface $psrRequest): HttpRequest
    {
        return service(PsrToBitrixRequest::class)->convert($psrRequest);
    }

    /**
     * Преобразовать PSR ResponseInterface → Bitrix HttpResponse
     */
    public function toBitrixResponse(ResponseInterface $psrResponse): HttpResponse
    {
        return service(PsrToBitrixResponse::class)->convert($psrResponse);
    }

    /**
     * Создать пустой PSR Response (например, для middleware)
     */
    public function createEmptyResponse(int $status = 200): ResponseInterface
    {
        return new Response($status);
    }

    /**
     * Создать пустой PSR Request (например, для тестов)
     */
    public function createEmptyRequest(string $method = 'GET', string $uri = '/'): ServerRequestInterface
    {
        return new ServerRequest($method, $uri);
    }
}
