<?php

namespace Beeralex\Core\Helpers;

use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\Name\LocationTable as LocationNameTable;

Loader::includeModule("sale");

class LocationHelper
{
    private function __construct() {}
    
    /**
     * Универсальный метод выборки из LocationTable c NAME
     */
    public static function get(
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

        $res = LocationTable::getList($params);

        if ($returnFirst) {
            return $res->fetch() ?: [];
        }

        return $res->fetchAll();
    }

    public static function getLocationByCityCode(string $cityCode, int $cacheTtl = 0): array
    {
        return static::get(
            filter: [
                '=CODE'             => $cityCode,
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
            ],
            returnFirst: true,
            cacheTtl: $cacheTtl
        );
    }

    public static function getAllLocations(int $cacheTtl = 0): array
    {
        return static::get(cacheTtl: $cacheTtl);
    }

    public static function getAllCities($select = ['ID', 'CITY_NAME' => 'NAME.NAME'], int $cacheTtl = 0): array
    {
        return static::get(
            filter: [
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                '=TYPE.CODE'        => 'CITY',
            ],
            select: $select,
            cacheTtl: $cacheTtl
        );
    }

    public static function getLocationByCityName(string $cityName, int $cacheTtl = 0): array
    {
        return static::get(
            filter: [
                '=NAME.NAME'        => $cityName,
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
            ],
            returnFirst: true,
            cacheTtl: $cacheTtl
        );
    }

    public static function getNearestCityByLocationCode(string $locationCode, int $cacheTtl = 0): array
    {
        $location = static::get(
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
        if (!empty($location['CITY_ID']) && $location['CITY_ID'] == $location['ID']) {
            return $location;
        }

        if (!empty($location['CITY_ID'])) {
            return static::get(
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
            $parent = static::get(
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
                return $parent['CITY_ID'] == $parent['ID']
                    ? $parent
                    : static::get(
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

    /**
     * поиск через компонент sale.location.selector.search
     */
    public static function find(string $query, int $pageSize = 50, $page = 0): array
    {
        \CBitrixComponent::includeComponentClass('bitrix:sale.location.selector.search');
        $parameters = [
            'select'      => ['CODE', 'TYPE_ID', 'VALUE' => 'ID', 'DISPLAY' => 'NAME.NAME'],
            'additionals' => ['PATH'],
            'filter'      => [
                '=PHRASE'           => $query,
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                '=SITE_ID'          => SITE_ID,
            ],
            'PAGE_SIZE' => $pageSize,
            'PAGE'      => $page,
        ];

        $data = \CBitrixLocationSelectorSearchComponent::processSearchRequestV2($parameters);
        $pathItems = $data['ETC']['PATH_ITEMS'];

        foreach ($data['ITEMS'] as &$item) {
            foreach ($item['PATH'] as $keyPath => &$path) {
                if ($pathItems[$path]) {
                    $path = $pathItems[$path];
                } else {
                    unset($item['PATH'][$keyPath]);
                }
            }
        }

        return $data['ITEMS'];
    }
}
