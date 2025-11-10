<?php
namespace Beeralex\Core\UserType;

class WebFormLinkType
{
    public static function GetUserTypeDescription()
    {
        $class = static::class;
        return [
            'USER_TYPE_ID' => 'webform_link',
            'USER_TYPE' => 'WEBFORM_LINK',
            'CLASS_NAME' => $class,
            'DESCRIPTION' => 'Привязка к веб-форме',
            'PROPERTY_TYPE' => \Bitrix\Iblock\PropertyTable::TYPE_STRING,
            'GetPropertyFieldHtml' => [$class, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [$class, 'ConvertToDB'],
            'ConvertFromDB' => [$class, 'ConvertFromDB'],
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        if (!\Bitrix\Main\Loader::includeModule('form')) {
            return '<span style="color:red;">Модуль "form" не установлен</span>';
        }

        $rsForms = \CForm::GetList($by = 's_sort', $order = 'asc', ['ACTIVE' => 'Y']);
        $options = '';

        while ($form = $rsForms->Fetch()) {
            $selected = ($value['VALUE'] == $form['ID']) ? 'selected' : '';
            $options .= "<option value='{$form['ID']}' {$selected}>[{$form['ID']}] {$form['NAME']}</option>";
        }

        return '<select name="' . htmlspecialcharsbx($strHTMLControlName["VALUE"]) . '">'
            . '<option value="">(не выбрано)</option>'
            . $options
            . '</select>';
    }

    public static function ConvertToDB($arProperty, $value)
    {
        return [
            'VALUE' => $value['VALUE'],
            'DESCRIPTION' => $value['DESCRIPTION'],
        ];
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        return [
            'VALUE' => $value['VALUE'],
            'DESCRIPTION' => $value['DESCRIPTION'],
        ];
    }
}
