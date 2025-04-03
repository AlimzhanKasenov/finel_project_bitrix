<?php

function fake_ai_update_wagons()
{
	$connection = \Bitrix\Main\Application::getConnection();
	$statuses = ['в пути', 'задержан', 'на станции'];
	$types = ['цистерна', 'платформа', 'крытый вагон'];
	$stations = ['Алматы', 'Астана', 'Караганда', 'Шымкент', 'Актобе'];

	// 1. Обновление статусов существующих вагонов
	$wagons = $connection->query("SELECT id FROM wagons")->fetchAll();
	foreach ($wagons as $wagon) {
		$randomStatus = $statuses[array_rand($statuses)];
		$connection->queryExecute("UPDATE wagons SET status = '{$randomStatus}' WHERE id = {$wagon['id']}");
	}

	// 2. Добавление новых вагонов (1-3 за раз)
	$newWagonsCount = rand(1, 3);
	for ($i = 0; $i < $newWagonsCount; $i++) {
		$number = rand(100000, 999999);	
		$type = $types[array_rand($types)];
		$departure = $stations[array_rand($stations)];
		$arrival = $stations[array_rand($stations)];
		while ($arrival === $departure) { // Исключаем одинаковые станции
			$arrival = $stations[array_rand($stations)];
		}

		$departureTime = date('Y-m-d H:i:s', strtotime("+" . rand(0, 12) . " hours"));
		$arrivalTime = date('Y-m-d H:i:s', strtotime($departureTime . " +" . rand(3, 24) . " hours"));
		$status = $statuses[array_rand($statuses)];

		$sql = "INSERT INTO wagons (number, type, departure_station, arrival_station, departure_time, arrival_time, status)
                VALUES ('$number', '$type', '$departure', '$arrival', '$departureTime', '$arrivalTime', '$status')";
		$connection->queryExecute($sql);
	}

	return "fake_ai_update_wagons();"; // Для повторного выполнения агента
}
