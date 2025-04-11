<?php
use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Crm\Service\Container;

/**
 * Класс DealHandlers
 * Проверяет, что по одному автомобилю (смарт-процесс ID 1040)
 * нельзя создать более одной открытой сделки.
 */
class DealHandlers
{
    /**
     * Код пользовательского поля, хранящего связь с автомобилем.
     */
    public const CAR_FIELD_CODE = 'PARENT_ID_1040';

    /**
     * ID смарт-процесса автомобилей.
     */
    public const CAR_SMART_ID = 1040;

    /**
     * Путь к лог-файлу.
     */
    private const LOG = '/local/logs/deal_debug.log';

    /**
     * Обработчик события OnBeforeCrmDealAdd / Update.
     * Проверяет наличие других незакрытых сделок по тому же автомобилю.
     *
     * @param array $fields Массив данных сделки (ссылка).
     * @return bool true — разрешить сохранение, false — отменить.
     */
    public static function onBefore(array &$fields): bool
    {
        if (!Loader::includeModule('crm')) {
            return true;
        }

        $dealId = (int)($fields['ID'] ?? 0);
        $rawCar = (string)($fields[self::CAR_FIELD_CODE] ?? '');

        self::log("START  dealID={$dealId}  rawCar=\"{$rawCar}\"");

        // Преобразуем числовое значение в формат D1040_ID
        if (preg_match('#^\d+$#', $rawCar)) {
            $rawCar = 'D' . self::CAR_SMART_ID . '_' . $rawCar;
            self::log("auto‑fix rawCar => {$rawCar}");
        }

        // Проверяем формат значения
        if (!preg_match('#^D' . self::CAR_SMART_ID . '_(\d+)$#', $rawCar, $m)) {
            return self::block('Автомобиль не указан или имеет неверный формат.', $fields);
        }

        $carId = (int)$m[1];
        $carName = "ID $carId";

        // Получаем название автомобиля из смарт-процесса
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

        // Ищем другие открытые сделки по этому автомобилю
        $open = DealTable::getList([
            'filter' => [
                ['=' . self::CAR_FIELD_CODE => [$rawCar, $carId, (string)$carId]],
                'CLOSED' => 'N',
                ['!ID'   => $dealId],
            ],
            'select' => ['ID']
        ])->fetchAll();

        if ($open) {
            $ids = implode(', ', array_column($open, 'ID'));
            return self::block(
                "По автомобилю $carName имеются незакрытые сделки c id: $ids. Закройте их.",
                $fields
            );
        }

        self::log("PASS  deal ok (car {$carId})");
        return true;
    }

    /**
     * Выводит сообщение об ошибке, логирует и блокирует сохранение.
     *
     * @param string $msg Сообщение ошибки.
     * @param array &$fields Массив полей сделки (по ссылке).
     * @return bool false
     */
    private static function block(string $msg, array &$fields): bool
    {
        $fields['RESULT_MESSAGE'] = $msg;
        self::log("BLOCK {$msg}");
        return false;
    }

    /**
     * Пишет сообщение в лог-файл.
     *
     * @param string $text Текст для записи в лог.
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

/**
 * Регистрация обработчиков события перед созданием и обновлением сделки.
 */
AddEventHandler('crm', 'OnBeforeCrmDealAdd',    ['DealHandlers', 'onBefore']);
//AddEventHandler('crm', 'OnBeforeCrmDealUpdate', ['DealHandlers', 'onBefore']);