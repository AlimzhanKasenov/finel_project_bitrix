<?php

use Bitrix\Main\Localization\Loc;

/**
 * Описание модуля garage.mod для регистрации в системе Битрикс.
 *
 * Массив содержит основные параметры модуля:
 * - MODULE_NAME: Название модуля.
 * - MODULE_DESCRIPTION: Краткое описание модуля.
 * - PARTNER_NAME: Название партнёра/разработчика.
 * - PARTNER_URI: Сайт партнёра.
 *
 * Все значения берутся из языковых файлов через Loc::getMessage().
 *
 * @var array<string, string> $arModuleDescription
 */
$arModuleDescription = [
    'MODULE_NAME' => Loc::getMessage('OTUS_HOMEWORK_MODULE_NAME'),
    'MODULE_DESCRIPTION' => Loc::getMessage('OTUS_HOMEWORK_MODULE_DESC'),
    'PARTNER_NAME' => Loc::getMessage('OTUS_HOMEWORK_PARTNER_NAME'),
    'PARTNER_URI' => Loc::getMessage('OTUS_HOMEWORK_PARTNER_URI'),
];
