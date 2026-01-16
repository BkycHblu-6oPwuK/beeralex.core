<?php
namespace Beeralex\Core\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;

class FormAnswerTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_form_answer';
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
            new BooleanField('ACTIVE', ['values' => ['N', 'Y']]),
            new IntegerField('FORM_ID'),
            new IntegerField('FIELD_ID'),
            new IntegerField('C_SORT'),
            new TextField('MESSAGE'),
            new TextField('VALUE'),
            new TextField('FIELD_TYPE'),
            new IntegerField('FIELD_WIDTH'),
            new IntegerField('FIELD_HEIGHT'),
            new TextField('FIELD_PARAM'),
            new Reference(
                'FORM',
                FormTable::class,
                ['=this.FORM_ID' => 'ref.ID']
            ),
            new Reference(
                'FIELD',
                FormFieldTable::class,
                ['=this.FIELD_ID' => 'ref.ID']
            ),
        ];
    }
}
