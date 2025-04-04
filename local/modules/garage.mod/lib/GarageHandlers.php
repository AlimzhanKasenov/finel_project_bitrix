<?php
// Файл: /local/modules/garage.mod/lib/GarageHandlers.php

namespace Garage\Mod;

use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

class GarageHandlers
{
    public static function updateTabs(Event $event)
    {
        if (!Loader::includeModule('crm')) {
            return;
        }

        $log = $_SERVER["DOCUMENT_ROOT"] . "/garage_tab_debug.log";
        file_put_contents($log, "[updateTabs] Событие сработало\n", FILE_APPEND);

        $tabs = $event->getParameter('tabs');
        $typeId = $event->getParameter('entityTypeID');
        $entityId = $event->getParameter('entityID');

        file_put_contents($log, "entityTypeID: {$typeId}, entityID: {$entityId}\n", FILE_APPEND);

        // 3 = CONTACT
        if ((int)$typeId === \CCrmOwnerType::Contact) {
            $tabs[] = [
                'id' => 'alim_tab',
                'name' => 'Алим',
                'loader' => [
                    'serviceUrl' => '/local/components/garage/grid/templates/.default/lazyload.ajax.php'
                        . '?contactId=' . $entityId
                        . '&site=' . SITE_ID
                        . '&' . bitrix_sessid_get(),
                    'componentData' => [
                        'componentName' => 'garage:grid',
                        'templateName' => '.default',
                        'params' => [
                            'CONTACT_ID' => $entityId,
                        ]
                    ]
                ],
                'enableLazyLoad' => true,
                'sort' => 1000,
            ];

            file_put_contents($log, "✅ Добавили вкладку 'alim_tab' для контакта #{$entityId}\n", FILE_APPEND);
        }

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
    }
}
