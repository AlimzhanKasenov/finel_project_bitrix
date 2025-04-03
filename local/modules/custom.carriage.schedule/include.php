<?php
Bitrix\Main\Loader::registerAutoLoadClasses(
	"custom.carriage.schedule",
	[
		"Custom\\CarriageSchedule\\CarriageTable" => "lib/CarriageTable.php",
		"Custom\\CarriageSchedule\\Api" => "lib/Api.php",
		"Custom\\CarriageSchedule\\Handlers" => "lib/Handlers.php",
		"Custom\\CarriageSchedule\\CarriageSeeder" => "lib/CarriageSeeder.php",
	]
);