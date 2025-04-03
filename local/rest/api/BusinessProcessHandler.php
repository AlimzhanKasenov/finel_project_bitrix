<?php

namespace Local\Rest\Api;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Rest\RestException;
use CIBlockElement;

Loader::includeModule("iblock");

/**
 * Класс для регистрации собственных REST-методов (my.bp.*)
 * и выполнения CRUD-операций с инфоблоком ID=29
 */
class BusinessProcessHandler
{
    private static int $iblockId = 29;

    /**
     * Регистрация REST-методов и Scope
     * @return array
     */
    public static function onRestServiceBuildDescription(): array
    {
        // scope => 'my.bp' позволяет вебхуку/приложению выдать права my.bp
        return [
            'my.bp' => [
                'scope' => 'my.bp',
                'my.bp.add'    => [__CLASS__, 'addProcess'],
                'my.bp.get'    => [__CLASS__, 'getProcess'],
                'my.bp.update' => [__CLASS__, 'updateProcess'],
                'my.bp.delete' => [__CLASS__, 'deleteProcess'],
            ],
        ];
    }

    /**
     * Создание элемента в инфоблоке (Create)
     * @param array $params
     * @param mixed $navi
     * @param \CRestServer $server
     * @return array
     * @throws RestException
     */
    public static function addProcess(array $params, $navi, \CRestServer $server): array
    {
        if (empty($params['NAME']) || empty($params['DESCRIPTION']) || empty($params['STATUS']) || empty($params['USER_ID'])) {
            throw new RestException("Необходимо передать NAME, DESCRIPTION, STATUS, USER_ID");
        }

        $el = new CIBlockElement();
        $fields = [
            "IBLOCK_ID" => self::$iblockId,
            "NAME"      => $params['NAME'],
            "PROPERTY_VALUES" => [
                "88" => $params['DESCRIPTION'],        // Код/ID свойства: Описание
                "89" => $params['STATUS'],             // Код/ID свойства: Статус
                "90" => [$params['USER_ID']],          // Код/ID свойства: Автор (привязка к сотруднику)
                "91" => date("d.m.Y H:i:s"),
            ]
        ];

        $id = $el->Add($fields);
        if ($id) {
            return ["status" => "success", "ID" => $id];
        }

        throw new RestException("Ошибка при создании элемента: " . $el->LAST_ERROR);
    }

    /**
     * Получение элемента (Read)
     * @param array $params
     * @param mixed $navi
     * @param \CRestServer $server
     * @return array
     * @throws RestException
     */
    public static function getProcess(array $params, $navi, \CRestServer $server): array
    {
        if (empty($params['ID'])) {
            throw new RestException("Необходимо передать ID");
        }

        $id = (int)$params['ID'];
        $res = CIBlockElement::GetList(
            [],
            ["IBLOCK_ID" => self::$iblockId, "ID" => $id],
            false,
            false,
            ["ID", "NAME", "PROPERTY_88", "PROPERTY_89", "PROPERTY_90", "PROPERTY_91"]
        );

        if ($element = $res->Fetch()) {
            return ["status" => "success", "data" => $element];
        }

        throw new RestException("Элемент с ID={$id} не найден");
    }

    /**
     * Обновление элемента (Update)
     * @param array $params
     * @param mixed $navi
     * @param \CRestServer $server
     * @return array
     * @throws RestException
     */
    public static function updateProcess(array $params, $navi, \CRestServer $server): array
    {
        if (empty($params['ID']) || empty($params['NAME']) || empty($params['DESCRIPTION']) || empty($params['STATUS'])) {
            throw new RestException("Необходимо передать ID, NAME, DESCRIPTION, STATUS");
        }

        $id = (int)$params['ID'];
        $el = new CIBlockElement();
        $fields = [
            "NAME" => $params['NAME'],
            "PROPERTY_VALUES" => [
                "88" => $params['DESCRIPTION'],    // Обновляем поле Описание
                "89" => $params['STATUS'],         // Обновляем поле Статус
            ]
        ];

        if ($el->Update($id, $fields)) {
            return ["status" => "success", "message" => "Элемент обновлён"];
        }

        throw new RestException("Ошибка при обновлении: " . $el->LAST_ERROR);
    }

    /**
     * Удаление элемента (Delete)
     * @param array $params
     * @param mixed $navi
     * @param \CRestServer $server
     * @return array
     * @throws RestException
     */
    public static function deleteProcess(array $params, $navi, \CRestServer $server): array
    {
        if (empty($params['ID'])) {
            throw new RestException("Необходимо передать ID");
        }

        $id = (int)$params['ID'];
        if (\CIBlockElement::Delete($id)) {
            return ["status" => "success", "message" => "Элемент удалён"];
        }

        throw new RestException("Ошибка при удалении элемента");
    }
}
