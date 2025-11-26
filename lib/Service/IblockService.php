<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Beeralex\Core\Model\SectionTableFactory;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\ORM\Query;
use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;

class IblockService
{
    public function __construct() 
    {
        Loader::includeModule('iblock');
    }

    /**
     * Получает id инфоблока по его коду
     */
    public function getIblockIdByCode(string $iblockCode): int
    {
        $row = IblockTable::query()
            ->setSelect(['ID'])
            ->where('CODE', $iblockCode)
            ->setCacheTtl(86400)
            ->exec()
            ->fetch();

        $id = $row['ID'] ?? null;
        if (!$id) {
            throw new \Exception("Iblock with code {$iblockCode} not found");
        }

        return (int)$id;
    }

    /**
     * Получить сущность для работы с элементами инфоблока по его символьному коду, так же должен быть задан сивольный код api
     * @throws \Exception
     * @return \Bitrix\Iblock\ORM\CommonElementTable|string
     */
    public function getElementApiTableByCode(string $iblockCode)
    {
        return $this->getElementApiTable($this->getIblockIdByCode($iblockCode));
    }

    /**
     * Получить сущность для работы с элементами инфоблока по его id, так же должен быть задан сивольный код api
     * @throws \Exception
     * @return \Bitrix\Iblock\ORM\CommonElementTable|string
     */
    public function getElementApiTable(int $iblockId)
    {
        $entity = Iblock::wakeUp($iblockId)->getEntityDataClass();
        if (!$entity) {
            throw new \Exception("entity with not found in iblock {$iblockId}");
        }

        return $entity;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getIblockPropIdByCode(string $code, int $iblockId): int
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

    public function getEnumValues(int $propId, array $xmlIds = []): array
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

    public function addSectionModelToQuery(Iblock|int $iblock, Query $query): Query
    {
        $sectionModel = service(SectionTableFactory::class)->compileEntityByIblock($iblock);
        $query->registerRuntimeField('IBLOCK_MODEL_SECTION', [
            'data_type' => $sectionModel,
            'reference' => ["=this.IBLOCK_SECTION_ID" => 'ref.ID'],
            'join_type' => 'LEFT',
        ]);

        return $query;
    }

    public function addPropertyModelToQuery(Query $query): Query
    {
        $query->registerRuntimeField('IBLOCK_MODEL_PROPERTY', [
            'data_type' => PropertyTable::class,
            'reference' => ["=this.ID" => 'ref.IBLOCK_ID'],
            'join_type' => 'LEFT',
        ]);

        return $query;
    }

    /**
     * Получает сущность справочника по ID свойства инфоблока
     */
    public function getTableEntityByPropertyId(int $propertyId)
    {
        $data = PropertyTable::query()
            ->setSelect(['USER_TYPE_SETTINGS_LIST'])
            ->where('ID', $propertyId)
            ->setCacheTtl(86400)
            ->exec()
            ->fetch();
        
        if (!$data['USER_TYPE_SETTINGS_LIST']['TABLE_NAME']) {
            throw new \Exception("Property with ID {$propertyId} is not a directory");
        }

        return service(HlblockService::class)->getHlBlockByTableName($data['USER_TYPE_SETTINGS_LIST']['TABLE_NAME']);
    }
}
