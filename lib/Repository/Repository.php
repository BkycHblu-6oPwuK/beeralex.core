<?php

namespace Beeralex\Core\Repository;

use Bitrix\Main\SystemException;

class Repository extends AbstractRepository
{
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

    public function add(array|object $data): int
    {
        $result = $this->entityClass::add((array)$data);
        if (!$result->isSuccess()) {
            throw new SystemException(implode(', ', $result->getErrorMessages()));
        }
        return $result->getId();
    }

    public function update(int $id, array|object $data): void
    {
        $result = $this->entityClass::update($id, (array)$data);
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

    public function save(array|object $data): int
    {
        if (!empty($data['ID'])) {
            $this->update((int)$data['ID'], (array)$data);
            return (int)$data['ID'];
        }

        return $this->add((array)$data);
    }
}
