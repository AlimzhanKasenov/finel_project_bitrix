<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();
$eventManager->unRegisterEventHandler(
    'crm',
    'onEntityDetailsTabsInitialized',
    'garage.mod',
    '\\Garage\\Mod\\GarageHandlers',
    'updateTabs'
);

$eventManager->registerEventHandler(
    'crm',
    'onEntityDetailsTabsInitialized',
    'garage.mod',
    '\\Garage\\Mod\\GarageHandlers',
    'updateTabs'
);

echo "Обработчик перерегистрирован";
