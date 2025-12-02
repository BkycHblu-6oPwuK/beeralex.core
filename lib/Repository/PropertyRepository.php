<?php

declare(strict_types=1);

namespace Beeralex\Core\Repository;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;

/**
 * Репозиторий для работы с таблицей свойств инфоблоков.
 */
class PropertyRepository extends Repository
{
    public function __construct(bool $useDecompose = false)
    {
        Loader::includeModule('iblock');
        parent::__construct(PropertyTable::class, $useDecompose);
    }

    public function getByIds(array $ids, array $select = ['*'], array $order = [], int $cacheTtl = 0, bool $cacheJoins = false): array
    {
        return $this->all(['ID' => $ids], $select, $order, $cacheTtl, $cacheJoins);
    }
}
