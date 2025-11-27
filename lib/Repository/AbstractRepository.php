<?php
declare(strict_types=1);
namespace Beeralex\Core\Repository;

use Beeralex\Core\Service\QueryService;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

/**
 * @template T of DataManager
 */
abstract class AbstractRepository implements RepositoryContract
{
    /** @var class-string<T> */
    protected readonly string $entityClass;
    protected readonly QueryService $queryService;
    protected bool $useDecompose;

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(string $entityClass, bool $useDecompose = false)
    {
        if (!is_subclass_of($entityClass, DataManager::class)) {
            throw new SystemException("Invalid entity class in repository: {$entityClass}");
        }
        $this->entityClass = $entityClass;
        $this->useDecompose = $useDecompose;
        $this->queryService = service(QueryService::class);
    }

    public function useDecompose(bool $useDecompose = true): static
    {
        $this->useDecompose = $useDecompose;
        return $this;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
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
