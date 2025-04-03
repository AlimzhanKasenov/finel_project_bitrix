<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

if (!Loader::includeModule("bizproc")) {
    echo json_encode(["error" => "Модуль бизнес-процессов не загружен"]);
    exit;
}

$inn = $_POST["inn"] ?? '';

if (!$inn) {
    echo json_encode(["error" => "ИНН не передан"]);
    exit;
}

$url = "https://test.comportal.kz/local/pages/get_company_data.php";
$response = file_get_contents($url . "?inn=" . $inn);
$data = json_decode($response, true);

if (!$data || isset($data["error"])) {
    echo json_encode(["error" => $data["error"] ?? "Ошибка получения данных"]);
    exit;
}

echo json_encode([
    "name" => $data["name"],
    "ogrn" => $data["ogrn"],
    "kpp" => $data["kpp"],
    "address" => $data["address"]
]);
?>
