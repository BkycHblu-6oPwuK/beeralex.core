<?php
namespace Beeralex\Core\Repository;

interface SortingRepositoryContract extends RepositoryContract 
{
    public function getDefaultSorting(array $select = [], int $cacheTtl = 0, bool $cacheJoins = false): ?array;
}