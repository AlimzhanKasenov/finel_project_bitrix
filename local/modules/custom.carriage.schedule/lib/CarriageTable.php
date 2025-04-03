<?php

namespace Custom\CarriageSchedule;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\Application;

class CarriageTable extends DataManager
{
	public static function getTableName()
	{
		return 'carriages_schedule';
	}

	public static function getMap()
	{
		return [
			new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new StringField('NUMBER', ['required' => true]),
			new StringField('TYPE', ['required' => true]),
			new StringField('DEPARTURE_STATION', ['required' => true]),
			new StringField('ARRIVAL_STATION', ['required' => true]),
			new StringField('DEPARTURE_TIME', ['required' => true]),
			new StringField('ARRIVAL_TIME', ['required' => true]),
			new StringField('STATUS', ['required' => true])
		];
	}

	public static function create($data)
	{
		// Дополнительная логика перед вставкой данных, например, валидация
		if (empty($data['NUMBER'])) {
			throw new \Exception("Поле 'NUMBER' обязательно для заполнения.");
		}

		if (empty($data['DEPARTURE_TIME']) || empty($data['ARRIVAL_TIME'])) {
			throw new \Exception("Время отправления и прибытия обязательны.");
		}

		// Вставка данных
		$connection = Application::getConnection();
		$connection->startTransaction();
		$sql = "INSERT INTO " . self::getTableName() . " (NUMBER, TYPE, DEPARTURE_STATION, ARRIVAL_STATION, DEPARTURE_TIME, ARRIVAL_TIME, STATUS) 
                VALUES ('" . $data['NUMBER'] . "', 
                        '" . $data['TYPE'] . "', 
                        '" . $data['DEPARTURE_STATION'] . "', 
                        '" . $data['ARRIVAL_STATION'] . "', 
                        '" . $data['DEPARTURE_TIME'] . "', 
                        '" . $data['ARRIVAL_TIME'] . "', 
                        '" . $data['STATUS'] . "')";

		try {
			$connection->query($sql);
			$connection->commitTransaction();  // Подтверждаем транзакцию
		} catch (\Exception $e) {
			$connection->rollbackTransaction();  // Откатываем транзакцию при ошибке
			throw $e;  // Пробрасываем ошибку дальше
		}
	}

	public static function getAll()
	{
		$connection = Application::getConnection();
		$sql = "SELECT * FROM " . self::getTableName() . " 
			ORDER BY ID ASC";

		// Выполняем запрос и получаем результат
		$result = $connection->query($sql);

		// $carriages = $result->fetch();
		// Преобразуем результат в массив
		$carriages = [];
		while ($row = $result->fetch()) {
			$carriages[] = $row;
		}

		// Логирование
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "CarriageTable getall \n" . print_r($carriages, true), FILE_APPEND);

		return $carriages;
	}

	public static function getById($id)
	{
		return self::getList([
			'select' => ['ID', 'NUMBER', 'TYPE', 'DEPARTURE_STATION', 'ARRIVAL_STATION', 'DEPARTURE_TIME', 'ARRIVAL_TIME', 'STATUS'],
			'filter' => ['ID' => $id]
		])->fetch();
	}

	public static function updateStatus($id, $status, $departureTime, $arrivalTime)
	{
		$carriage = self::getById($id);  // Получаем данные по ID
		if ($carriage) {
			self::update($id, [
				'STATUS' => $status,
				'DEPARTURE_TIME' => $departureTime,
				'ARRIVAL_TIME' => $arrivalTime
			]);  // Передаем обновленные данные
		}
	}
}
