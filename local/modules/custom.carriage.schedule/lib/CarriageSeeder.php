<?php

namespace Custom\CarriageSchedule;

use Bitrix\Main\Application;
use DateTime;

class CarriageSeeder
{
	public static function seed()
	{
		// Массив данных для вставки
		$carriages = [
			[
				'NUMBER' => '001',
				'TYPE' => 'цистерна',
				'DEPARTURE_STATION' => 'Москва',
				'ARRIVAL_STATION' => 'Санкт-Петербург',
				'DEPARTURE_TIME' => '2025-01-30 10:00:00',
				'ARRIVAL_TIME' => '2025-01-30 18:00:00',
				'STATUS' => 'в пути'
			],
			[
				'NUMBER' => '002',
				'TYPE' => 'платформа',
				'DEPARTURE_STATION' => 'Воронеж',
				'ARRIVAL_STATION' => 'Калуга',
				'DEPARTURE_TIME' => '2025-01-30 12:00:00',
				'ARRIVAL_TIME' => '2025-01-30 20:00:00',
				'STATUS' => 'на станции'
			]
			// Добавьте больше данных по мере необходимости
		];

		// Используем низкоуровневую вставку данных через Application::getConnection()
		$connection = Application::getConnection();
		$connection->startTransaction();

		try {
			$tableName = \Custom\CarriageSchedule\CarriageTable::getTableName();

			// Проверяем, существует ли таблица
            if ($connection->isTableExists($tableName)) {
				// Вставляем данные
				foreach ($carriages as $carriage) {
					// $data = [
					// 	'NUMBER' => $carriage['NUMBER'],
					// 	'TYPE' => $carriage['TYPE'],
					// 	'DEPARTURE_STATION' => $carriage['DEPARTURE_STATION'],
					// 	'ARRIVAL_STATION' => $carriage['ARRIVAL_STATION'],
					// 	'DEPARTURE_TIME' => new DateTime($carriage['DEPARTURE_TIME']),
					// 	'ARRIVAL_TIME' => new DateTime($carriage['ARRIVAL_TIME']),
					// 	'STATUS' => $carriage['STATUS']
					// ];

					\Custom\CarriageSchedule\CarriageTable::create($carriage);
				}

                $connection->commitTransaction(); // Подтверждаем транзакцию
            } else {
                throw new \Exception("Таблица '$tableName' не существует.");
            }

		} catch (\Exception $e) {
			$connection->rollbackTransaction();
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "CarriageSeeder ошибка" . $e->getMessage(), FILE_APPEND);

			throw $e; // Выбрасываем исключение, если произошла ошибка
		}
	}
}
