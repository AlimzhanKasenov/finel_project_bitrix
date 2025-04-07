<?php
/**
 * Описание компонента custom:grid
 *
 * Компонент отображает список автомобилей, связанных с контактом,
 * и по клику открывает последние 5 сделок, связанных с выбранным автомобилем.
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    'NAME' => 'Custom Grid',
    'DESCRIPTION' => 'Список авто + сделки (Controllerable)',
    'PATH' => [
        'ID' => 'custom',
        'NAME' => 'Пользовательские компоненты'
    ],
    'COMPLEX' => 'N',
    'CACHE_PATH' => 'Y',  // Использовать кэш для шаблонов
    'AJAX_MODE' => 'Y',   // Поддержка AJAX (для старых шаблонов)
];
