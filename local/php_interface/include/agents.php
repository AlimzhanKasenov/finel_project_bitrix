<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Application;

function RunStockUpdateAgent()
{
    $logFile = $_SERVER["DOCUMENT_ROOT"] . '/local/pages/cron_log.txt';
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] АГЕНТ Этот файл стартовал стартовал\n", FILE_APPEND);

    global $USER;

    if (!is_object($USER)) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    }

    // Авторизуемся под пользователем ID 15
    $userId = 15;
    $USER = new CUser;
    $USER->Authorize($userId);

    if (!$USER->IsAuthorized()) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка авторизации пользователя ID {$userId}\n", FILE_APPEND);
        return "RunStockUpdateAgent();";
    } else {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Авторизация прошла успешно для пользователя ID {$userId}\n", FILE_APPEND);
    }

    // Подключение модулей
    if (!Loader::includeModule("iblock") || !Loader::includeModule("catalog") || !Loader::includeModule("bizproc")) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Не удалось подключить модули\n", FILE_APPEND);
        return "RunStockUpdateAgent();";
    }

    $iblockId = 14;
    $sectionId = 13;
    $targetIblockId = 30;
    $bpId = 28;

    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'SECTION_ID' => $sectionId, 'ACTIVE' => 'Y', 'INCLUDE_SUBSECTIONS' => 'Y'],
        false,
        false,
        ['ID', 'NAME']
    );

    while ($element = $res->GetNext()) {
        $productId = $element['ID'];
        $productName = $element['NAME'];

        $skuItems = \CCatalogSKU::getOffersList([$productId], $iblockId, ['ACTIVE' => 'Y'], ['ID', 'NAME']);

        if (!empty($skuItems[$productId])) {
            foreach ($skuItems[$productId] as $sku) {
                $skuId = $sku['ID'];

                $randomQuantity = file_get_contents(
                    "https://www.random.org/integers/?num=1&min=0&max=3&col=1&base=10&format=plain&rnd=new"
                );

                if ($randomQuantity === false) {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка запроса random.org для SKU {$skuId}\n", FILE_APPEND);
                    continue;
                }

                $randomQuantity = intval(trim($randomQuantity));
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Установлено количество {$randomQuantity} для SKU ID {$skuId}\n", FILE_APPEND);

                $updateResult = \Bitrix\Catalog\Model\Product::update($skuId, ['QUANTITY' => $randomQuantity]);

                if ($updateResult->isSuccess()) {
                    if ($randomQuantity === 0) {
                        $el = new CIBlockElement;
                        $arFields = [
                            "IBLOCK_ID" => $targetIblockId,
                            "NAME" => "Запуск роботом",
                            "CREATED_BY" => $userId,
                            "PROPERTY_VALUES" => [
                                "KOLICHESTVO" => 10,
                                "ELEMENT_KATALOGA_TOVAROV" => $productId
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
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка обновления SKU ID {$skuId}: " . implode(', ', $updateResult->getErrorMessages()) . "\n", FILE_APPEND);
                }

                usleep(300000);
            }
        }
    }

    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] АГЕНТ завершён\n", FILE_APPEND);
    return "RunStockUpdateAgent();";
}
