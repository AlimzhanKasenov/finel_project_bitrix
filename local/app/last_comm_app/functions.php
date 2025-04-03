<?php
define('WEBHOOK_URL', 'https://co45937.tw1.ru/rest/1/jt7cu0zvractjbmr/');

/**
 * Логирование данных в log.txt
 */
function logData($message, $data=[])
{
    $line = date('Y-m-d H:i:s')." - $message: ".print_r($data,true)."\n";
    file_put_contents(__DIR__.'/log.txt', $line, FILE_APPEND);
}

/**
 * Универсальный REST-запрос в Bitrix24
 */
function callRest($method, $params=[])
{
    $url = WEBHOOK_URL . $method . '.json';
    $query = http_build_query($params);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $query,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($result, true);
    if (isset($decoded['error'])) {
        logData("❌ Ошибка API ($method)", $decoded);
    }
    return $decoded;
}

/**
 * Обновление поля последней коммуникации у контакта
 */
function updateLastCommunication($contactId)
{
    $now = date('Y-m-d H:i:s');
    $resp = callRest('crm.contact.update', [
        'id' => $contactId,
        'fields' => [
            'UF_CRM_1738909589' => $now
        ]
    ]);

    logData("✅ crm.contact.update -> $contactId", $resp);
}
