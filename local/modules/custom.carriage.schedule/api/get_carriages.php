<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Custom\CarriageSchedule\Model\CarriageTable;

header('Content-Type: application/json');

if (!Loader::includeModule("custom.carriage.schedule")) {
	echo json_encode(["error" => "Модуль не установлен"]);
	exit;
}

$carriages = CarriageTable::getList([
	'select' => ['*'],
	'order' => ['DEPARTURE_TIME' => 'ASC']
])->fetchAll();

echo json_encode($carriages);
