<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class OtusGridComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/kazydeal_log.txt';
        file_put_contents($logFile, "\n=== [class.php] Старт executeComponent ===\n", FILE_APPEND);

        if (!\Bitrix\Main\Loader::includeModule('iblock'))
        {
            file_put_contents($logFile, "[class.php] Ошибка: модуль Инфоблоки не подключен\n", FILE_APPEND);
            ShowError('Модуль Инфоблоки не подключен');
            return;
        }

        // Твой код выборки из инфоблока — без изменений
        // ...
        // Пример:
        $this->arResult['ITEMS'] = [
            ['ID' => 1, 'NAME' => 'Авто №1'],
            ['ID' => 2, 'NAME' => 'Авто №2'],
        ];

        file_put_contents($logFile, "[class.php] Готовый arResult: " . print_r($this->arResult, true) . "\n", FILE_APPEND);
        $this->includeComponentTemplate();

        file_put_contents($logFile, "=== [class.php] Конец executeComponent ===\n", FILE_APPEND);
    }
}
