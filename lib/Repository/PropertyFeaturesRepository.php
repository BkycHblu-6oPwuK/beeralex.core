<?php

declare(strict_types=1);

namespace Beeralex\Core\Repository;

use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Main\Loader;

/**
 * Репозиторий для работы с таблицей параметров свойств инфоблоков.
 */
class PropertyFeaturesRepository extends Repository
{
    public function __construct(bool $useDecompose = false)
    {
        Loader::includeModule('sale');
        parent::__construct(PropertyFeatureTable::class, $useDecompose);
    }

    public function getByFeatureId(
        string $featureId,
        array $select = ['*'],
        array $order = [],
        int $cacheTtl = 0,
        bool $cacheJoins = false
    ): ?array {
        return $this->all(['=FEATURE_ID' => $featureId], $select, $order, $cacheTtl, $cacheJoins);
    }

    public function getByOffersTree(
        array $select = ['*'],
        array $order = [],
        int $cacheTtl = 0,
        bool $cacheJoins = false
    ) {
        return $this->getByFeatureId('OFFER_TREE', $select, $order, $cacheTtl, $cacheJoins);
    }
}
