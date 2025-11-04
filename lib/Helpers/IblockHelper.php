<?php

namespace Beeralex\Core\Helpers;

use Beeralex\Core\Model\SectionModel;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\ORM\Query;
use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;

class IblockHelper
{
    static $iblockCodeIdMap = [];
    static $entityMap = [];

    private function __construct() {}

    /**
     * Получает id инфоблока по его коду
     *
     * @param string $iblockCode
     *
     * @return int
     */
    public static function getIblockIdByCode(string $iblockCode): int
    {
        if (!isset(static::$iblockCodeIdMap[$iblockCode])) {
            Loader::includeModule('iblock');

            $id = IblockTable::getList([
                'select' => ['ID'],
                'filter' => ['CODE' => $iblockCode],
                'cache' => ['ttl' => 86400]
            ])->fetch()['ID'];

            if (!$id) {
                throw new \Exception("Iblock with code {$iblockCode} not found");
            }

            static::$iblockCodeIdMap[$iblockCode] = $id;
        }

        return static::$iblockCodeIdMap[$iblockCode];
    }

    /**
     * Получить сущность для работы с элементами инфоблока по его символьному коду, так же должен быть задан сивольный код api
     * @param string $iblockCode
     * @throws \Exception
     * @return \Bitrix\Iblock\ORM\CommonElementTable|string
     */
    public static function getElementApiTableByCode(string $iblockCode)
    {
        return self::getElementApiTable(self::getIblockIdByCode($iblockCode));
    }

    /**
     * Получить сущность для работы с элементами инфоблока по его id, так же должен быть задан сивольный код api
     * @param int $iblockId
     * @throws \Exception
     * @return \Bitrix\Iblock\ORM\CommonElementTable|string
     */
    public static function getElementApiTable(int $iblockId)
    {
        if (!isset(static::$entityMap[$iblockId])) {
            Loader::includeModule('iblock');
            $entity = Iblock::wakeUp($iblockId)->getEntityDataClass();
            if (!$entity) {
                throw new \Exception("entity with not found in iblock {$iblockId}");
            }
            static::$entityMap[$iblockId] = $entity;
        }

        return static::$entityMap[$iblockId];
    }

    /**
     * @param $code
     * @param $iblockId
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public static function getIblockPropIdByCode(string $code, int $iblockId): int
    {
        $propId = PropertyTable::query()
            ->setSelect(['ID'])
            ->where('IBLOCK_ID', $iblockId)
            ->where('CODE', $code)
            ->setCacheTtl(86400)
            ->exec()
            ->fetch()['ID'];
        return $propId ? (int)$propId : 0;
    }

    /**
     * @param int   $propId
     * @param array $xmlIds
     *
     * @return array [ xmlId => [id => valueId] ]
     */
    public static function getEnumValues(int $propId, array $xmlIds = []): array
    {
        $dbRes = \CIBlockPropertyEnum::GetList([], [
            'PROPERTY_ID' => $propId,
            'XML_ID'      => $xmlIds
        ]);

        $values = [];
        while ($value = $dbRes->Fetch()) {
            $values[$value['XML_ID']] = [
                'id' => $value['ID']
            ];
        }

        return $values;
    }

    /**
     * Добавить склад/количество (STORE_PRODUCT) в запрос
     */
    public static function addSectionModelToQuery(Iblock|int $iblock, Query $query): Query
    {
        $sectionModel = SectionModel::compileEntityByIblock($iblock);
        $query->registerRuntimeField('IBLOCK_MODEL_SECTION', [
            'data_type' => $sectionModel,
            'reference' => ["=this.IBLOCK_SECTION_ID" => 'ref.ID'],
            'join_type' => 'LEFT',
        ]);

        return $query;
    }
}
