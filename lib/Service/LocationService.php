<?php
declare(strict_types=1);

namespace Beeralex\Core\Service;

use Bitrix\Main\Loader;
use Beeralex\Core\Repository\LocationRepository;

/**
 * перенос в beeralex.catalog
 */
class LocationService
{
    public function __construct(
        protected readonly LocationRepository $locationRepository
    ) {
        Loader::includeModule("sale");
    }

    /**
     * Универсальный метод выборки через LocationRepository
     * Сигнатура сохранена для обратной совместимости.
     */
    public function get(
        array $filter = ['=NAME.LANGUAGE_ID' => LANGUAGE_ID],
        array $select = ['*', 'LOCATION_NAME_' => 'NAME'],
        bool $returnFirst = false,
        int $cacheTtl = 0
    ): array {
        return $this->locationRepository->get(
            filter: $filter,
            select: $select,
            returnFirst: $returnFirst,
            cacheTtl: $cacheTtl
        );
    }

    public function getLocationByCityCode(string $cityCode, int $cacheTtl = 0): array
    {
        return $this->locationRepository->get(
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

    public function getAllCities($select = ['ID', 'CITY_NAME' => 'NAME.NAME'], int $cacheTtl = 0): array
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

    /**
     * поиск через компонент sale.location.selector.search
     * (оставляем в сервисе, т.к. это не ORM-метод)
     */
    public function find(string $query, int $pageSize = 50, $page = 0): array
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