<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Main\ORM\Query\QueryHelper;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * Расширенный QueryHelper с методом, возвращающим данные в виде массива.
 */
class QueryService extends QueryHelper
{
    /**
     * Выполняет decompose() и преобразует результат в массив (рекурсивно).
     *
     * @param \Bitrix\Main\ORM\Query\Query $query
     * @return array
     */
    public function decomposeToArray($query): array
    {
        $result = parent::decompose($query);
        if ($result instanceof Collection) {
            return $this->convertCollectionToArray($result);
        }

        if ($result instanceof EntityObject) {
            return $this->convertObjectToArray($result);
        }

        return (array)$result;
    }

    /**
     * Преобразует коллекцию ORM-объектов в массив.
     */
    protected function convertCollectionToArray(Collection $collection): array
    {
        $data = [];
        foreach ($collection as $item) {
            $data[] = $this->convertObjectToArray($item);
        }
        return $data;
    }

    /**
     * Преобразует ORM-объект в массив, включая связи.
     */
    protected function convertObjectToArray(EntityObject $object): array
    {
        $runtimeValues = new \ReflectionProperty($object, '_runtimeValues');
        $values = $object->collectValues(recursive: true);
        $runtimeValues->setAccessible(true);
        foreach((array)$runtimeValues->getValue($object) as $key => $item) {
            if($item instanceof EntityObject) {
                $values[$key] = $this->convertObjectToArray($item);
            }
        }
        return $values;
    }
}
