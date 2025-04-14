<?php

use Bitrix\Main\Loader;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\Model\Product;

/**
 * Увеличивает остаток первой найденной SKU-вариации родительского товара.
 *
 * @param int $productId ID родительского товара (основной товар).
 * @param float $addQuantity Количество, на которое увеличивается остаток.
 * @param int $offersIblockId ID инфоблока торговых предложений (по умолчанию 14).
 * @return array ['success' => bool, 'message' => string]
 */
function updateFirstSkuQuantity(int $productId, float $addQuantity, int $offersIblockId = 14): array
{
    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

    if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
        return ['success' => false, 'message' => '❌ Не удалось подключить модули catalog или iblock'];
    }

    $skuItems = \CCatalogSKU::getOffersList(
        [$productId],
        $offersIblockId,
        ['ACTIVE' => 'Y'],
        ['ID', 'NAME']
    );

    if (empty($skuItems[$productId])) {
        return ['success' => false, 'message' => "❌ Не найдены SKU для товара с ID {$productId}"];
    }

    $sku = reset($skuItems[$productId]);
    $skuId = $sku['ID'];

    $productData = ProductTable::getRowById($skuId);
    $currentQuantity = (float)($productData['QUANTITY'] ?? 0);
    $newQuantity = $currentQuantity + $addQuantity;

    $result = Product::update($skuId, ['QUANTITY' => $newQuantity]);

    if ($result->isSuccess()) {
        return [
            'success' => true,
            'message' => "✅ Успешно: SKU ID {$skuId} (для товара {$productId}): было {$currentQuantity}, стало {$newQuantity}"
        ];
    }

    return ['success' => false, 'message' => '❌ Ошибка обновления: ' . implode('; ', $result->getErrorMessages())];
}
