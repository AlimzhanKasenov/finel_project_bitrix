<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Custom\CarriageSchedule\CarriageTable;

header('Content-Type: application/json; charset=UTF-8');

// Обработка GET-запроса
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	// Считываем параметры фильтра
	$status = $_GET['status'] ?? null;
	$type = $_GET['type'] ?? null;
	$departureStation = $_GET['departure_station'] ?? null;

	// Строим условия для фильтрации
	$filter = [];
	if ($status) {
		$filter['STATUS'] = $status;
	}
	if ($type) {
		$filter['TYPE'] = $type;
	}
	if ($departureStation) {
		$filter['DEPARTURE_STATION'] = $departureStation;
	}

	// Получаем вагоны с фильтрацией
	$carriages = CarriageTable::getList([
		'filter' => $filter
	]);

	$result = [];
	foreach ($carriages as $carriage) {
		$result[] = [
			'id' => $carriage['ID'],
			'number' => $carriage['NUMBER'],
			'type' => $carriage['TYPE'],
			'departure_station' => $carriage['DEPARTURE_STATION'],
			'arrival_station' => $carriage['ARRIVAL_STATION'],
			'departure_time' => $carriage['DEPARTURE_TIME'],
			'arrival_time' => $carriage['ARRIVAL_TIME'],
			'status' => $carriage['STATUS']
		];
	}

	echo json_encode(['status' => 'success', 'data' => $result]);
}

// Обработка POST-запроса для добавления нового вагона
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Получаем данные из тела запроса
	$data = json_decode(file_get_contents('php://input'), true);

	// Проверяем, что данные валидны
	if (!empty($data) && isset($data['number'], $data['type'], $data['departure_station'], $data['arrival_station'], $data['departure_time'], $data['arrival_time'], $data['status'])) {
		// Вставка данных в базу
		CarriageTable::add([
			'NUMBER' => $data['number'],
			'TYPE' => $data['type'],
			'DEPARTURE_STATION' => $data['departure_station'],
			'ARRIVAL_STATION' => $data['arrival_station'],
			'DEPARTURE_TIME' => $data['departure_time'],
			'ARRIVAL_TIME' => $data['arrival_time'],
			'STATUS' => $data['status']
		]);

		echo json_encode(['status' => 'success', 'message' => 'Carriage added successfully']);
	} else {
		echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
	}
}

// Если метод не поддерживается
else {
	echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
