<?php
declare(strict_types=1);
namespace Beeralex\Core\Http\Controllers;

use Beeralex\Core\Http\Request\AbstractRequestDto;
use Beeralex\Core\Http\Resources\Resource;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Web\Json;

/**
 * Базовый API контроллер с поддержкой DTO и Resource в аргументах действий
 * @todo bitrix там что то делает с атрибутами, и вроде можно будет автовалидировать без обработки в processBeforeAction
 */
abstract class ApiController extends Controller
{
    public function getAutoWiredParameters(): array
    {
        return array_merge(parent::getAutoWiredParameters(), [
            new Parameter(
                AbstractRequestDto::class,
                function (string $className) {
                    return $className::fromArray($this->getRequestData());
                }
            ),
            new Parameter(
                Resource::class,
                function (string $className) {
                    return $className::make($this->getRequestData());
                }
            ),
        ]);
    }

    protected function processBeforeAction(Action $action): bool
    {
        try {
            $arguments = $action->getArguments();
        } catch (\Throwable $exception) {
            if ($exception->getPrevious() instanceof \JsonException) {
                $this->addError(new Error('Некорректный JSON'));
                return false;
            }

            throw $exception;
        }

        foreach ($arguments as $argument) {
            if (!$argument instanceof AbstractRequestDto) {
                continue;
            }

            if (!$argument->isValid()) {
                foreach ($argument->getErrors() as $error) {
                    $this->addError($error);
                }

                return false;
            }
        }

        return parent::processBeforeAction($action);
    }

    private function getRequestData(): array
    {
        $request = $this->getRequest();
        $data = $request->isPost()
            ? $request->getPostList()->toArray()
            : $request->getQueryList()->toArray();

        if (empty($data) && $request->isJson()) {
            $decoded = Json::decode($request->getInput());

            return is_array($decoded) ? $decoded : [];
        }

        return $data;
    }
}
