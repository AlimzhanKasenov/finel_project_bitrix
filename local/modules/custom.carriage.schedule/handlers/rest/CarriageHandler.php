<?php

namespace Custom\CarriageSchedule\Handlers;

use Custom\CarriageSchedule\Api;
use Bitrix\Main\Engine\Controller;
use Custom\CarriageSchedule\CarriageTable; // Убедитесь, что этот класс подключен

class CarriageHandler extends Controller
{
	public function configureActions()
	{
		return [
			'getCarriages' => [
				'class' => static::class,
				'method' => 'getCarriagesAction',
				'parameters' => []
			],
			'addCarriage' => [
				'class' => static::class,
				'method' => 'addCarriageAction',
				'parameters' => ['data']
			]
		];
	}

	public function getCarriagesAction()
	{
		// Получение фильтров из GET-запроса
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
			'filter' => $filter,
		])->fetchAll(); // Используем fetchAll() для получения всех записей

		$result = [];
		foreach ($carriages as $carriage) {
			$result[] = [
				'id' => $carriage['ID'],
				'number' => $carriage['NUMBER'],
				'type' => $carriage['TYPE'],
				'departure_station' => $carriage['DEPARTURE_STATION'],
				'arrival_station' => $carriage['ARRIVAL_STATION'],
				'departure_time' => $carriage['DEPARTURE_TIME']->toString(), // Преобразуем DateTime в строку
				'arrival_time' => $carriage['ARRIVAL_TIME']->toString(), // Преобразуем DateTime в строку
				'status' => $carriage['STATUS'],
			];
		}

		// Возвращаем результат в формате JSON
		return [
			'status' => 'success',
			'data' => $result
		];
	}

	public function addCarriageAction($data)
	{
		// Пример добавления нового вагона через API
		Api::addCarriage($data);

		// Возвращаем сообщение об успешном добавлении
		return [
			'status' => 'success',
			'message' => 'Carriage added successfully'
		];
	}
}
