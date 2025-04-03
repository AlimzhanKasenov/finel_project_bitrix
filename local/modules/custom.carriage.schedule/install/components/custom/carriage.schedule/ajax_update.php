<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Custom\CarriageSchedule\CarriageTable;

// Установим заголовок для ответа
header('Content-Type: text/html; charset=UTF-8');

// Функция для обновления статусов вагонов
function updateCarriagesStatus()
{
  if (!\Bitrix\Main\Loader::includeModule("custom.carriage.schedule")) {
    ShowError("Модуль custom.carriage.schedule не установлен.");
    return;
  }

  $carriages = CarriageTable::getAll();
  $statusOptions = ['В пути', 'Задержан', 'На станции'];

  foreach ($carriages as $carriage) {
    // Генерация случайного статуса для вагона
    $newStatus = $statusOptions[array_rand($statusOptions)];

    CarriageTable::update($carriage['ID'], ['STATUS' => $newStatus]);
  }
}

// Пример добавления случайных вагонов каждые 10 секунд (для фейкового ИИ)
function addCarriages()
{
  $number = rand(10000, 99999); // Генерация случайного номера вагона
  $type = ['Цистерна', 'Платформа', 'Крытый вагон'][array_rand(['Цистерна', 'Платформа', 'Крытый вагон'])];
  $departureStation = 'Станция ' . rand(1, 100); // Случайная станция отправления
  $arrivalStation = 'Станция ' . rand(1, 100);  // Случайная станция прибытия
  $departureTime = (new DateTime())->modify('+' . rand(1, 10) . ' minutes')->format('Y-m-d H:i:s');
  $arrivalTime = (new DateTime())->modify('+' . rand(11, 20) . ' minutes')->format('Y-m-d H:i:s');

  $result = CarriageTable::create([
    'NUMBER' => $number,
    'TYPE' => $type,
    'DEPARTURE_STATION' => $departureStation,
    'ARRIVAL_STATION' => $arrivalStation,
    'DEPARTURE_TIME' => $departureTime,
    'ARRIVAL_TIME' => $arrivalTime,
    'STATUS' => "В пути"
  ]);
}

// Обновим статусы вагонов
updateCarriagesStatus();

// Добавить новый вагон
// addCarriages();

// Получаем обновленные данные о вагонах
$carriages = CarriageTable::getAll();

// Формируем таблицу с новыми данными
foreach ($carriages as $carriage) {
  echo "<tr>
            <td>{$carriage['NUMBER']}</td>
            <td>{$carriage['TYPE']}</td>
            <td>{$carriage['DEPARTURE_STATION']}</td>
            <td>{$carriage['ARRIVAL_STATION']}</td>
            <td>{$carriage['DEPARTURE_TIME']}</td>
            <td>{$carriage['ARRIVAL_TIME']}</td>
            <td class='status'>{$carriage['STATUS']}</td>
          </tr>";
}
