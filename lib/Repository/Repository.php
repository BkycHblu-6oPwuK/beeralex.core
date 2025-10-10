<?php

namespace Beeralex\Core\Repository;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

/**
 * @template T of DataManager
 */
class Repository implements RepositoryContract
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

    public function all(array $filter = [], array $select = ['*'], array $order = []): array
    {
        return $this->query()
            ->setSelect($select)
            ->setFilter($filter)
            ->setOrder($order)
            ->exec()
            ->fetchAll();
    }

    public function one(array $filter = [], array $select = ['*']): ?array
    {
        $row = $this->query()
            ->setSelect($select)
            ->setFilter($filter)
            ->setLimit(1)
            ->exec()
            ->fetch();

        return $row ?: null;
    }

    public function getById(int $id, array $select = ['*']): ?array
    {
        return $this->one(['ID' => $id], $select);
    }

    public function add(array $data): int
    {
        $result = $this->entityClass::add($data);
        if (!$result->isSuccess()) {
            throw new SystemException(implode(', ', $result->getErrorMessages()));
        }
        return $result->getId();
    }

    public function update(int $id, array $data): void
    {
        $result = $this->entityClass::update($id, $data);
        if (!$result->isSuccess()) {
            throw new SystemException(implode(', ', $result->getErrorMessages()));
        }
    }

    public function delete(int $id): void
    {
        $result = $this->entityClass::delete($id);
        if (!$result->isSuccess()) {
            throw new SystemException(implode(', ', $result->getErrorMessages()));
        }
    }

    public function save(array $data): int
    {
        if (!empty($data['ID'])) {
            $this->update((int)$data['ID'], $data);
            return (int)$data['ID'];
        }

        return $this->add($data);
    }
}
