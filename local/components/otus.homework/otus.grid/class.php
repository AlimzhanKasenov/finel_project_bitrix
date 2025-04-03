<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$logFile = $_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt';
file_put_contents($logFile, "=== [class.php] Старт ===\n", FILE_APPEND);

class OtusGridComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt';
        file_put_contents($logFile, "=== [class.php] Старт ===\n", FILE_APPEND);

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            file_put_contents($logFile, "[class.php] Ошибка: модуль Инфоблоки не подключен\n", FILE_APPEND);
            ShowError('Модуль Инфоблоки не подключен');
            return;
        }

        // Настройка фильтра для выборки данных из инфоблока
        $filter = [
            'IBLOCK_ID' => 16, // Убедитесь, что это правильный ID инфоблока
            'ACTIVE' => 'Y',
        ];

        $select = ['ID', 'NAME', 'PROPERTY_PROTSEDURY'];

        $res = \CIBlockElement::GetList([], $filter, false, false, $select);

        $this->arResult['ITEMS'] = [];
        $procedureIds = []; // Для сбора всех ID процедур

        while ($item = $res->GetNext()) {
            // Собираем ID процедур в массив
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

        // Убираем дублирующиеся ID процедур
        $procedureIds = array_unique($procedureIds);

        // Получаем названия процедур из связанного инфоблока (ID 17)
        $procedureNames = [];
        if (!empty($procedureIds)) {
            $procedureRes = \CIBlockElement::GetList([], ['IBLOCK_ID' => 17, 'ID' => $procedureIds], false, false, ['ID', 'NAME']);
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

        file_put_contents($logFile, "[class.php] Итоговый результат: " . print_r($this->arResult['ITEMS'], true) . "\n", FILE_APPEND);

        $this->includeComponentTemplate();
        file_put_contents($logFile, "=== [class.php] Конец ===\n", FILE_APPEND);
    }
}
