<?php
declare(strict_types=1);
namespace Beeralex\Core\Http\Adapter;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Server;
use Psr\Http\Message\ServerRequestInterface;

class PsrToBitrixRequest
{
    public function convert(ServerRequestInterface $psrRequest): HttpRequest
    {
        $serverParams = $psrRequest->getServerParams();
        $serverParams['REQUEST_METHOD'] = $psrRequest->getMethod();
        $uri = $psrRequest->getUri();
        $serverParams['REQUEST_URI'] = (string)$uri->getPath();
        $serverParams['QUERY_STRING'] = $uri->getQuery();
        $serverParams['HTTP_HOST'] = $uri->getHost();
        $serverParams['SERVER_PROTOCOL'] = 'HTTP/' . $psrRequest->getProtocolVersion();

        foreach ($psrRequest->getHeaders() as $name => $values) {
            $key = 'HTTP_' . strtoupper(str_replace('-', '_', (string)$name));
            $serverParams[$key] = implode(', ', $values);

            if ($key === 'HTTP_CONTENT_TYPE') {
                $serverParams['CONTENT_TYPE'] = implode(', ', $values);
            }
            if ($key === 'HTTP_CONTENT_LENGTH') {
                $serverParams['CONTENT_LENGTH'] = implode(', ', $values);
            }
        }

        $server = new Server($serverParams);
        $query = $psrRequest->getQueryParams();
        $post = is_array($psrRequest->getParsedBody()) ? $psrRequest->getParsedBody() : [];
        $cookies = $psrRequest->getCookieParams();
        $files = $this->normalizeUploadedFiles($psrRequest->getUploadedFiles());

        $bitrixRequest = new HttpRequest(
            $server,
            $query,
            $post,
            $files,
            $cookies
        );

        return $bitrixRequest;
    }

    /**
     * Преобразует массив PSR UploadedFileInterface в Bitrix-совместимый формат $_FILES
     */
    protected function normalizeUploadedFiles(array $uploadedFiles): array
    {
        $result = [];

        foreach ($uploadedFiles as $key => $file) {
            if (is_array($file)) {
                $result[$key] = $this->normalizeUploadedFiles($file);
                continue;
            }

            /** @var \Psr\Http\Message\UploadedFileInterface $file */
            $result[$key] = [
                'name'     => $file->getClientFilename(),
                'type'     => $file->getClientMediaType(),
                'tmp_name' => method_exists($file->getStream(), 'getMetadata')
                    ? ($file->getStream()->getMetadata('uri') ?? '')
                    : '',
                'error'    => $file->getError(),
                'size'     => $file->getSize(),
            ];
        }

        return $result;
    }
}
