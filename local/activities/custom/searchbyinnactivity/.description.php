<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME"        => Loc::getMessage("SEARCHBYINN_DESCR_NAME"),   // Название активности
    "DESCRIPTION" => Loc::getMessage("SEARCHBYINN_DESCR_DESCR"),  // Описание активности
    "TYPE"        => "activity",
    "CLASS"       => "CBPSearchByInnActivity",                    // Важно совпадение с именем класса
    "JSCLASS"     => "BizProcActivity",
    "CATEGORY"    => [
        "ID"       => "other",
        "OWN_ID"   => "custom",
        "OWN_NAME" => "Пользовательские активности",
    ],
    // Описание возвращаемых значений
    "RETURN" => [
        "Text" => [
            "NAME" => Loc::getMessage("SEARCHBYINN_DESCR_FIELD_TEXT"),
            "TYPE" => "string",
        ],
        "ZakazchikElementID" => [
            "NAME" => Loc::getMessage("SEARCHBYINN_DESCR_FIELD_ZAKAZCHIK_ELEMENT_ID"),
            "TYPE" => "int",
        ],
        "ErrorText" => [
            "NAME" => Loc::getMessage("SEARCHBYINN_DESCR_FIELD_ERROR_TEXT"),
            "TYPE" => "text",
        ],
    ],
];
