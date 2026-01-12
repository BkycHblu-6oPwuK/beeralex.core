<?php
namespace Beeralex\Core\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;

class FormTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_form';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new DatetimeField('TIMESTAMP_X', [
                'default_value' => function() {
                    return new DateTime();
                }
            ]),
            new StringField('NAME'),
            new StringField('SID'),
            new StringField('BUTTON'),
            new StringField('DESCRIPTION_TYPE'),
            new StringField('FIRST_SITE_ID'),
            new TextField('DESCRIPTION'),
            new IntegerField('C_SORT'),
            new IntegerField('IMAGE_ID'),
            new BooleanField('USE_CAPTCHA', ['values' => ['N', 'Y']]),
            new TextField('FORM_TEMPLATE'),
            new BooleanField('USE_DEFAULT_TEMPLATE', ['values' => ['N', 'Y']]),
            new TextField('SHOW_TEMPLATE'),
            new TextField('MAIL_EVENT_TYPE'),
            new TextField('SHOW_RESULT_TEMPLATE'),
            new TextField('PRINT_RESULT_TEMPLATE'),
            new TextField('EDIT_RESULT_TEMPLATE'),
            new TextField('FILTER_RESULT_TEMPLATE'),
            new TextField('TABLE_RESULT_TEMPLATE'),
            new BooleanField('USE_RESTRICTIONS', ['values' => ['N', 'Y']]),
            new IntegerField('RESTRICT_USER'),
            new IntegerField('RESTRICT_TIME'),
            new TextField('RESTRICT_STATUS'),
            new TextField('STAT_EVENT1'),
            new TextField('STAT_EVENT2'),
            new TextField('STAT_EVENT3'),
        ];
    }
}
