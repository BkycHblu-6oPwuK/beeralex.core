<?php

namespace Beeralex\Core\Repository;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

/**
 * @template T of DataManager
 */
abstract class AbstractRepository implements RepositoryContract
{
    /** @var class-string<T> */
    public readonly string $entityClass;

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(string $entityClass)
    {
        if (!is_subclass_of($entityClass, DataManager::class)) {
            throw new SystemException("Invalid entity class in repository: {$entityClass}");
        }
        $this->entityClass = $entityClass;
    }

    /** @return Query<T> */
    public function query(): Query
    {
        return $this->entityClass::query();
    }

    /**
     * @param array{
     *     select?: array,
     *     filter?: array|\Bitrix\Main\ORM\Query\Filter\Filter,
     *     group?: array,
     *     order?: array,
     *     limit?: int,
     *     offset?: int,
     *     count_total?: bool,
     *     runtime?: array<string, \Bitrix\Main\ORM\Fields\Field>,
     *     data_doubling?: bool,
     *     private_fields?: bool,
     *     cache?: array{ttl: int, cache_joins?: bool}
     * } $parameters
     *
     * @return \Bitrix\Main\ORM\Query\Result
     */
    public function getList(array $parameters = []): \Bitrix\Main\ORM\Query\Result
    {
        return $this->entityClass::getList($parameters);
    }
}
