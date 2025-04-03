<?php
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Iblock\ElementTable;

Loader::includeModule('crm');
Loader::includeModule('iblock');

AddEventHandler(
    "crm",
    "OnAfterCrmDealUpdate",
    ["CrmDealHandlers", "onAfterDealUpdate"]
);

class CrmDealHandlers
{
    public static function onAfterDealUpdate($arFields)
    {
        $dealId = $arFields["ID"];

        $res = DealTable::getList([
            'filter' => ['=ID' => $dealId],
            'select' => ['ID', 'OPPORTUNITY', 'UF_CRM_1738908152342']
        ]);

        if ($deal = $res->fetch()) {
            $iblockElementId = $deal['UF_CRM_1738908152342']; // ID заявки из сделки

            if ($iblockElementId) {
                // Получаем текущие свойства элемента инфоблока, чтобы не затирать их
                $elementData = self::getElementProperties($iblockElementId);

                if (!$elementData) {
                    AddMessage2Log("Ошибка: заявка {$iblockElementId} не найдена", "DEBUG");
                    return;
                }

                // Оставляем старые данные, кроме суммы
                $props = [
                    "SUMM" => $deal["OPPORTUNITY"],
                    "ASSIGNED" => $elementData["ASSIGNED"], // Сохранение Ответственного
                    "DEAL_ID" => $elementData["DEAL_ID"], // Сохранение ID сделки
                ];

                $el = new CIBlockElement;
                $arLoad = ["PROPERTY_VALUES" => $props];

                if ($el->Update($iblockElementId, $arLoad)) {
                    AddMessage2Log("Заявка #{$iblockElementId} обновлена. Новая сумма: {$deal["OPPORTUNITY"]}", "DEBUG");
                } else {
                    AddMessage2Log("Ошибка обновления заявки #{$iblockElementId}", "DEBUG");
                }
            }
        }
    }

    private static function getElementProperties($elementId)
    {
        $res = CIBlockElement::GetList(
            [],
            ["ID" => $elementId, "IBLOCK_ID" => 28],
            false,
            false,
            ["ID", "IBLOCK_ID", "PROPERTY_DEAL_ID", "PROPERTY_SUMM", "PROPERTY_ASSIGNED"]
        );

        if ($ob = $res->Fetch()) {
            return [
                "DEAL_ID" => $ob["PROPERTY_DEAL_ID_VALUE"],
                "ASSIGNED" => $ob["PROPERTY_ASSIGNED_VALUE"],
            ];
        }
        return null;
    }
}
