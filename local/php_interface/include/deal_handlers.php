<?php
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Crm\Service\Container;

/**
 * Класс DealHandlers
 * Проверяет, что по одному автомобилю (смарт-процесс ID 1040)
 * нельзя создать более одной открытой сделки.
 * В случае блокировки — уведомляет ответственных за незакрытые сделки.
 */
class DealHandlers
{
    public const CAR_FIELD_CODE = 'PARENT_ID_1040';
    public const CAR_SMART_ID   = 1040;
    private const LOG           = '/local/logs/deal_debug.log';

    /**
     * Основной обработчик: перехват создания/обновления сделки.
     *
     * @param array $fields
     * @return bool
     */
    public static function onBefore(array &$fields): bool
    {
        if (!Loader::includeModule('crm')) {
            return true;
        }

        $dealId = (int)($fields['ID'] ?? 0);
        $rawCar = (string)($fields[self::CAR_FIELD_CODE] ?? '');

        self::log("START  dealID={$dealId}  rawCar=\"{$rawCar}\"");

        // Поддержка короткого ID: "2" → "D1040_2"
        if (preg_match('#^\d+$#', $rawCar)) {
            $rawCar = 'D' . self::CAR_SMART_ID . '_' . $rawCar;
            self::log("auto‑fix rawCar => {$rawCar}");
        }

        // Проверка формата
        if (!preg_match('#^D' . self::CAR_SMART_ID . '_(\d+)$#', $rawCar, $m)) {
            return self::block('Автомобиль не указан или имеет неверный формат.', $fields);
        }

        $carId = (int)$m[1];
        $carName = "ID $carId";

        // Название автомобиля
        try {
            $factory = Container::getInstance()->getFactory(self::CAR_SMART_ID);
            if ($factory) {
                $item = $factory->getItem($carId);
                if ($item) {
                    $carName = '"' . $item->getTitle() . '"';
                    self::log("title = {$carName}");
                }
            }
        } catch (\Throwable $e) {
            self::log("Ошибка получения названия авто: " . $e->getMessage());
        }

        // Получение незакрытых сделок по авто
        $openDeals = DealTable::getList([
            'filter' => [
                ['=' . self::CAR_FIELD_CODE => [$rawCar, $carId, (string)$carId]],
                'CLOSED' => 'N',
                ['!ID'   => $dealId],
            ],
            'select' => ['ID', 'ASSIGNED_BY_ID']
        ])->fetchAll();

        if ($openDeals) {
            $byUser = [];

            foreach ($openDeals as $deal) {
                $userId = (int)$deal['ASSIGNED_BY_ID'];
                $dealId = (int)$deal['ID'];

                if ($userId > 0) {
                    $byUser[$userId][] = $dealId;
                }
            }

            $logMsg = "BLOCK: по авто $carName открытые сделки: ";
            foreach ($byUser as $uid => $ids) {
                $logMsg .= "user $uid: [" . implode(', ', $ids) . "]; ";
            }
            self::log($logMsg);

            // Отправка каждому своему сообщения
            if (Loader::includeModule('im')) {
                foreach ($byUser as $userId => $ids) {
                    $msg = "По автомобилю $carName у вас имеются незакрытые заказ-наряды (сделки) с ID: " .
                        implode(', ', $ids) . ". Пожалуйста, закройте их перед созданием нового.";
                    \CIMNotify::Add([
                        "TO_USER_ID"    => $userId,
                        "FROM_USER_ID"  => 0,
                        "NOTIFY_TYPE"   => IM_NOTIFY_SYSTEM,
                        "NOTIFY_MODULE" => "crm",
                        "NOTIFY_MESSAGE" => $msg,
                    ]);
                    self::log("notify sent to user {$userId} for deals: " . implode(', ', $ids));
                }
            }

            // Выводим общее сообщение для интерфейса
            $allIds = array_column($openDeals, 'ID');
            return self::block(
                "По автомобилю $carName имеются незакрытые заказ-наряды (сделки) с ID: " . implode(', ', $allIds) .
                ". Пожалуйста, закройте их перед созданием нового.",
                $fields
            );
        }

        self::log("PASS  deal ok (car {$carId})");
        return true;
    }

    /**
     * Заблокировать сохранение и вывести сообщение.
     *
     * @param string $msg
     * @param array $fields
     * @return false
     */
    private static function block(string $msg, array &$fields): bool
    {
        $fields['RESULT_MESSAGE'] = $msg;
        self::log("BLOCK {$msg}");
        return false;
    }

    /**
     * Запись лога
     *
     * @param string $text
     * @return void
     */
    private static function log(string $text): void
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . self::LOG;

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }

        file_put_contents(
            $file,
            '[' . date('Y-m-d H:i:s') . "] {$text}\n",
            FILE_APPEND
        );
    }
}

// Регистрация событий
AddEventHandler('crm', 'OnBeforeCrmDealAdd',    ['DealHandlers', 'onBefore']);
