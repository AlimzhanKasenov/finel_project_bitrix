<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Crm\Relation\EntityRelationTable;
use Bitrix\Crm\Service\Container;

if (!Loader::includeModule('crm'))
{
    die('CRM модуль не подключен');
}

$dealId = 13; // ID вашей сделки
$dealTypeId = \CCrmOwnerType::Deal; // используем стандартные константы

// Получаем тип смарт-процесса для автомобилей (1040)
$factory = Container::getInstance()->getFactory(1040);
if (!$factory) {
    die('Фабрика смарт-процесса 1040 не найдена');
}
$carTypeId = $factory->getEntityTypeId();

// Ищем связи в ОБЕ стороны
$links = EntityRelationTable::getList([
    'filter' => [
        [
            'LOGIC' => 'OR',
            [
                '=SRC_ENTITY_TYPE_ID' => $dealTypeId,
                '=SRC_ENTITY_ID' => $dealId,
                '=DST_ENTITY_TYPE_ID' => $carTypeId,
            ],
            [
                '=DST_ENTITY_TYPE_ID' => $dealTypeId,
                '=DST_ENTITY_ID' => $dealId,
                '=SRC_ENTITY_TYPE_ID' => $carTypeId,
            ],
        ]
    ]
])->fetchAll();

// Выводим результат
echo '<pre>';
if (!$links) {
    echo "❌ У сделки {$dealId} нет связанных автомобилей.";
} else {
    echo "✅ Найдено ".count($links)." связь(и):\n";
    foreach ($links as $rel) {
        $carId = $rel['SRC_ENTITY_TYPE_ID'] == $carTypeId ? $rel['SRC_ENTITY_ID'] : $rel['DST_ENTITY_ID'];
        $car = $factory->getItem($carId);
        if ($car) {
            echo "🚗 Автомобиль [{$carId}] «".$car->getTitle()."»\n";
        } else {
            echo "⚠️ Автомобиль с ID {$carId} не найден (возможно удалён)\n";
        }
    }
}
echo '</pre>';
