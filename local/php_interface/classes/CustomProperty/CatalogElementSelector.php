<?php
namespace CustomProperty;

use Bitrix\Main\Loader;

/**
 * Кастомный тип свойства "Элемент каталога (привязка к товару)".
 * Отображает выпадающий список элементов из заданного раздела каталога.
 */
class CatalogElementSelector
{
    /**
     * Описание пользовательского типа свойства для регистрации в системе.
     *
     * @return array
     */
    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => 'E',
            'USER_TYPE' => 'catalog_element_selector',
            'DESCRIPTION' => 'Элемент каталога (привязка к товару)',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'GetPublicEditHTML' => [__CLASS__, 'GetPublicEditHTML'],
        ];
    }

    /**
     * Получает HTML-код select-элемента с элементами каталога из нужного раздела.
     *
     * @param int $selectedElementId ID выбранного элемента
     * @return string HTML <select> с товарами
     */
    protected static function getCatalogElements($selectedElementId = 0)
    {
        Loader::includeModule('iblock');

        $iblockId = 14;      // ID каталога
        $sectionId = 13;     // ID раздела "Товары"

        $html = '<select name="%s">';
        $html .= '<option value="">(не выбрано)</option>';

        $rs = \CIBlockElement::GetList(
            ['NAME' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                'SECTION_ID' => $sectionId,
                'INCLUDE_SUBSECTIONS' => 'Y'
            ],
            false,
            false,
            ['ID', 'NAME']
        );

        while ($arElem = $rs->Fetch()) {
            $selected = ($arElem['ID'] == $selectedElementId) ? 'selected' : '';
            $html .= '<option value="' . $arElem['ID'] . '" ' . $selected . '>' . htmlspecialchars($arElem['NAME']) . '</option>';
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Отображение поля в форме редактирования в админке.
     *
     * @param array $arProperty Параметры свойства
     * @param array $value Текущее значение
     * @param array $strHTMLControlName Управляющие имена
     * @return string HTML кода поля
     */
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $selectedId = (int)$value['VALUE'];
        return sprintf(self::getCatalogElements($selectedId), $strHTMLControlName["VALUE"]);
    }

    /**
     * Отображение значения в списке элементов (админка).
     *
     * @param array $arProperty Параметры свойства
     * @param array $value Значение свойства
     * @param array $strHTMLControlName Управляющие имена
     * @return string HTML
     */
    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $elementId = (int)$value['VALUE'];
        if (!$elementId) {
            return '&nbsp;';
        }

        Loader::includeModule('iblock');
        $arElem = \CIBlockElement::GetByID($elementId)->Fetch();

        if ($arElem) {
            return '<a href="#" class="open-modal" data-id="' . $arElem['ID'] . '">' . htmlspecialchars($arElem['NAME']) . '</a>';
        }

        return '<span style="color: gray;">Нет данных</span>';
    }

    /**
     * Публичная часть: отображение значения (view).
     *
     * @param array $arProperty Параметры свойства
     * @param array $value Значение свойства
     * @param array $strHTMLControlName Управляющие имена
     * @return string HTML
     */
    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return self::GetAdminListViewHTML($arProperty, $value, $strHTMLControlName);
    }

    /**
     * Публичная часть: редактирование значения (edit).
     *
     * @param array $arProperty Параметры свойства
     * @param array $value Значение свойства
     * @param array $strHTMLControlName Управляющие имена
     * @return string HTML
     */
    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        $selectedId = (int)$value['VALUE'];
        return sprintf(self::getCatalogElements($selectedId), $strHTMLControlName["VALUE"]);
    }
}
