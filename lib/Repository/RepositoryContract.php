<?php

namespace Beeralex\Core\Repository;

use Bitrix\Main\ORM\Query\Query;

/**
 * @property-read class-string<\Bitrix\Main\ORM\Data\DataManager> $entityClass
 */
interface RepositoryContract
{
    /** @return Query<T> */
    public function query(): Query;
    public function all(array $filter = [], array $select = ['*'], array $order = [], int $cacheTtl = 0, bool $cacheJoins = false): object|array;
    public function one(array $filter = [], array $select = ['*'], int $cacheTtl = 0, bool $cacheJoins = false): object|null|array;
    public function getById(int $id, array $select = ['*']): object|null|array;
    public function add(array|object $data): int;
    public function update(int $id, array|object $data): void;
    public function delete(int $id): void;
    public function save(array|object $data): int;
    public function count(array $filter = []): int;
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
    public function getList(array $parameters = []): \Bitrix\Main\ORM\Query\Result;
}
