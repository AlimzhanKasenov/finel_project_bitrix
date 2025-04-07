<?php

use Bitrix\Main\Loader;

// Лог-файл
$logFile = __DIR__ . '/cron_log.txt';

// Логируем запуск
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] index.php стартовал\n", FILE_APPEND);

// Проверяем, подключено ли ядро Bitrix
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ядро Bitrix не подключено! Скрипт остановлен.\n", FILE_APPEND);
    return;
}

// Подключаем модули
if (
    !Loader::includeModule("iblock") ||
    !Loader::includeModule("catalog") ||
    !Loader::includeModule("bizproc")
) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Не удалось подключить модули\n", FILE_APPEND);
    return;
}

// Константы
$iblockId = 14; // Каталог
$sectionId = 13;
$targetIblockId = 30;
$bpId = 28;
$authorId = 15;

// Получаем элементы
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

// Обработка
while ($element = $res->GetNext()) {
    $productId = $element['ID'];
    $productName = $element['NAME'];

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

            // Обновляем остаток
            $updateResult = \Bitrix\Catalog\Model\Product::update($skuId, ['QUANTITY' => $randomQuantity]);

            if ($updateResult->isSuccess()) {
                if ($randomQuantity === 0) {
                    $el = new CIBlockElement;
                    $arFields = [
                        "IBLOCK_ID" => $targetIblockId,
                        "NAME" => "Запуск роботом",
                        "CREATED_BY" => $authorId,
                        "PROPERTY_VALUES" => [
                            "KOLICHESTVO" => 10,
                            "ELEMENT_KATALOGA_TOVAROV" => $productId
                        ]
                    ];

                    if ($newElementId = $el->Add($arFields)) {
                        CBPDocument::StartWorkflow(
                            $bpId,
                            ["lists", "Bitrix\Lists\BizprocDocumentLists", $newElementId],
                            [], $errors
                        );

                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Создан элемент ID {$newElementId} и запущен БП\n", FILE_APPEND);
                    } else {
                        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка создания элемента: " . $el->LAST_ERROR . "\n", FILE_APPEND);
                    }
                }
            } else {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка обновления SKU ID {$skuId}\n", FILE_APPEND);
            }

            // Задержка между запросами — 0.3 секунды
            usleep(300000);
        }
    }
}

// Логируем завершение
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] index.php завершён\n", FILE_APPEND);
