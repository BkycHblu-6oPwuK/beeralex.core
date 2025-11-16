<?php
declare(strict_types=1);
namespace Beeralex\Core\Http\Adapter;

use Beeralex\Core\Service\WebService;
use Bitrix\Main\HttpRequest;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class BitrixToPsrRequest
{
    public function __construct(
        protected WebService $webService
    ){}

    public function convert(HttpRequest $request): ServerRequestInterface
    {
        $serverRequest = new ServerRequest(
            $request->getRequestMethod(),
            $request->getRequestUri(),
            $this->webService->collectHttpHeaders($request->getHeaders()),
            HttpRequest::getInput(),
            $this->webService->parseHttpProtocolVersion($request->getServer()->get('SERVER_PROTOCOL')),
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
