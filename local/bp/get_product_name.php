<?php

use Bitrix\Main\Loader;

/**
 * Получает название товара по его ID из инфоблока каталога.
 *
 * @param int $productId ID товара.
 * @param int $iblockId ID инфоблока каталога (по умолчанию 14).
 * @return string Название товара или сообщение об ошибке.
 */
function getProductName(int $productId, int $iblockId = 14): string
{
    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

    if (!Loader::includeModule("iblock")) {
        return "❌ Модуль iblock не подключен";
    }

    if (!$productId) {
        return "❌ ID товара не передан";
    }

    $res = \CIBlockElement::GetList(
        [],
        [
            'ID' => $productId,
            'IBLOCK_ID' => $iblockId
        ],
        false,
        false,
        ['ID', 'NAME']
    );

    if ($item = $res->GetNext()) {
        return $item['NAME'];
    }

    return "❌ Товар не найден";
}
