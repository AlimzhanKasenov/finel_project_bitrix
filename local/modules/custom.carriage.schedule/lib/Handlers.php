<?php

namespace Custom\CarriageSchedule;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Handlers
{
	public static function updateTabs(Event $event): EventResult
	{
		// return new EventResult(EventResult::SUCCESS, ['tabs' => 'asd']); 
		$logFile = $_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt';
		// file_put_contents($logFile, "=== Старт обработки события updateTabs custom.carriage.schedule ===\n", FILE_APPEND);

		$tabs = $event->getParameter('tabs');
		$entityTypeId = $event->getParameter('entityTypeID');

		file_put_contents($logFile, "entityTypeId: $entityTypeId\n", FILE_APPEND);

		if ($entityTypeId !== \CCrmOwnerType::Deal) {
			file_put_contents($logFile, "Это не сделка. Обработчик завершил работу.\n", FILE_APPEND);
			return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
		}

		$dealId = (int)$event->getParameter('entityID');
		file_put_contents($logFile, "ID сделки: $dealId\n", FILE_APPEND);

		$tabs[] = [
			'id' => 'carriages',
			'name' => 'Расписание вагонов',
			'loader' => [
				'serviceUrl' => '/local/components/custom/carriage.schedule/lazyload.ajax.php'
					. '?dealId=' . $dealId
					. '&site=' . \SITE_ID
					. '&' . bitrix_sessid_get(),
				'componentData' => [
					'templates' => '.default',
					'params' => [
						'DEAL_ID' => $dealId,
					],
				],
			],
		];

		// Записываем краткую информацию о добавленной вкладке
		file_put_contents($logFile, "Добавлена вкладка: ID - {$tabs[count($tabs) - 1]['id']}, Name - {$tabs[count($tabs) - 1]['name']}\n", FILE_APPEND);

		return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
	}
}
