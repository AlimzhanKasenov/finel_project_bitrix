<?php
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use RestApi\BusinessProcessHandler;
use Bitrix\Main\Page\Asset;




//Подключение файла который добавляет кастомный тип поля
Loader::registerAutoLoadClasses(null, [
    'CustomProperty\\CatalogElementSelector' => '/local/php_interface/classes/CustomProperty/CatalogElementSelector.php',
]);

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['CustomProperty\\CatalogElementSelector', 'GetUserTypeDescription']
);

//Создание функции и регистрация агента
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/agents.php");

// Подключение обработчиков сделок
require_once $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/deal_handlers.php';















Bitrix\Main\UI\Extension::load(['popup', 'crm.currency', 'time.custom']);

// Подключаем модуль инфоблоков, если нужно
Loader::includeModule("iblock");

// Подключаем класс REST API
require_once $_SERVER["DOCUMENT_ROOT"] . "/local/rest/api/BusinessProcessHandler.php";

// Регистрируем наш класс в автозагрузке (рекомендуется)
Loader::registerAutoLoadClasses(null, [
    'Local\Rest\Api\BusinessProcessHandler' => '/local/rest/api/BusinessProcessHandler.php',
]);

// Регистрируем обработчик REST
$eventManager = EventManager::getInstance();
$eventManager->addEventHandlerCompatible(
    'rest',
    'OnRestServiceBuildDescription',
    ['Local\Rest\Api\BusinessProcessHandler', 'onRestServiceBuildDescription']
);

$eventManager = EventManager::getInstance();

// Повесим обработчики на добавление и обновление элемента, чтобы шла "синхронизация".
$eventManager->addEventHandler(
    'iblock',
    'OnBeforeIBlockElementAdd',
    ['MyHandlers', 'syncProcedures']
);

$eventManager->addEventHandler(
    'iblock',
    'OnBeforeIBlockElementUpdate',
    ['MyHandlers', 'syncProcedures']
);

// Регистрация кастомного типа свойства
EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    ['CustomProperty\ProcedureSelector', 'GetUserTypeDescription']
);

class MyHandlers
{
    /**
     * Синхронизация свойств "Запись на процедуру" -> "Процедуры"
     */
    public static function syncProcedures(&$arFields)
    {
        // Убедитесь, что это нужный инфоблок (врачи).
        // Допустим, у вас ИД инфоблока врачей = 16
        if ((int)$arFields['IBLOCK_ID'] !== 16) {
            return;
        }

        // Проверяем, что в текущем сохранении пришло свойство "ZAPIS_NA_PROTSEDURU"
        if (!empty($arFields["PROPERTY_VALUES"]["ZAPIS_NA_PROTSEDURU"])) {
            // Копируем значение из "Запись на процедуру" в "Процедуры"
            $arFields["PROPERTY_VALUES"]["PROTSEDURY"] = $arFields["PROPERTY_VALUES"]["ZAPIS_NA_PROTSEDURU"];
        }
    }
}

// --- ДОПОЛНЕНИЯ ---
// Подключение CSS и JS для модального окна на страницах админки
$eventManager->addEventHandler('main', 'OnProlog', function () {
    if (strpos($_SERVER['REQUEST_URI'], '/bitrix/admin/') !== false) {
        global $APPLICATION;

        // Подключаем JS для работы модального окна
        $APPLICATION->AddHeadScript('/local/js/procedure_modal.js');
    }
});

// Добавление HTML модального окна на страницы админки
$eventManager->addEventHandler('main', 'OnEpilog', function () {
    if (strpos($_SERVER['REQUEST_URI'], '/bitrix/admin/') !== false) {
        echo '
        <div id="procedureModal" style="display: none; position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); background: #fff; padding: 20px; z-index: 1000; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <div class="modal-header">
                <strong>Запись на процедуру</strong>
            </div>
            <div class="modal-body">
                <form id="procedureForm">
                    <input type="hidden" id="procedureId" name="procedureId" value="">
                    <div>
                        <label for="patientName">ФИО пациента:</label><br>
                        <input type="text" id="patientName" name="patientName" required><br><br>
                        <label for="appointmentDate">Дата и время:</label><br>
                        <input type="datetime-local" id="appointmentDate" name="appointmentDate" required><br>
                    </div>
                    <div class="modal-footer" style="margin-top: 10px;">
                        <button type="button" id="closeModal">Отмена</button>
                        <button type="submit">Записать</button>
                    </div>
                </form>
            </div>
        </div>
        ';
    }
});

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/';
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($baseDir . $classPath)) {
        require_once $baseDir . $classPath;
    }
});

// --- ДОПОЛНЕНИЕ: Обработчик для работы модального окна в публичной части ---
$eventManager->addEventHandler('main', 'OnProlog', function () {
    global $APPLICATION;

    // Подключаем JS для публичной части
    if (strpos($_SERVER['REQUEST_URI'], '/services/') !== false) {
        $APPLICATION->AddHeadScript('/local/js/procedure_modal.js');
    }
});

// --- ДОПОЛНЕНИЕ: Добавление HTML модального окна в публичной части ---
$eventManager->addEventHandler('main', 'OnEpilog', function () {
    if (strpos($_SERVER['REQUEST_URI'], '/services/') !== false) {
        echo '
<div id="procedureModal" style="display: none; position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); background: #fff; padding: 20px; z-index: 1000; border-radius: 8px; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); width: 400px;">
    <div class="modal-header" style="margin-bottom: 10px; text-align: center; font-size: 18px; font-weight: bold; color: #333;">
        Запись на процедуру
    </div>
    <div class="modal-body" style="font-size: 14px; color: #333;">
        <form id="procedureForm">
            <input type="hidden" id="procedureId" name="procedureId" value="">
            <div style="margin-bottom: 10px;">
                <label for="patientName">ФИО пациента:</label><br>
                <input type="text" id="patientName" name="patientName" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"><br>
            </div>
            <div style="margin-bottom: 10px;">
                <label for="appointmentDate">Дата и время:</label><br>
                <input type="datetime-local" id="appointmentDate" name="appointmentDate" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"><br>
            </div>
            <div class="modal-footer" style="margin-top: 20px; display: flex; justify-content: space-between;">
                <button type="button" id="closeModal" style="background: #ccc; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Отмена</button>
                <button type="submit" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Записать</button>
            </div>
        </form>
    </div>
</div>


        ';
    }
});


// Подключаем файл с обработчиками инфоблока
require_once __DIR__ . '/events/iblock_events.php';

// Подключаем файл с обработчиками CRM (сделки)
require_once __DIR__ . '/events/crm_events.php';