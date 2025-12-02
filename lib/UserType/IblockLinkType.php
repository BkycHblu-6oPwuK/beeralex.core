<?php
declare(strict_types=1);
namespace Beeralex\Core\UserType;

/**
 * Пользовательский тип свойства для привязки к инфоблоку
 * Используется для создания выпадающего списка с инфоблоками
 */
class IblockLinkType
{
    public static function GetUserTypeDescription()
    {
        $class = static::class;
        return [
            'USER_TYPE_ID' => 'iblock_link',
            'USER_TYPE' => 'IBLOCK_LINK',
            'CLASS_NAME' => $class,
            'DESCRIPTION' => 'Привязка к инфоблоку',
            'PROPERTY_TYPE' => \Bitrix\Iblock\PropertyTable::TYPE_STRING,
            'GetPropertyFieldHtml' => [$class, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [$class, 'ConvertToDB'],
            'ConvertFromDB' => [$class, 'ConvertFromDB'],
        ];
    }

    public static function GetPropertyFieldHtml(array $arProperty, array $value, array $strHTMLControlName)
    {
        $res = \CIBlock::GetList([], ['ACTIVE' => 'Y']);
        $options = '';
        while ($iblock = $res->Fetch()) {
            $selected = $value['VALUE'] == $iblock['ID'] ? 'selected' : '';
            $options .= "<option value='{$iblock['ID']}' $selected>[{$iblock['ID']}] {$iblock['NAME']}</option>";
        }

        return '<select name="' . $strHTMLControlName["VALUE"] . '">'
            . '<option value="">(не выбрано)</option>'
            . $options
            . '</select>';
    }

    public static function ConvertToDB(array $arProperty, array $value)
    {
        return [
            'VALUE' => $value['VALUE'],
            'DESCRIPTION' => $value['DESCRIPTION'],
        ];
    }

    public static function ConvertFromDB(array $arProperty, array $value)
    {
        return [
            'VALUE' => $value['VALUE'],
            'DESCRIPTION' => $value['DESCRIPTION'],
        ];
    }
}
