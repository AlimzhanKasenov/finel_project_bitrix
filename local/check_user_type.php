<?php
use Bitrix\Main\Loader;

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
Loader::includeModule('iblock');

$propertyTypes = [];
foreach (GetModuleEvents("iblock", "OnIBlockPropertyBuildList", true) as $event) {
    $propertyTypes[] = ExecuteModuleEventEx($event);
}

header('Content-Type: application/json');
echo json_encode($propertyTypes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
