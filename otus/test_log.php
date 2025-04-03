<?php
// Включение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение ядра Bitrix
require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php";

// Проверка загрузки модуля 'main'
if (!\Bitrix\Main\Loader::includeModule('main')) {
    die("Модуль 'main' не загружен. Проверьте настройки системы.");
}

// Подключение вашего класса
use local\file\otus_file_exception_handler_log\OtusFileExceptionHandlerLog;

try {
    // Искусственно создаем исключение
    throw new \Exception("Тестовое исключение для проверки логов. Запись прошла успешно");
} catch (\Exception $e) {
    // Создаем объект логгера и записываем лог
    $logger = new OtusFileExceptionHandlerLog();
    $logger->write($e, 'ERROR что то пошло не так');
}

echo "Логирование завершено. Проверьте файл логов.";
