<?php

namespace Beeralex\Core\Repository;

use Bitrix\Main\ORM\Query\Query;

/**
 * @property-read class-string<\Bitrix\Main\ORM\Data\DataManager> $entityClass
 */
interface RepositoryContract
{
    public function query(): Query;
    public function all(array $filter, array $select, array $order): object|array;
    public function one(array $filter, array $select): object|null|array;
    public function getById(int $id, array $select): object|null|array;
    public function add(array|object $data): int;
    public function update(int $id, array|object $data): void;
    public function delete(int $id): void;
    public function save(array|object $data): int;
}
