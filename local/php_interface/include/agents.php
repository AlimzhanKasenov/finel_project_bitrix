<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Application;

/**
 * Агент обновляет остатки товаров в заданном разделе.
 * Для каждой вариации (SKU) товара устанавливается случайное количество от 0 до 3.
 * Если количество равно 0, создаётся элемент в другом инфоблоке и запускается бизнес-процесс.
 *
 * @return string строка для повторного запуска агента
 */
function RunStockUpdateAgent()
{
    /** @var string $logFile Путь до лог-файла */
    $logFile = $_SERVER["DOCUMENT_ROOT"] . '/local/pages/cron_log.txt';
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] АГЕНТ Этот файл стартовал стартовал\n", FILE_APPEND);

    global $USER;

    // Проверяем и подключаем пользователя
    if (!is_object($USER)) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    }

    /** @var int $userId ID пользователя, под которым будет выполняться агент */
    $userId = 15;

    $USER = new CUser;
    $USER->Authorize($userId);

    if (!$USER->IsAuthorized()) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Ошибка авторизации пользователя ID {$userId}\n", FILE_APPEND);
        return "RunStockUpdateAgent();";
    } else {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Авторизация прошла успешно для пользователя ID {$userId}\n", FILE_APPEND);
    }

    // Подключаем необходимые модули
    if (!Loader::includeModule("iblock") || !Loader::includeModule("catalog") || !Loader::includeModule("bizproc")) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Не удалось подключить модули\n", FILE_APPEND);
        return "RunStockUpdateAgent();";
    }

    /** @var int $iblockId ID инфоблока основного каталога */
    $iblockId = 14;

    /** @var int $sectionId ID раздела товаров, по которому происходит выборка */
    $sectionId = 13;

    /** @var int $targetIblockId ID инфоблока, в который будет добавляться элемент при нулевом остатке */
    $targetIblockId = 30;

    /** @var int $bpId ID бизнес-процесса, который запускается при добавлении элемента */
    $bpId = 28;

    // Получаем все активные элементы из нужного раздела
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'SECTION_ID' => $sectionId, 'ACTIVE' => 'Y', 'INCLUDE_SUBSECTIONS' => 'Y'],
        false,
        false,
        ['ID', 'NAME']
    );

    while ($element = $res->GetNext()) {
        /** @var int $productId ID товара */
        $productId = $element['ID'];
        /** @var string $productName Название товара */
        $productName = $element['NAME'];

        // Получаем торговые предложения (SKU) для товара
        $skuItems = \CCatalogSKU::getOffersList([$productId], $iblockId, ['ACTIVE' => 'Y'], ['ID', 'NAME']);

        if (!empty($skuItems[$productId])) {
            foreach ($skuItems[$productId] as $sku) {
                /** @var int $skuId ID вариации товара */
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
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Установлено количество {$randomQuantity} для SKU ID {$skuId}\n", FILE_APPEND);

                // Обновляем остаток
                $updateResult = \Bitrix\Catalog\Model\Product::update($skuId, ['QUANTITY' => $randomQuantity]);

                if ($updateResult->isSuccess()) {
                    // Если остаток = 0 — создаём элемент и запускаем БП
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

                // Задержка между итерациями (0.3 секунды)
                usleep(300000);
            }
        }
    }

    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] АГЕНТ завершён\n", FILE_APPEND);
    return "RunStockUpdateAgent();";
}
