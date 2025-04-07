<?php
/**
 * Подключение компонента custom:grid в режиме lazyload (AJAX)
 * Используется для загрузки вкладки "Гараж" в карточке контакта.
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

// Подключаем компонент custom:grid с шаблоном .default
// Передаём ID контакта из параметра запроса
$APPLICATION->IncludeComponent(
    'custom:grid',
    '.default',
    [
        'CONTACT_ID' => (int)($_GET['contactId'] ?? 0),
    ]
);
