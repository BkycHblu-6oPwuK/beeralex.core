<?php
namespace Beeralex\Core\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;

class FormFieldTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_form_field';
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
            new IntegerField('FORM_ID'),
            new BooleanField('ACTIVE', ['values' => ['N', 'Y']]),
            new TextField('TITLE'),
            new StringField('TITLE_TYPE'),
            new StringField('SID'),
            new IntegerField('C_SORT'),
            new BooleanField('REQUIRED', ['values' => ['N', 'Y']]),
            new BooleanField('IN_FILTER', ['values' => ['N', 'Y']]),
            new BooleanField('IN_RESULTS_TABLE', ['values' => ['N', 'Y']]),
            new BooleanField('IN_EXCEL_TABLE', ['values' => ['N', 'Y']]),
            new StringField('FIELD_TYPE'),
            new IntegerField('IMAGE_ID'),
            new TextField('COMMENTS'),
            new TextField('FILTER_TITLE'),
            new TextField('RESULTS_TABLE_TITLE'),
            new Reference(
                'FORM',
                FormTable::class,
                ['=this.FORM_ID' => 'ref.ID']
            ),
        ];
    }
}
