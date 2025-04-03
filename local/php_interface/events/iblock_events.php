<?php
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Iblock\ElementTable;

Loader::includeModule('iblock');
Loader::includeModule('crm');

const IBLOCK_ID_REQUESTS = 28; // ID инфоблока "Заявки"

AddEventHandler(
    "iblock",
    "OnAfterIBlockElementAdd",
    ["RequestIblockHandlers", "onAfterIBlockElementUpdate"]
);
AddEventHandler(
    "iblock",
    "OnAfterIBlockElementUpdate",
    ["RequestIblockHandlers", "onAfterIBlockElementUpdate"]
);

class RequestIblockHandlers
{
    public static function onAfterIBlockElementUpdate(&$arFields)
    {
        if ($arFields["IBLOCK_ID"] != IBLOCK_ID_REQUESTS) {
            return;
        }

        $elementId = $arFields["ID"];

        // Получаем свойства элемента
        $res = CIBlockElement::GetList(
            [],
            ["IBLOCK_ID" => IBLOCK_ID_REQUESTS, "ID" => $elementId],
            false,
            false,
            ["ID", "IBLOCK_ID", "PROPERTY_DEAL_ID", "PROPERTY_SUMM"]
        );

        if ($ob = $res->Fetch()) {
            $dealId = $ob["PROPERTY_DEAL_ID_VALUE"];
            $sum = $ob["PROPERTY_SUMM_VALUE"];

            if ($dealId) {
                $result = DealTable::update($dealId, [
                    'OPPORTUNITY' => $sum,
                    'UF_CRM_1738908152342' => $elementId, // Записываем ID заявки в пользовательское поле сделки
                ]);

                if (!$result->isSuccess()) {
                    AddMessage2Log("Ошибка обновления сделки {$dealId}: " . implode(", ", $result->getErrorMessages()), "DEBUG");
                } else {
                    AddMessage2Log("Сделка #{$dealId} обновлена. Новая сумма: {$sum}, заявка: {$elementId}", "DEBUG");
                }
            }
        }
    }
}
