<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

// Проверяем наличие модуля валют
if (!CModule::IncludeModule("currency")) return;

// Получаем список доступных валют
$currencies = [];
$dbCurrencies = CCurrency::GetList(($by = "name"), ($order = "asc"));
while ($currency = $dbCurrencies->Fetch()) {
    $currencies[$currency['CURRENCY']] = $currency['CURRENCY'];
}

// Определяем параметры компонента
$arComponentParameters = [
    "PARAMETERS" => [
        "CURRENCY" => [
            "PARENT" => "BASE",
            "NAME" => "Выберите валюту",
            "TYPE" => "LIST",
            "VALUES" => $currencies,
            "DEFAULT" => "USD",
            "REFRESH" => "N", // Не обновляем форму при изменении
        ],
    ],
];
