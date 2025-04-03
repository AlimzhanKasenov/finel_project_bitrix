<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Query\Query;
use Custom\CarriageSchedule\CarriageTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CarriageScheduleComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		if (!Loader::includeModule("custom.carriage.schedule")) {
			ShowError("Модуль custom.carriage.schedule не установлен.");
			return;
		}

		$this->arResult['CARRIAGES'] = CarriageTable::getAll();
		$this->includeComponentTemplate();
	}

	private function getCarriages()
	{
		return CarriageTable::getAll();
	}
}
