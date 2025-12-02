<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Main\Context;
use Bitrix\Main\Web\HttpHeaders;

class WebService
{
    /**
     * Выводит JSON ответ и завершает выполнение скрипта
     */
    public function jsonAnswer(array $result)
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json');
        echo \Bitrix\Main\Web\Json::encode($result);
        Context::getCurrent()->getResponse()->setStatus(200);
        \CMain::FinalActions();
    }

    /**
     * Собирает HTTP заголовки из объекта HttpHeaders в ассоциативный массив
     */
    public function collectHttpHeaders(HttpHeaders $headers): array
    {
        $list = [];
        
        foreach ($headers->toArray() as $header) {
            $list[$header['name']] = $header['values'];
        }

        return $list;
    }

    /**
     * Извлекает версию HTTP протокола из SERVER_PROTOCOL
     */
    public function parseHttpProtocolVersion(?string $serverProtocol): string
    {
        return $serverProtocol !== null
            ? str_replace('HTTP/', '', $serverProtocol)
            : '1.0';
    }
}
