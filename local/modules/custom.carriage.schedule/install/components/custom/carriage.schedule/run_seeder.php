<?php

use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Читаем входные данные
	$inputData = json_decode(file_get_contents('php://input'), true);

	// Проверка, что запрос содержит необходимое действие
	if (isset($inputData['action']) && $inputData['action'] === 'run_seeder') {
		try {
			// Запускаем сидер
			Loader::includeModule("custom.carriage.schedule");
			\Custom\CarriageSchedule\CarriageSeeder::seed();

			// Отправляем успешный ответ
			echo json_encode(['success' => true]);
		} catch (Exception $e) {
			// Отправляем ошибку
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
