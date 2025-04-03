<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Выбор валюты");

// Подключаем компонент
$APPLICATION->IncludeComponent(
	"custom:currency.selector", 
	".default", 
	array(
		"CURRENCY" => "UAH",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
