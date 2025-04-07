<?php

namespace Custom\Grid;

use Bitrix\Main\Loader;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Crm\Service\Container;
use Bitrix\Main\Type\DateTime;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Компонент custom:grid
 *
 * Отображает список автомобилей, связанных с контактом,
 * и по клику загружает последние 5 сделок, связанных с автомобилем.
 */
class Grid extends \CBitrixComponent implements Controllerable
{
    /**
     * Объявление ajax-действий компонента.
     *
     * @return array
     */
    public function configureActions()
    {
        return [
            'getDealsByCar' => [
                'prefilters' => []
            ]
        ];
    }

    /**
     * Ajax-действие: получить последние 5 сделок, связанных с автомобилем.
     *
     * @param int $carId ID смарт-процесса (автомобиля)
     * @return array Массив сделок с полями ID, DATE, WORK_TYPE, PRICE
     * @throws \Bitrix\Main\SystemException
     */
    public function getDealsByCarAction($carId)
    {
        if (!Loader::includeModule('crm')) {
            throw new \Bitrix\Main\SystemException('CRM module not loaded');
        }

        $carId = (int)$carId;
        $result = [];

        // Получаем фабрику сделок (тип 2)
        $factory = Container::getInstance()->getFactory(2);
        if (!$factory) {
            throw new \Bitrix\Main\SystemException('Factory for deals not found');
        }

        // Получаем сделки, связанные с автомобилем
        $items = $factory->getItems([
            'filter' => ['=PARENT_ID_1040' => $carId],
            'select' => ['ID', 'DATE_CREATE', 'UF_CRM_1743663540837', 'OPPORTUNITY'],
            'order' => ['ID' => 'DESC'],
            'limit' => 5
        ]);

        foreach ($items as $item) {
            $date = $item->get('DATE_CREATE');
            $dateFormatted = ($date instanceof DateTime)
                ? $date->format("d.m.Y H:i:s")
                : (string)$date;

            $result[] = [
                'ID' => $item->getId(),
                'DATE' => $dateFormatted,
                'WORK_TYPE' => $item->get('UF_CRM_1743663540837') ?? '',
                'PRICE' => $item->get('OPPORTUNITY') ?? '',
            ];
        }

        return $result;
    }

    /**
     * Основной метод компонента — загружает список автомобилей контакта.
     */
    public function executeComponent()
    {
        if (!Loader::includeModule('crm')) {
            ShowError("Модуль CRM не подключен");
            return;
        }

        $contactId = (int)($this->arParams['CONTACT_ID'] ?? $_GET['contactId'] ?? 0);
        if (!$contactId) {
            ShowError("Не передан ID контакта");
            return;
        }

        $factory = Container::getInstance()->getFactory(1040); // Фабрика смарт-процесса "Автомобили"
        if (!$factory) {
            ShowError("Смарт-процесс не найден");
            return;
        }

        // Получаем автомобили, связанные с контактом
        $items = $factory->getItems([
            'filter' => ['=CONTACT_ID' => $contactId],
            'select' => [
                'ID',
                'TITLE',
                'UF_CRM_4_1742986554', // Модель
                'UF_CRM_4_1742986643', // Год
                'UF_CRM_4_1742986678', // Цвет
                'UF_CRM_4_1742986687', // Пробег
                'CONTACT_ID'
            ],
            'order' => ['ID' => 'DESC']
        ]);

        $this->arResult['CARS'] = [];

        foreach ($items as $item) {
            $this->arResult['CARS'][] = [
                'ID' => $item->getId(),
                'TITLE' => $item->getTitle(),
                'MODEL' => $item->get('UF_CRM_4_1742986554'),
                'YEAR' => $item->get('UF_CRM_4_1742986643'),
                'COLOR' => $item->get('UF_CRM_4_1742986678'),
                'MILEAGE' => $item->get('UF_CRM_4_1742986687'),
            ];
        }

        $this->includeComponentTemplate();
    }
}
