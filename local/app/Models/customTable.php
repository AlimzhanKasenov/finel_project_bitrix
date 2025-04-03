<?php
namespace Bitrix\Table;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

/**
 * Class TableTable
 *
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> name string(255) mandatory
 * <li> infoblock_element_id int optional
 * <li> created_at datetime optional default current datetime
 * </ul>
 *
 * @package Bitrix\Table
 **/

class TableTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'custom_table';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            'id' => (new IntegerField('id',
                []
            ))->configureTitle(Loc::getMessage('TABLE_ENTITY_ID_FIELD'))
                ->configurePrimary(true)
                ->configureAutocomplete(true)
            ,
            'name' => (new StringField('name',
                [
                    'validation' => function()
                    {
                        return[
                            new LengthValidator(null, 255),
                        ];
                    },
                ]
            ))->configureTitle(Loc::getMessage('TABLE_ENTITY_NAME_FIELD'))
                ->configureRequired(true)
            ,
            'infoblock_element_id' => (new IntegerField('infoblock_element_id',
                []
            ))->configureTitle(Loc::getMessage('TABLE_ENTITY_INFOBLOCK_ELEMENT_ID_FIELD'))
            ,
            'created_at' => (new DatetimeField('created_at',
                []
            ))->configureTitle(Loc::getMessage('TABLE_ENTITY_CREATED_AT_FIELD'))
                ->configureDefaultValue(function()
                {
                    return new DateTime();
                })
            ,
        ];
    }
}