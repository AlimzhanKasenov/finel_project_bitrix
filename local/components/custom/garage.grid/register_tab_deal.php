<?php
use Bitrix\Main\EventManager;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

// Файл логов
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/kazydeal_log.txt';
file_put_contents($logFile, "\n=== [register_tab_deal.php] Старт подключения ===\n", FILE_APPEND);

// Подключим JavaScript для отладки
global $APPLICATION;
$APPLICATION->AddHeadString(
    '<script>
        console.log("[register_tab_deal.php] Файл подключён (AddHeadString)");
        // Отслеживаем клик на пользовательское событие (вдруг Bitrix вызывет его)
        BX.addCustomEvent("deal_13_details_click_garage_tab", function() {
            console.log("Сработал кастомный ивент: deal_13_details_click_garage_tab");
        });
    </script>',
    true
);

EventManager::getInstance()->addEventHandler(
    'crm',
    'onEntityDetailsTabsInitialized',
    function (Event $event) use ($logFile) {
        file_put_contents($logFile, "[onEntityDetailsTabsInitialized] Событие запущено\n", FILE_APPEND);

        // Получаем все вкладки, тип и ID сущности
        $tabs = $event->getParameter('tabs');
        $entityTypeId = (int)$event->getParameter('entityTypeID');
        $entityId = (int)$event->getParameter('entityID');

        file_put_contents($logFile, "entityTypeId = {$entityTypeId}\n", FILE_APPEND);
        file_put_contents($logFile, "entityId = {$entityId}\n", FILE_APPEND);

        // Проверяем, что это сделка
        // \CCrmOwnerType::Deal = 2
        if ($entityTypeId !== 2)
        {
            file_put_contents($logFile, "Это не сделка, вкладку не добавляем\n", FILE_APPEND);
            return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
        }

        // Теперь добавим ещё один JS, который скажет, что мы дошли именно до этапа "добавления вкладки"
        global $APPLICATION;
        $APPLICATION->AddHeadString(
            '<script>
                console.log("[register_tab_deal.php] Добавляем вкладку: dealId = ' . $entityId . '");
            </script>',
            true
        );

        // Формируем вкладку
        // Обрати внимание: ID и текст можешь менять, но делай это согласованно
        $tabs[] = [
            'id' => 'garage_tab', // будешь видеть в HTML как crm_scope_detail_c_deal_1__garage_tab
            'name' => 'Мой Гараж (кастом)',
            'loader' => [
                'serviceUrl' => '/local/components/custom/garage.grid/lazyload.ajax.php'
                    . '?dealId=' . $entityId
                    . '&site=' . SITE_ID
                    . '&' . bitrix_sessid_get(),
                'componentData' => [
                    'componentName' => 'custom:garage.grid',
                    'template' => '.default',
                    'params' => [
                        'DEAL_ID' => $entityId,
                    ],
                ],
            ],
            'enableLazyLoad' => true,
            'sort' => 999,
        ];

        file_put_contents($logFile, "✅ Добавили вкладку 'garage_tab' для сделки #{$entityId}\n", FILE_APPEND);

        // Возвращаем результат
        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
    }
);
