<?php

namespace Beeralex\Core\Model;

use Beeralex\Core\Helpers\IblockHelper;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\Loader;
use Bitrix\Main\UserFieldTable;

/**
 * Модель раздела инфоблока с поддержкой пользовательских полей, в том числе ENUM (перечисление)
 */
class SectionModel
{
    private static $entityInstance = [];

    /**
     * @param int|string|Iblock $iblock Iblock object, or CODE, or ID
     *
     * @return SectionTable|string|null
     */
    public static function compileEntityByIblock(Iblock|int $iblock)
    {
        Loader::requireModule('iblock');
        $iblockId = static::resolveIblockId($iblock);

        if ($iblockId <= 0) {
            return null;
        }

        if (!isset(static::$entityInstance[$iblockId])) {
            $className = 'Section' . $iblockId . 'Table';
            $entityName = "\\Beeralex\\Core\\Model\\" . $className;
            $referenceName = '\Bitrix\Iblock\Section' . $iblockId;

            $ufId = "IBLOCK_{$iblockId}_SECTION";
            $ufEnums = UserFieldTable::getList([
                'filter' => ['ENTITY_ID' => $ufId, 'USER_TYPE_ID' => 'enumeration'],
                'select' => ['FIELD_NAME'],
            ])->fetchAll();

            $enumFieldsCode = '';
            foreach ($ufEnums as $uf) {
                $fieldName = $uf['FIELD_NAME'];
                $enumFieldsCode .= '
                    $fields["' . $fieldName . '_ENUM"] = [
                        "data_type" => "\\Beeralex\\Core\\Model\\UserFieldEnumTable",
                        "reference" => ["=this.' . $fieldName . '" => "ref.ID"],
                    ];
                ';
            }

            $entity = '
            namespace Beeralex\Core\Model;
            class ' . $className . ' extends \Bitrix\Iblock\SectionTable
            {
                public static function getUfId()
                {
                    return "IBLOCK_' . $iblockId . '_SECTION";
                }
                
                public static function getMap(): array
                {
                    $fields = parent::getMap();
                    $fields["PARENT_SECTION"] = [
                        "data_type" => "' . $referenceName . '",
                        "reference" => ["=this.IBLOCK_SECTION_ID" => "ref.ID"],
                    ];
                    ' . $enumFieldsCode . '
                    return $fields;
                }
                
                public static function setDefaultScope($query)
                {
                    return $query->where("IBLOCK_ID", ' . $iblockId . ');
                }
            }';
            eval($entity);
            static::$entityInstance[$iblockId] = $entityName;
        }

        return static::$entityInstance[$iblockId];
    }

    protected static function resolveIblockId(Iblock|int $iblock): int
    {
        if(is_numeric($iblock)) {
            return $iblock;
        }

        return $iblock->getId();
    }
}
