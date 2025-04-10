<?php

use Bitrix\Main\Loader;

/**
 * Скрипт обновления остатков товаров и запуска бизнес-процесса при нулевом остатке.
 * Работает с инфоблоком каталога, получает все товары из заданного раздела,
 * для каждого SKU прибавляет случайное количество от 0 до 3 к текущему остатку.
 * Если итоговый остаток = 0 — создаёт элемент в другом инфоблоке и запускает бизнес-процесс.
 */

// Путь к лог-файлу
$logFile = __DIR__ . '/cron_log.txt';

// Логируем запуск
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] index.php стартовал\n", FILE_APPEND);

// Проверка подключения ядра Bitrix
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ядро Bitrix не подключено! Скрипт остановлен.\n", FILE_APPEND);
    return;
}

// Подключение необходимых модулей
if (
    !Loader::includeModule("iblock") ||
    !Loader::includeModule("catalog") ||
    !Loader::includeModule("bizproc")
) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Не удалось подключить модули\n", FILE_APPEND);
    return;
}

// Константы конфигурации
$iblockId = 14;           // ID инфоблока каталога
$sectionId = 13;          // ID раздела товаров
$targetIblockId = 30;     // ID инфоблока процессов
$bpId = 28;               // ID бизнес-процесса
$authorId = 15;           // ID пользователя, от имени которого создаётся элемент

// Получение элементов из каталога
$res = CIBlockElement::GetList(
    [],
    [
        'IBLOCK_ID' => $iblockId,
        'SECTION_ID' => $sectionId,
        'ACTIVE' => 'Y',
        'INCLUDE_SUBSECTIONS' => 'Y',
    ],
    false,
    false,
    ['ID', 'NAME']
);

// Обработка каждого элемента каталога
while ($element = $res->GetNext()) {
    $productId = $element['ID'];
    $productName = $element['NAME'];

    // Получаем SKU (предложения)
    $skuItems = \CCatalogSKU::getOffersList(
        [$productId],
        $iblockId,
        ['ACTIVE' => 'Y'],
        ['ID', 'NAME']
    );

    if (!empty($skuItems[$productId])) {
        foreach ($skuItems[$productId] as $sku) {
            $skuId = $sku['ID'];

            // Получаем случайное количество от 0 до 3
            $randomQuantity = file_get_contents(
                "https://www.random.org/integers/?num=1&min=0&max=3&col=1&base=10&format=plain&rnd=new"
            );

            if ($randomQuantity === false) {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка запроса random.org для SKU {$skuId}\n", FILE_APPEND);
                continue;
            }

            $randomQuantity = intval(trim($randomQuantity));

            // Получаем текущий остаток
            $productData = \Bitrix\Catalog\ProductTable::getRowById($skuId);
            $currentQuantity = (float)($productData['QUANTITY'] ?? 0);

            // Вычисляем новое количество
            $newQuantity = $currentQuantity + $randomQuantity;

            // Обновляем остаток
            $updateResult = \Bitrix\Catalog\Model\Product::update($skuId, ['QUANTITY' => $newQuantity]);

            if ($updateResult->isSuccess()) {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] SKU {$skuId}: было {$currentQuantity}, прибавили {$randomQuantity}, стало {$newQuantity}\n", FILE_APPEND);

                // Если новый остаток = 0 — создаём элемент и запускаем БП
                if ($newQuantity === 0) {
                    $el = new CIBlockElement;
                    $arFields = [
                        "IBLOCK_ID" => $targetIblockId,
                        "NAME" => "Запуск роботом",
                        "CREATED_BY" => $authorId,
                        "PROPERTY_VALUES" => [
                            "KOLICHESTVO" => 10,
                            "ELEMENT_KATALOGA_TOVAROV" => $skuId // <-- ВАЖНО: теперь используем ID вариации!
                        ]
                    ];

                    if ($newElementId = $el->Add($arFields)) {
                        CBPDocument::StartWorkflow(
                            $bpId,
                            ["lists", "Bitrix\Lists\BizprocDocumentLists", $newElementId],
                            [],
                            $errors
                        );

                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Создан элемент ID {$newElementId} и запущен БП\n", FILE_APPEND);
                    } else {
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка создания элемента: " . $el->LAST_ERROR . "\n", FILE_APPEND);
                    }
                }
            } else {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка обновления SKU ID {$skuId}: " . implode('; ', $updateResult->getErrorMessages()) . "\n", FILE_APPEND);
            }

            // Задержка между SKU
            usleep(300000);
        }
    }
}

// Логируем завершение
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] index.php завершён\n", FILE_APPEND);
