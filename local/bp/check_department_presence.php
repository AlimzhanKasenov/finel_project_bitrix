<?php

use Bitrix\Main\Loader;

/**
 * Проверка отсутствия всех подчинённых в отделе, кроме начальника.
 *
 * @param int $departmentId ID отдела.
 * @param int $structureIblockId ID инфоблока структуры компании.
 * @param int $absenceIblockId ID инфоблока отсутствий.
 * @return int 1 — если хотя бы один сотрудник присутствует, 0 — если все отсутствуют.
 */
function checkDepartmentPresence(int $departmentId, int $structureIblockId, int $absenceIblockId): int
{
    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

    Loader::includeModule("main");
    Loader::includeModule("iblock");

    $now = time();
    $headId = null;
    $sotrud_na_meste = 0;

    // Получение начальника отдела
    $sectionRes = \CIBlockSection::GetList(
        [],
        ['IBLOCK_ID' => $structureIblockId, 'ID' => $departmentId],
        false,
        ['ID', 'NAME', 'UF_HEAD']
    );

    if ($section = $sectionRes->Fetch()) {
        $headId = (int)$section['UF_HEAD'];
    }

    // Получение пользователей отдела
    $rsUsers = \CUser::GetList(
        ($by = "last_name"),
        ($order = "asc"),
        ["ACTIVE" => "Y", "UF_DEPARTMENT" => $departmentId],
        ["SELECT" => ["ID", "NAME", "LAST_NAME", "SECOND_NAME"]]
    );

    while ($arUser = $rsUsers->Fetch()) {
        $userId = (int)$arUser['ID'];

        // Начальника не проверяем
        if ($userId !== $headId) {
            $absence = \CIBlockElement::GetList(
                [],
                [
                    "IBLOCK_ID" => $absenceIblockId,
                    "PROPERTY_USER" => $userId,
                    "<=DATE_ACTIVE_FROM" => ConvertTimeStamp($now, "FULL"),
                    ">=DATE_ACTIVE_TO" => ConvertTimeStamp($now, "FULL"),
                    "ACTIVE" => "Y"
                ],
                false,
                false,
                ["ID"]
            )->Fetch();

            if (!$absence) {
                $sotrud_na_meste = 1;
                break; // достаточно одного присутствующего
            }
        }
    }

    return $sotrud_na_meste;
}
