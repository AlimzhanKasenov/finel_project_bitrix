<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

global $APPLICATION;

header('Content-Type: text/html; charset=UTF-8');

$APPLICATION->IncludeComponent(
	'custom:carriage.schedule',
	'.default',
	['DEAL_ID' => 1]
);
