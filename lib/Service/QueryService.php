<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Main\ORM\Query\QueryHelper;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Query\Query;

/**
 * Расширенный QueryHelper с методом, возвращающим данные в виде массива.
 */
class QueryService extends QueryHelper
{
    /**
     * Выполняет decompose() и преобразует результат в массив (рекурсивно).
     * некорректно работает с полями добавленными через runtime (например, связи). Пробуйте использовать fetchGroupedEntities
     * 
     */
    public function decomposeToArray(Query $query): array
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
            
        foreach((array)$runtimeValues->getValue($object) as $key => $item) {
            if($item instanceof EntityObject) {
                $values[$key] = $this->convertObjectToArray($item);
            } elseif(is_string($item) || is_numeric($item) || is_null($item)) {
                $values[$key] = $item;
            }
        }

        return $values;
    }

    /**
     * Выполняет запрос и группирует EntityObject-строки по ключу идентификатора.
     * Поля `_runtimeValues` (вложенные коллекции/связи) будут объединены без дублирования.
     *
     * @param Query $query
     * @param string $idKey ключ идентификатора в значениях (по умолчанию `ID`)
     * @return array
     */
    public function fetchGroupedEntities(Query $query, string $idKey = 'ID'): array
    {
        $result = $query->exec();
        $items = [];

        while ($entity = $result->fetchObject()) {
            if (!($entity instanceof EntityObject)) {
                // в случае, если fetchObject вернул что-то другое, пробуем привести через collectValues
                $values = is_object($entity) && method_exists($entity, 'collectValues')
                    ? $entity->collectValues(recursive: true)
                    : (array)$entity;
            } else {
                $values = $this->convertObjectToArray($entity);
            }

            $id = $values[$idKey] ?? null;
            if ($id === null) {
                // если нет ID — просто добавляем в конец списка
                $items[] = $values;
                continue;
            }

            if (!isset($items[$id])) {
                $items[$id] = $values;
                continue;
            }

            // Объединяем существующую запись с новой
            foreach ($values as $k => $v) {
                if ($k === $idKey) {
                    continue;
                }

                if (!array_key_exists($k, $items[$id]) || $items[$id][$k] === null) {
                    $items[$id][$k] = $v;
                    continue;
                }

                $existing = $items[$id][$k];

                if ($this->areValuesEqual($existing, $v)) {
                    continue;
                }

                // Проверяем, является ли существующее значение списком (индексный массив)
                $isExistingList = is_array($existing) && (empty($existing) || array_keys($existing) === range(0, count($existing) - 1));

                if ($isExistingList) {
                    // Проверяем наличие в списке
                    $found = false;
                    foreach ($existing as $exItem) {
                        if ($this->areValuesEqual($exItem, $v)) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $items[$id][$k][] = $v;
                    }
                } else {
                    // Превращаем одиночное значение в список
                    $items[$id][$k] = [$existing, $v];
                }
            }
        }

        return array_values($items);
    }

    /**
     * Сравнивает два значения. Массивы сравниваются по содержимому (без учета порядка ключей).
     */
    private function areValuesEqual($a, $b): bool
    {
        if (is_array($a) && is_array($b)) {
            if (count($a) !== count($b)) {
                return false;
            }
            // Работаем с копиями массивов, чтобы не ломать исходные данные сортировкой
            $aCopy = $a;
            $bCopy = $b;
            $this->recursiveKsort($aCopy);
            $this->recursiveKsort($bCopy);
            return serialize($aCopy) === serialize($bCopy);
        }

        if (is_object($a) && is_object($b)) {
            return serialize($a) === serialize($b);
        }

        return $a === $b;
    }

    private function recursiveKsort(array &$array): void
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveKsort($value);
            }
        }
        ksort($array);
    }
}
