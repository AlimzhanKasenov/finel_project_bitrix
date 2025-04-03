<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Crm\CompanyTable;
use Bitrix\Crm\Settings\LeadSettings;
use CIBlockElement;
use CUserTypeCrm; // Для правильной привязки CRM-элемента

class CBPSearchByInnActivity extends BaseActivity
{
    public function __construct($name)
    {
        parent::__construct($name);

        // ✅ Оставляем все свойства, которые были ранее
        $this->arProperties = [
            'Inn'                => '',    // Вход: ИНН
            'Text'               => null,  // Выход: название компании
            'ZakazchikElementID' => null,  // Выход: ID найденной/созданной компании в CRM
            'ElementId26'        => null,  // Вход: ID элемента в ИБ 26
            'ErrorText'          => null,  // Выход: текст ошибки
        ];

        // ✅ Сохраняем типы данных
        $this->SetPropertiesTypes([
            'Inn'                => ['Type' => FieldType::STRING],
            'Text'               => ['Type' => FieldType::STRING],
            'ZakazchikElementID' => ['Type' => FieldType::INT],
            'ElementId26'        => ['Type' => FieldType::INT],
            'ErrorText'          => ['Type' => FieldType::TEXT],
        ]);
    }

    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * Основная логика выполнения
     */
    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        // ✅ 1. Проверяем модули
        if (!Loader::includeModule('crm'))
        {
            $errors->setError(new Error('Модуль CRM не установлен'));
            $this->preparedProperties['ErrorText'] = 'Модуль CRM не установлен';
            return $errors;
        }
        if (!Loader::includeModule('iblock'))
        {
            $errors->setError(new Error('Модуль iblock не установлен'));
            $this->preparedProperties['ErrorText'] = 'Модуль iblock не установлен';
            return $errors;
        }

        // ✅ 2. Проверяем входные данные
        $inn = trim($this->Inn);
        if (!$inn || !preg_match('/^\d{10,12}$/', $inn))
        {
            $errors->setError(new Error('Некорректный ИНН'));
            $this->preparedProperties['ErrorText'] = 'Некорректный ИНН';
            return $errors;
        }

        // ✅ 3. Ищем компанию в CRM по UF_CRM_1740658444 (новое поле для ИНН)
        $company = CompanyTable::getList([
            'filter' => ['=UF_CRM_1740658444' => $inn],
            'select' => ['ID', 'TITLE']
        ])->fetch();

        if ($company)
        {
            // Компания найдена
            $companyId   = (int)$company['ID'];
            $companyName = $company['TITLE'];
        }
        else
        {
            // ✅ 4. Если компании нет в CRM, ищем в DaData
            $token = "e035caa2775243da49a0701a724b7dae11ee400f"; // API-ключ DaData
            $url   = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";

            $companyData = $this->getCompanyFromDadata($url, $inn, $token);
            if (!$companyData)
            {
                $errors->setError(new Error('Компания по ИНН не найдена в DaData'));
                $this->preparedProperties['ErrorText'] = 'Компания по ИНН не найдена в DaData';
                return $errors;
            }

            // ✅ 5. Создаём новую компанию в CRM
            $addResult = CompanyTable::add([
                'TITLE'                => $companyData["name"],
                'UF_CRM_1740658444'    => $companyData["inn"] ?? '',
                'UF_CRM_ZAKAZCHIK_CRM' => $companyData["ogrn"] ?? '',
                'ASSIGNED_BY_ID'       => 1, // ✅ Ответственный (администратор)
                'MODIFY_BY_ID'         => 1, // ✅ Кем обновлено (администратор)
            ]);

            if (!$addResult->isSuccess())
            {
                $errors->setError(new Error(
                    'Ошибка создания компании в CRM: ' . implode('; ', $addResult->getErrorMessages())
                ));
                return $errors;
            }

            $companyId   = $addResult->getId();
            $companyName = $companyData["name"];
        }

        // ✅ 6. Записываем ID компании в инфоблок 26 (если указан ElementId26)
        $elementId26 = (int)$this->ElementId26;
        if ($elementId26 > 0)
        {
            $crmCompanyBinding = 'CO_'.$companyId; // Формат для привязки к CRM

            CIBlockElement::SetPropertyValuesEx(
                $elementId26,
                26,
                ['ZAKAZCHIK_CRM' => [$crmCompanyBinding]]
            );
        }

        // ✅ 7. Возвращаем все нужные параметры
        $this->preparedProperties['Inn']                = $inn;
        $this->preparedProperties['Text']               = $companyName;
        $this->preparedProperties['ZakazchikElementID'] = $companyId;
        $this->preparedProperties['ElementId26']        = $elementId26;
        $this->preparedProperties['ErrorText']          = '';

        return $errors;
    }

    /**
     * Запрос к DaData
     */
    private function getCompanyFromDadata($url, $inn, $token)
    {
        $data = [
            "query" => $inn,
            "count" => 1,
            "type"  => "LEGAL",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Token " . $token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (empty($result['suggestions'])) {
            return null;
        }

        $company = $result['suggestions'][0];
        return [
            "name"    => $company["value"] ?? "Неизвестная компания",
            "inn"     => $company["data"]["inn"] ?? "",
            "ogrn"    => $company["data"]["ogrn"] ?? "",
            "kpp"     => $company["data"]["kpp"] ?? "",
            "address" => $company["data"]["address"]["unrestricted_value"] ?? "",
        ];
    }
}
