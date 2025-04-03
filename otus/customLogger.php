<?php

class CustomLogger
{
    private $filePath;

    public function __construct($fileName = "custom_debug_log.txt")
    {
        $this->filePath = __DIR__ . "/" . $fileName;
    }

    /**
     * Функция для записи данных в лог
     *
     * @param mixed  $data  Данные для записи
     * @param string $title Заголовок сообщения
     * @return bool
     */
    public function writeToLog($data, $title = '')
    {
        $log = "\n------------------------\n";
        $log .= "OTUS " . date("Y.m.d G:i:s") . "\n";
        $log .= "OTUS " . (strlen($title) > 0 ? $title : 'DEBUG') . "\n";

        file_put_contents($this->filePath, $log, FILE_APPEND);

        return true;
    }
}
?>
