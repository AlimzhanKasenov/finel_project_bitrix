<?php

use Bitrix\Main\Application;

// Получаем соединение с базой данных
$connection = Application::getConnection();

// Выполняем запрос
$result = $connection->query("SHOW TABLES LIKE 'carriages_schedule'");

// Проверяем, существует ли таблица
if ($result->fetch()) {
	echo "Таблица существует";
} else {
	echo "Таблица не существует";
}
