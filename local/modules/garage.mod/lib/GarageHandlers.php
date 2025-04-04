<?php
namespace Garage\Mod;

use Bitrix\Main\Event;
use Bitrix\Main\Loader;

class GarageHandlers
{
    public static function updateTabs(\Bitrix\Main\Event $event)
    {
        $typeId = $event->getParameter('entityTypeID');

        $log = $_SERVER["DOCUMENT_ROOT"] . "/garage_tab_debug.log";
        file_put_contents($log, "entityTypeID: " . var_export($typeId, true) . "\n", FILE_APPEND);

        $tabs = $event->getParameter('tabs');

        if ((int)$typeId === \CCrmOwnerType::Deal) {
            $tabs[] = [
                'id' => 'alim_tab',
                'name' => 'Алим',
                'loader' => [
                    'serviceUrl' => '/local/components/garage/grid/templates/.default/lazyload.ajax.php',
                ],
                'sort' => 1000,
            ];

            file_put_contents($log, "Вкладка Алим добавлена!\n", FILE_APPEND);
        }

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            ['tabs' => $tabs]
        );
    }


}
