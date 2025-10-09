<?php

namespace Beeralex\Core\Http\Adapter;

use Beeralex\Core\Helpers\WebHelper;
use Bitrix\Main\HttpRequest;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class BitrixToPsrRequest
{
    public static function convert(HttpRequest $request): ServerRequestInterface
    {
        $serverRequest = new ServerRequest(
            $request->getRequestMethod(),
            $request->getRequestUri(),
            WebHelper::collectHttpHeaders($request->getHeaders()),
            HttpRequest::getInput(),
            WebHelper::parseHttpProtocolVersion($request->getServer()->get('SERVER_PROTOCOL')),
            $request->getServer()->toArray()
        );

        return $serverRequest
            ->withCookieParams($request->getCookieList()->getValues())
            ->withQueryParams($request->getQueryList()->getValues())
            ->withParsedBody($request->getPostList()->getValues())
            ->withUploadedFiles(ServerRequest::normalizeFiles(
                $request->getFileList()->getValues()
            ));
    }
}
