<?php

define("BX_DEBUG", true);
ini_set('display_errors', 1);
error_reporting(E_ALL);

use Bitrix\Table\TableTable;
use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// Проверяем, что модуль main загружен
if (!Loader::includeModule('main')) {
    die('Модуль main не загружен');
}

file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/viewCustomTable.log', "Скрипт начат\n", FILE_APPEND);

try {
    // Выполняем запрос к базе через модель TableTable
    $result = TableTable::getList([
        'select' => ['id', 'name', 'infoblock_element_id', 'created_at'],
        'order' => ['id' => 'DESC'], // Сортируем по ID
    ]);
    file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/viewCustomTable.log', "Данные получены\n", FILE_APPEND);
} catch (\Exception $e) {
    file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/viewCustomTable.log', "Ошибка: " . $e->getMessage() . "\n", FILE_APPEND);
    die('Ошибка: ' . $e->getMessage());
}

// Вывод данных в таблице
echo '<table border="1" cellspacing="0" cellpadding="5">';
echo '<tr><th>ID</th><th>Имя</th><th>ID Инфоблока</th><th>Дата создания</th></tr>';

while ($row = $result->fetch()) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['id']) . '</td>';
    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['infoblock_element_id']) . '</td>';
    echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
    echo '</tr>';
}

echo '</table>';

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
