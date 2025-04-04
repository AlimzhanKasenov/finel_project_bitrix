<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Компонент OtusGridComponent
 * Отвечает за выборку элементов из инфоблока и вывод названий связанных процедур.
 */
class OtusGridComponent extends CBitrixComponent
{
    /**
     * Основной метод компонента.
     * Выполняет:
     * - Подключение модуля инфоблоков
     * - Получение элементов из инфоблока ID 16
     * - Сбор ID процедур и получение их названий из инфоблока ID 17
     * - Формирование arResult для шаблона компонента
     *
     * @return void
     */
    public function executeComponent()
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            ShowError('Модуль Инфоблоки не подключен');
            return;
        }

        // Получение активных элементов из инфоблока ID 16
        $filter = [
            'IBLOCK_ID' => 16,
            'ACTIVE' => 'Y',
        ];

        $select = ['ID', 'NAME', 'PROPERTY_PROTSEDURY'];
        $res = \CIBlockElement::GetList([], $filter, false, false, $select);

        $this->arResult['ITEMS'] = [];
        $procedureIds = [];

        while ($item = $res->GetNext()) {
            // Собираем ID процедур
            if (is_array($item['PROPERTY_PROTSEDURY_VALUE'])) {
                $item['PROPERTY_PROTSEDURY_VALUES'] = $item['PROPERTY_PROTSEDURY_VALUE'];
                $procedureIds = array_merge($procedureIds, $item['PROPERTY_PROTSEDURY_VALUE']);
            } else {
                $item['PROPERTY_PROTSEDURY_VALUES'] = [$item['PROPERTY_PROTSEDURY_VALUE']];
                $procedureIds[] = $item['PROPERTY_PROTSEDURY_VALUE'];
            }

            $this->arResult['ITEMS'][$item['ID']]['NAME'] = $item['NAME'];
            $this->arResult['ITEMS'][$item['ID']]['PROCEDURES'] = array_merge(
                $this->arResult['ITEMS'][$item['ID']]['PROCEDURES'] ?? [],
                $item['PROPERTY_PROTSEDURY_VALUES']
            );
        }

        // Убираем дубликаты ID процедур
        $procedureIds = array_unique($procedureIds);

        // Получаем названия процедур из инфоблока ID 17
        $procedureNames = [];
        if (!empty($procedureIds)) {
            $procedureRes = \CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => 17, 'ID' => $procedureIds],
                false,
                false,
                ['ID', 'NAME']
            );

            while ($proc = $procedureRes->GetNext()) {
                $procedureNames[$proc['ID']] = $proc['NAME'];
            }
        }

        // Заменяем ID процедур на названия
        foreach ($this->arResult['ITEMS'] as &$item) {
            $item['PROCEDURES'] = array_map(
                fn($id) => $procedureNames[$id] ?? "ID: $id",
                $item['PROCEDURES']
            );
        }

        $this->includeComponentTemplate();
    }
}
