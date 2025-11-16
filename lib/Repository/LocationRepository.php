<?php
declare(strict_types=1);

namespace Beeralex\Core\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\Name\LocationTable as LocationNameTable;

/**
 * Репозиторий для работы с таблицей локаций Bitrix (sale: LocationTable)
 *
 * Оборачивает типичные сценарии выборок, ранее реализованные в LocationService.
 */
class LocationRepository extends Repository
{
    public function __construct(bool $useDecompose = false)
    {
        Loader::includeModule('sale');
        parent::__construct(LocationTable::class, $useDecompose);
    }

    /**
     * Универсальная выборка из LocationTable с INNER JOIN к NAME (LocationNameTable)
     *
     * @param array $filter
     * @param array $select
     * @param bool  $returnFirst
     * @param int   $cacheTtl
     *
     * @return array
     */
    public function get(
        array $filter = ['=NAME.LANGUAGE_ID' => LANGUAGE_ID],
        array $select = ['*', 'LOCATION_NAME_' => 'NAME'],
        bool $returnFirst = false,
        int $cacheTtl = 0
    ): array {
        $params = [
            'filter'  => $filter,
            'select'  => $select,
            'order'   => ['SORT' => 'ASC'],
            'runtime' => [
                new Reference(
                    'NAME',
                    LocationNameTable::class,
                    ['=this.ID' => 'ref.LOCATION_ID'],
                    ['join_type' => 'inner']
                ),
            ],
        ];

        if ($cacheTtl > 0) {
            $params['cache'] = [
                'ttl'         => $cacheTtl,
                'cache_joins' => true,
            ];
        }

        if ($returnFirst) {
            $params['limit'] = 1;
        }

        $res = $this->getList($params);

        if ($returnFirst) {
            return $res->fetch() ?: [];
        }

        return $res->fetchAll();
    }

    public function getLocationByCityCode(string $cityCode, int $cacheTtl = 0): array
    {
        return $this->get(
            filter: [
                '=CODE'             => $cityCode,
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
            ],
            returnFirst: true,
            cacheTtl: $cacheTtl
        );
    }

    public function getAllLocations(int $cacheTtl = 0): array
    {
        return $this->get(cacheTtl: $cacheTtl);
    }

    public function getAllCities(array $select = ['ID', 'CITY_NAME' => 'NAME.NAME'], int $cacheTtl = 0): array
    {
        return $this->get(
            filter: [
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                '=TYPE.CODE'        => 'CITY',
            ],
            select: $select,
            cacheTtl: $cacheTtl
        );
    }

    public function getLocationByCityName(string $cityName, int $cacheTtl = 0): array
    {
        return $this->get(
            filter: [
                '=NAME.NAME'        => $cityName,
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
            ],
            returnFirst: true,
            cacheTtl: $cacheTtl
        );
    }

    /**
     * Возвращает ближайший город (CITY) для кода локации.
     * Если сама локация — город, вернёт её.
     */
    public function getNearestCityByLocationCode(string $locationCode, int $cacheTtl = 0): array
    {
        $location = $this->get(
            filter: [
                '=CODE'             => $locationCode,
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
            ],
            returnFirst: true,
            cacheTtl: $cacheTtl
        );

        if (!$location) {
            return [];
        }

        if (!empty($location['CITY_ID']) && (int)$location['CITY_ID'] === (int)$location['ID']) {
            return $location;
        }

        if (!empty($location['CITY_ID'])) {
            return $this->get(
                filter: [
                    '=ID'               => $location['CITY_ID'],
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ],
                returnFirst: true,
                cacheTtl: $cacheTtl
            );
        }

        $parentId = $location['PARENT_ID'] ?? null;
        while ($parentId) {
            $parent = $this->get(
                filter: [
                    '=ID'               => $parentId,
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ],
                returnFirst: true,
                cacheTtl: $cacheTtl
            );

            if (!$parent) {
                break;
            }

            if (!empty($parent['CITY_ID'])) {
                return ((int)$parent['CITY_ID'] === (int)$parent['ID'])
                    ? $parent
                    : $this->get(
                        filter: [
                            '=ID'               => $parent['CITY_ID'],
                            '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                        ],
                        returnFirst: true,
                        cacheTtl: $cacheTtl
                    );
            }

            $parentId = $parent['PARENT_ID'] ?? null;
        }

        return [];
    }
}