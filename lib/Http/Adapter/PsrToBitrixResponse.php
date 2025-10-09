<?php

namespace Beeralex\Core\Http\Adapter;

use Bitrix\Main\HttpResponse;
use Bitrix\Main\Web\HttpHeaders;
use Psr\Http\Message\ResponseInterface;

class PsrToBitrixResponse
{
    public static function convert(ResponseInterface $psrResponse): HttpResponse
    {
        $headers = new HttpHeaders();

        foreach ($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers->add($name, $value);
            }
        }

        return (new HttpResponse())
            ->setStatus($psrResponse->getStatusCode())
            ->setHeaders($headers)
            ->setContent((string)$psrResponse->getBody());
    }
}