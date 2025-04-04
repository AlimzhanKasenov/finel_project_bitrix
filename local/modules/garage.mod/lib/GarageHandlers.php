<?php

namespace Garage\Mod;

use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

/**
 * Класс GarageHandlers содержит обработчики событий для расширения CRM.
 */
class GarageHandlers
{
    /**
     * Обработчик события добавления вкладок в карточку CRM.
     * Добавляет кастомную вкладку "Гараж" в карточку контакта.
     *
     * @param Event $event Событие, содержащее параметры:
     *                     - tabs (array): массив существующих вкладок.
     *                     - entityTypeID (int): тип сущности (например, контакт).
     *                     - entityID (int): ID сущности.
     *
     * @return EventResult Результат события с обновлённым массивом вкладок.
     */
    public static function updateTabs(Event $event): EventResult
    {
        if (!Loader::includeModule('crm')) {
            return new EventResult(EventResult::ERROR, []);
        }

        $log = $_SERVER["DOCUMENT_ROOT"] . "/garage_tab_debug.log";
        file_put_contents($log, "[updateTabs] Событие сработало\n", FILE_APPEND);

        $tabs = $event->getParameter('tabs');
        $typeId = $event->getParameter('entityTypeID');
        $entityId = $event->getParameter('entityID');

        file_put_contents($log, "entityTypeID: {$typeId}, entityID: {$entityId}\n", FILE_APPEND);

        if ((int)$typeId === \CCrmOwnerType::Contact) {
            $tabs[] = [
                'id' => 'garage_tab',
                'name' => 'Гараж',
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

            file_put_contents($log, "✅ Добавили вкладку 'garage_tab' для контакта #{$entityId}\n", FILE_APPEND);
        }

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
    }
}
