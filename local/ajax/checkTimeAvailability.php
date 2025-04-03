<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Application;

require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";
Loader::includeModule('iblock');

$request = Application::getInstance()->getContext()->getRequest();
$data = json_decode($request->getInput(), true);

$procedureId = (int)($data['procedureId'] ?? 0);
$date = htmlspecialchars(trim($data['date'] ?? ''));

if (!$procedureId || !$date) {
    echo json_encode(["status" => "error", "message" => "Не все данные переданы!"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Проверяем занятость времени
$res = \CIBlockElement::GetList([], [
    "IBLOCK_ID" => 22,
    "PROPERTY_PROTSEDURA" => $procedureId,
    "PROPERTY_VREMYA_ZAPISI" => $date,
], false, false, ["ID"]);

if ($res->Fetch()) {
    echo json_encode(["status" => "error", "message" => "Это время уже занято для данной процедуры!"], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(["status" => "success", "message" => "Время доступно для записи."], JSON_UNESCAPED_UNICODE);
