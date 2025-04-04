<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Описание компонента garage.grid для административного интерфейса Bitrix.
 * Используется в карточке CRM (например, контакта или сделки) как кастомная вкладка "Гараж".
 *
 * @var array $arComponentDescription
 */
$arComponentDescription = [
    "NAME" => "garage.grid", // Уникальное имя компонента
    "DESCRIPTION" => "Компонент для вкладки 'Гараж'", // Краткое описание
    "PATH" => [
        "ID" => "garage",     // Группа компонентов (ID)
        "NAME" => "Garage"    // Название группы компонентов
    ]
];
