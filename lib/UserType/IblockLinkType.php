<?php

namespace Beeralex\Core\UserType;

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

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
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

    public static function ConvertToDB($arProperty, $value)
    {
        return $value;
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        return $value;
    }
}
