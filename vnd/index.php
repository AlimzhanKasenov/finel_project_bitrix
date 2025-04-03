<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $APPLICATION;
$APPLICATION->IncludeComponent(
    'otus.homework:otus.grid',
    '.default',
    ['DEAL_ID' => 1]
);
