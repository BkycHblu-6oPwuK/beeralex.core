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

    public function query(): Query
    {
        return $this->entityClass::query();
    }

    public function getList(array $parameters = []): \Bitrix\Main\ORM\Query\Result
    {
        return $this->entityClass::getList($parameters);
    }
}
