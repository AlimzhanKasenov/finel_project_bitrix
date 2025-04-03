<?php

namespace Custom\CarriageSchedule;

use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;

class Carriage
{
	public static function getAll()
	{
		$connection = Application::getConnection();
		$result = $connection->query("SELECT * FROM carriages_schedule");

		$wagons = [];
		while ($wagon = $result->fetch()) {
			$wagons[] = $wagon;
		}

		return $wagons;
	}

	public static function getWagonById($id)
	{
		$connection = Application::getConnection();
		$result = $connection->query("SELECT * FROM carriages_schedule WHERE id = " . (int)$id);

		return $result->fetch();
	}

	public static function updateWagonStatus($id, $status, $departureTime, $arrivalTime)
	{
		$connection = Application::getConnection();
		$connection->query("UPDATE carriages_schedule SET status = '$status', departure_time = '$departureTime', arrival_time = '$arrivalTime' WHERE id = " . (int)$id);
	}
}
