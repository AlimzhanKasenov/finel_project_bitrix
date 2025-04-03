<?php
namespace local\file\otus_file_exception_handler_log;

use Bitrix\Main\Diag\ExceptionHandler;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

class OtusFileExceptionHandlerLog extends ExceptionHandler
{
    private $logFile;

    public function __construct()
    {
        // Устанавливаем путь к файлу логов
        $this->logFile = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/otus_exceptions.log';

        // Убедимся, что файл существует или создаем его
        if (!file_exists($this->logFile)) {
            touch($this->logFile);
            chmod($this->logFile, 0666); // Даем права на запись
        }
    }

    public function write($exception, $logType)
    {
        // Форматируем исключение через стандартный форматтер Bitrix
        $text = ExceptionHandlerFormatter::format($exception);

        // Устанавливаем уровень логирования
        $logLevel = strtoupper($logType);

        // Формируем сообщение
        $message = "[date: " . date('Y-m-d H:i:s') . "] Host: {$_SERVER['HTTP_HOST']} Type: {$logLevel} - {$text}";

        // Разбиваем сообщение на строки
        $lines = explode("\n", $message);

        // Добавляем "OTUS" в начало каждой строки
        foreach ($lines as &$line) {
            if (trim($line) !== '') {
                $line = 'OTUS ' . $line;
            }
        }

        // Объединяем строки обратно
        $message = implode("\n", $lines);

        // Пишем сообщение в файл
        $this->writeToFile($message);
    }

    private function writeToFile($message)
    {
        // Добавляем сообщение в лог-файл
        file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND);
    }
}
