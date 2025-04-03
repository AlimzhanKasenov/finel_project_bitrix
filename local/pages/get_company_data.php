<?php
// Подключаем Bitrix
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use CIBlockElement;

// Устанавливаем заголовок ответа
header("Content-Type: application/json; charset=UTF-8");

// Логирование
$logFile = $_SERVER['DOCUMENT_ROOT'] . "/local/logs/dadata_debug.log";

// Массив для логирования ошибок
$errorLog = [];

// Проверка подключения модуля инфоблоков
if (!Loader::includeModule('iblock')) {
    $errorLog[] = "Модуль iblock не подключён";
    file_put_contents($logFile, implode("\n", $errorLog), FILE_APPEND);
    echo json_encode(["status" => "error", "errors" => $errorLog]);
    exit;
}

// Считываем параметры
$inn = isset($_GET['inn']) ? trim($_GET['inn']) : null;
$elementId26 = isset($_GET['element_id_26']) ? (int)$_GET['element_id_26'] : 0;

// Проверяем входные данные
if (!$inn || !preg_match('/^\d{10,12}$/', $inn)) {
    $errorLog[] = "Некорректный ИНН (должен содержать 10 или 12 цифр)";
}
if ($elementId26 <= 0) {
    $errorLog[] = "Некорректный ID элемента инфоблока 26";
}

// Если уже есть ошибки, записываем их и выходим
if (!empty($errorLog)) {
    file_put_contents($logFile, implode("\n", $errorLog), FILE_APPEND);
    echo json_encode(["status" => "error", "errors" => $errorLog], JSON_UNESCAPED_UNICODE);
    exit;
}

// **Настройки инфоблоков**
$iblockId27 = 27; // Инфоблок компаний
$iblockId26 = 26; // Инфоблок, куда записываем ID компании
$zakazchikProp = "ZAKAZCHIK"; // Свойство в ИБ 26

// **1. Ищем компанию в инфоблоке 27 по ИНН**
$existingElementId27 = null;
$companyName = null;

$res = CIBlockElement::GetList(
    ['ID' => 'ASC'],
    ['IBLOCK_ID' => $iblockId27, '=PROPERTY_INN' => $inn, 'ACTIVE' => 'Y'],
    false,
    ['nTopCount' => 1],
    ['ID', 'NAME']
);

if ($arCompany = $res->Fetch()) {
    $existingElementId27 = (int)$arCompany['ID'];
    $companyName = $arCompany['NAME'];
}

// **2. Если компания не найдена в Битрикс, запрашиваем Dadata**
if (!$existingElementId27) {
    $token = "e035caa2775243da49a0701a724b7dae11ee400f"; // Ваш API-ключ
    $url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";

    $data = [
        "query" => $inn,
        "count" => 1,
        "type" => "LEGAL" // **Ищем только юрлица**
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Token " . $token,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    // Логирование ответа
    file_put_contents($logFile, "Запрос к Dadata: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    file_put_contents($logFile, "Ответ Dadata: " . $response . "\n", FILE_APPEND);

    if ($curlErr) {
        $errorLog[] = "Ошибка при запросе к Dadata: " . $curlErr;
    } else {
        $responseData = json_decode($response, true);

        if (empty($responseData['suggestions'])) {
            $errorLog[] = "Компания по ИНН не найдена в Dadata";
        } else {
            $company = $responseData['suggestions'][0];
            $companyName = $company["value"];
            $dataFields = $company["data"];

            $fields27 = [
                "IBLOCK_ID" => $iblockId27,
                "NAME" => $companyName,
                "ACTIVE" => "Y",
                "PROPERTY_VALUES" => [
                    "INN" => $dataFields["inn"] ?? "",
                    "KPP" => $dataFields["kpp"] ?? "",
                    "OGRN" => $dataFields["ogrn"] ?? "",
                    "ADRES" => $dataFields["address"]["unrestricted_value"] ?? "",
                ],
            ];

            $el = new CIBlockElement();
            $newElementId = $el->Add($fields27);

            if (!$newElementId) {
                $errorLog[] = "Ошибка создания элемента в инфоблоке 27: " . $el->LAST_ERROR;
            } else {
                $existingElementId27 = (int)$newElementId;
            }
        }
    }
}

// **3. Если компания всё ещё не найдена, ошибка**
if (!$existingElementId27) {
    $errorLog[] = "Не найдено/не создано ни одного элемента в ИБ 27 для ИНН={$inn}";
}

// **4. Привязка компании к элементу в инфоблоке 26**
if (empty($errorLog)) {
    $el = new CIBlockElement();
    $updateProps = [$zakazchikProp => $existingElementId27];
    $el->SetPropertyValuesEx($elementId26, $iblockId26, $updateProps);
}

// **5. Формируем итоговый ответ**
if (!empty($errorLog)) {
    file_put_contents($logFile, implode("\n", $errorLog), FILE_APPEND);
    echo json_encode(["status" => "error", "errors" => $errorLog], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "status" => "success",
        "id_infoblock_27" => $existingElementId27,
        "id_infoblock_26" => $elementId26,
        "name" => $companyName
    ], JSON_UNESCAPED_UNICODE);
}