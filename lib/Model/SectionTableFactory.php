<?php
declare(strict_types=1);
namespace Beeralex\Core\Model;

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Loader;
use Bitrix\Main\UserFieldTable;

/**
 * Создает динамические ORM сущности для работы с разделами инфоблоков
 * В запросе можно получить так же перечисления пользовательских полей, если они есть
 */
class SectionTableFactory
{
    protected static array $entityInstance = [];

    public function __construct()
    {
        Loader::requireModule('iblock');
    }

    /**
     * @param int|Iblock $iblock Iblock object, or ID
     *
     * @return SectionTable|string|null
     */
    public function compileEntityByIblock(Iblock|int $iblock)
    {
        $iblockId = $this->resolveIblockId($iblock);

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

    protected function resolveIblockId(Iblock|int $iblock): int
    {
        if(is_numeric($iblock)) {
            return $iblock;
        }

        return $iblock->getId();
    }
}
