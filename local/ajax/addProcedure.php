<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Application;

require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";
Loader::includeModule('iblock');

$request = Application::getInstance()->getContext()->getRequest();
$data = json_decode($request->getInput(), true);

$procedureId = (int)($data['procedureId'] ?? 0);
$name = htmlspecialchars(trim($data['name'] ?? ''));
$date = htmlspecialchars(trim($data['date'] ?? ''));

$iblockId = 22; // ID инфоблока "Бронирование"

if (!$procedureId || !$name || !$date) {
    echo json_encode(["status" => "error", "message" => "Не все данные переданы!"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Проверяем занятость времени перед созданием записи
$res = \CIBlockElement::GetList([], [
    "IBLOCK_ID" => $iblockId,
    "PROPERTY_PROTSEDURA" => $procedureId,
    "PROPERTY_VREMYA_ZAPISI" => $date,
], false, false, ["ID"]);

if ($res->Fetch()) {
    echo json_encode(["status" => "error", "message" => "Это время уже занято!"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Создаём элемент
$el = new \CIBlockElement;
$fields = [
    "IBLOCK_ID" => $iblockId,
    "NAME" => $name,
    "PROPERTY_VALUES" => [
        "PROTSEDURA"    => $procedureId,
        "VREMYA_ZAPISI" => $date,
        "FIO_VRACHA"    => "", // Если связь с врачами потребуется
    ],
];

if ($newId = $el->Add($fields)) {
    echo json_encode(["status" => "success", "message" => "Запись успешно создана!", "id" => $newId], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["status" => "error", "message" => "Ошибка создания записи: ".$el->LAST_ERROR], JSON_UNESCAPED_UNICODE);
}
