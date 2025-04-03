<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

// Проверяем подключение модуля валют
if (!CModule::IncludeModule("currency")) {
    ShowError("Модуль 'currency' не установлен.");
    return;
}

// Получаем выбранную валюту
$currencyCode = $arParams['CURRENCY'] ?: 'USD'; // Валюта по умолчанию
$baseCurrency = CCurrency::GetBaseCurrency();

// Получаем курс выбранной валюты к базовой валюте
$currencyRate = CCurrencyRates::ConvertCurrency(1, $currencyCode, $baseCurrency);

// Формируем данные для шаблона
$arResult['CURRENCY_CODE'] = $currencyCode;
$arResult['CURRENCY_RATE'] = $currencyRate;

// Подключаем шаблон компонента
$this->IncludeComponentTemplate();
