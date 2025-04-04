<?php
/**
 * Стартовый скрипт для lazy-загрузки компонента `garage:grid` во вкладке CRM.
 * Используется во вкладке "Гараж", добавляемой в карточку контакта.
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

global $APPLICATION;

// Устанавливаем корректную кодировку для вывода HTML
header('Content-Type: text/html; charset=UTF-8');

// Подключение компонента garage:grid без параметров (заполняются внутри компонента)
$APPLICATION->IncludeComponent(
    'garage:grid', // Название компонента
    '.default',    // Шаблон компонента
    []             // Параметры (при необходимости можно передать CONTACT_ID, DEAL_ID и т.д.)
);
