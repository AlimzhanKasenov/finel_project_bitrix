<?php
require_once __DIR__ . '/functions.php';

// 1) Получаем "сырые" данные
$raw = file_get_contents('php://input');
logData('RAW DATA', $raw);

// 2) Разбираем URL-кодированные данные
parse_str($raw, $parsedData);
logData('PARSED DATA', $parsedData);

// 3) Получаем название события
$event = $parsedData['event'] ?? '';
logData('handler.php -> Событие', $event);

// 4) Игнорируем служебные вызовы Bitrix24
if (empty($event)) {
    logData("⚙️ Служебный вызов (без event), игнорируем", $parsedData);
    echo json_encode(['status' => 'ok', 'message' => 'No event param']);
    return;
}

// 5) Если это ONCRMACTIVITYADD
if ($event === 'ONCRMACTIVITYADD') {
    $activityId = $parsedData['data']['FIELDS']['ID'] ?? null;

    if (!$activityId) {
        logData("⚠️ Нет ID активности", $parsedData);
        echo json_encode(['status' => 'error', 'message' => 'Нет ID активности']);
        return;
    }

    // 6) Получаем данные активности
    $activityData = callRest('crm.activity.get', ['id' => $activityId]);
    logData("📩 Полученные данные активности", $activityData);

    // Важно: преобразуем строку в число
    $ownerTypeId = isset($activityData['result']['OWNER_TYPE_ID'])
        ? (int)$activityData['result']['OWNER_TYPE_ID']
        : 0;

    $ownerId = isset($activityData['result']['OWNER_ID'])
        ? (int)$activityData['result']['OWNER_ID']
        : 0;

    if (!$ownerTypeId || !$ownerId) {
        logData("⚠️ Ошибка: нет владельца активности", $activityData);
        echo json_encode(['status' => 'error', 'message' => 'Нет владельца активности']);
        return;
    }

    // 7) Если это контакт (OWNER_TYPE_ID = 3) — обновляем сразу
    if ($ownerTypeId === 3) {
        updateLastCommunication($ownerId);
        echo json_encode(['status' => 'success', 'message' => 'Обновлено для контакта']);
        return;
    }

    // 8) Если это сделка (OWNER_TYPE_ID = 2), получаем контакт, связанный с этой сделкой
    if ($ownerTypeId === 2) {
        $dealData = callRest('crm.deal.get', ['id' => $ownerId]);
        logData("📜 Данные сделки", $dealData);

        // Для одной сделки может быть несколько контактов:
        // - Если API возвращает поле CONTACT_ID (один контакт)
        // - Или массив CONTACTS[] (несколько контактов)
        // Ниже – самый простой случай, когда сделка имеет поле CONTACT_ID:
        $contactId = $dealData['result']['CONTACT_ID'] ?? null;

        // Если нет CONTACT_ID, попробуем взять из CONTACTS, если оно есть
        if (!$contactId && isset($dealData['result']['CONTACTS']) && is_array($dealData['result']['CONTACTS'])) {
            // Берём первый контакт в массиве
            $contactId = $dealData['result']['CONTACTS'][0]['CONTACT_ID'] ?? null;
        }

        if ($contactId) {
            updateLastCommunication($contactId);
            echo json_encode(['status' => 'success', 'message' => 'Обновлено для контакта через сделку']);
            return;
        } else {
            logData("⚠️ Сделка не содержит контакт", $dealData);
            echo json_encode(['status' => 'error', 'message' => 'Сделка без контакта']);
            return;
        }
    }

    // 9) Если активность связана не с контактом и не со сделкой, просто логируем
    logData("⚠️ Не связана с контактом или сделкой", $activityData);
    echo json_encode(['status' => 'error', 'message' => 'Не связана с контактом или сделкой']);
}
