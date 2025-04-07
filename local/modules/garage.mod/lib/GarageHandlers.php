<?php

namespace Garage\Mod;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

/**
 * Класс GarageHandlers
 *
 * Обработчик события onEntityDetailsTabsInitialized, добавляющий кастомную вкладку "Гараж"
 * в карточку контакта в CRM Bitrix24 (коробочная версия).
 */
class GarageHandlers
{
    /**
     * Обработчик события добавления вкладки.
     * Добавляет вкладку "Гараж" в карточку контакта, если entityTypeID = 3.
     *
     * @param Event $event Событие Bitrix onEntityDetailsTabsInitialized.
     *
     * @return EventResult Результат выполнения обработчика, с добавленными вкладками.
     */
    public static function updateTabs(Event $event)
    {
        // Лог-файл для отладки
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/garage_tab_debug.log';

        // Проверяем подключение модуля CRM
        if (!Loader::includeModule('crm')) {
            file_put_contents($logFile, "[updateTabs] Модуль CRM не подключен\n", FILE_APPEND);
            return;
        }

        file_put_contents($logFile, "[updateTabs] Событие запущено\n", FILE_APPEND);

        // Получаем параметры события
        $tabs     = $event->getParameter('tabs');
        $typeId   = (int)$event->getParameter('entityTypeID'); // Тип сущности
        $entityId = (int)$event->getParameter('entityID');     // ID сущности

        file_put_contents($logFile, "typeID={$typeId}, entityID={$entityId}\n", FILE_APPEND);

        // Если это контакт (entityTypeID = 3), добавляем вкладку
        if ($typeId === 3) {
            file_put_contents($logFile, "Это контакт, добавляем вкладку «Гараж»\n", FILE_APPEND);

            $tabs[] = [
                'id' => 'garage_tab',
                'name' => 'Гараж',
                'loader' => [
                    'serviceUrl' => '/local/components/custom/grid/lazyload.ajax.php'
                        . '?contactId=' . $entityId
                        . '&site=' . SITE_ID
                        . '&' . bitrix_sessid_get(),
                    'componentData' => [
                        'componentName' => 'custom:grid',
                        'templateName' => '.default',
                        'params' => [
                            'CONTACT_ID' => $entityId,
                        ],
                    ],
                ],
                'enableLazyLoad' => true,
                'sort' => 1000,
            ];
        } else {
            file_put_contents($logFile, "Это не контакт. Вкладку не добавляем.\n", FILE_APPEND);
        }

        // Возвращаем модифицированный список вкладок
        return new EventResult(
            EventResult::SUCCESS,
            ['tabs' => $tabs]
        );
    }
}
