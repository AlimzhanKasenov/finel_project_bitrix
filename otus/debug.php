<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . "/customLogger.php");

$logger = new CustomLogger("debug_log_with_otus.txt");

$currentDateTime = date("Y-m-d H:i:s");

$logger->writeToLog($currentDateTime, "Обращение к debug.php");

echo "Лог успешно записан. Дата и время: " . $currentDateTime;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
