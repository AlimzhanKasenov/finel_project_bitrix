<?php
namespace CustomProperty;

use Bitrix\Main\Loader;

class ProcedureSelector
{
    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => 'E', // Тип свойства (привязка к элементу)
            'USER_TYPE' => 'procedure_selector', // Уникальный идентификатор типа
            'DESCRIPTION' => 'Запись на процедуру (привязка к элементам)',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'GetPublicEditHTML' => [__CLASS__, 'GetPublicEditHTML'],
        ];
    }
    /**
     * Админка: редактирование в форме элемента
     */
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        Loader::includeModule('iblock');

        // Текущее значение (ID связанного элемента)
        $selectedElementId = (int)$value['VALUE'];

        // Готовим select
        $html = '<select name="' . $strHTMLControlName["VALUE"] . '">';
        $html .= '<option value="">(не выбрано)</option>';

        // Например, выбираем элементы из ИБ "Процедуры" (IBLOCK_ID = 2)
        $rs = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => 17, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'NAME']
        );
        while ($arElem = $rs->Fetch()) {
            $selected = ($arElem['ID'] == $selectedElementId) ? 'selected' : '';
            $html .= '<option value="'.$arElem['ID'].'" '.$selected.'>'.htmlspecialchars($arElem['NAME']).'</option>';
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Админка: вывод в списке
     */
    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $elementId = (int)$value['VALUE'];
        if (!$elementId) {
            return '&nbsp;';
        }

        // Получаем данные элемента инфоблока
        Loader::includeModule('iblock');
        $arElem = \CIBlockElement::GetList(
            [],
            ['ID' => $elementId],
            false,
            false,
            ['ID', 'NAME']
        )->Fetch();

        if ($arElem) {
            // Генерируем ссылку для открытия модального окна
            return '<a href="#" class="open-modal" data-id="' . $arElem['ID'] . '">' . htmlspecialchars($arElem['NAME']) . '</a>';
        }

        return '<span style="color: gray;">Нет данных</span>';
    }


    /**
     * Публичная часть: вывод (view)
     */
    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $elementId = (int)$value['VALUE'];
        if (!$elementId) {
            return '<span style="color: gray;">Нет данных</span>';
        }

        Loader::includeModule('iblock');
        $arElem = \CIBlockElement::GetList(
            [],
            ['ID' => $elementId],
            false,
            false,
            ['ID', 'NAME']
        )->Fetch();

        if ($arElem) {
            // Создаём ссылку для открытия модального окна
            return '<a href="#" class="open-modal" data-id="' . $arElem['ID'] . '">' . htmlspecialchars($arElem['NAME']) . '</a>';
        }

        return '<span style="color: gray;">Нет данных</span>';
    }


    /**
     * Публичная часть: редактирование (edit)
     */
    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        Loader::includeModule('iblock');

        $selectedElementId = (int)$value['VALUE'];
        $html = '<select name="' . $strHTMLControlName["VALUE"] . '">';
        $html .= '<option value="">(не выбрано)</option>';

        // Заполняем список элементов (например, из инфоблока "Процедуры")
        $rs = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => 17, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'NAME']
        );

        while ($arElem = $rs->Fetch()) {
            $selected = ($arElem['ID'] == $selectedElementId) ? 'selected' : '';
            $html .= '<option value="'.$arElem['ID'].'" '.$selected.'>'.htmlspecialchars($arElem['NAME']).'</option>';
        }

        $html .= '</select>';
        return $html;
    }

}
