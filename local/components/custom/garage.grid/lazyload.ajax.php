<?php
// Обязательно подключаем пролог
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$logFile = $_SERVER['DOCUMENT_ROOT'] . '/kazydeal_log.txt';
file_put_contents($logFile, "\n=== [lazyload.ajax.php] Запущен ===\n", FILE_APPEND);

$dealId = (int)($_REQUEST['dealId'] ?? 0);
file_put_contents($logFile, "[lazyload.ajax.php] dealId = $dealId\n", FILE_APPEND);

global $APPLICATION;
$APPLICATION->IncludeComponent(
    'custom:garage.grid',
    '.default',
    [
        'DEAL_ID' => $dealId
    ]
);
