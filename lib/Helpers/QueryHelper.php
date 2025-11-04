<?php
namespace Beeralex\Core\Helpers;

use Bitrix\Main\ORM\Query\QueryHelper as BaseQueryHelper;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * Расширенный QueryHelper с методом, возвращающим данные в виде массива.
 */
class QueryHelper extends BaseQueryHelper
{
    /**
     * Выполняет decompose() и преобразует результат в массив (рекурсивно).
     *
     * @param \Bitrix\Main\ORM\Query\Query $query
     * @return array
     */
    public static function decomposeToArray($query): array
    {
        $result = parent::decompose($query);
        if ($result instanceof Collection) {
            return static::convertCollectionToArray($result);
        }

        if ($result instanceof EntityObject) {
            return static::convertObjectToArray($result);
        }

        return (array)$result;
    }

    /**
     * Преобразует коллекцию ORM-объектов в массив.
     */
    protected static function convertCollectionToArray(Collection $collection): array
    {
        $data = [];
        foreach ($collection as $item) {
            $data[] = static::convertObjectToArray($item);
        }
        return $data;
    }

    /**
     * Преобразует ORM-объект в массив, включая связи.
     */
    protected static function convertObjectToArray(EntityObject $object): array
    {
        $runtimeValues = new \ReflectionProperty($object, '_runtimeValues');
        $values = $object->collectValues(recursive: true);
        $runtimeValues->setAccessible(true);
        foreach((array)$runtimeValues->getValue($object) as $key => $item) {
            if($item instanceof EntityObject) {
                $values[$key] = static::convertObjectToArray($item);
            }
        }
        return $values;
    }
}
