<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

/**
 * Класс garage_mod — основной класс пользовательского модуля Bitrix.
 * Отвечает за установку, удаление, регистрацию обработчиков событий и компонентов.
 */
class garage_mod extends CModule
{
    /** @var string Идентификатор модуля */
    public $MODULE_ID = 'garage.mod';

    /** @var string Версия модуля */
    public $MODULE_VERSION;

    /** @var string Дата версии модуля */
    public $MODULE_VERSION_DATE;

    /** @var string Название модуля */
    public $MODULE_NAME;

    /** @var string Описание модуля */
    public $MODULE_DESCRIPTION;

    /** @var string Имя партнёра */
    public $PARTNER_NAME;

    /** @var string Сайт партнёра */
    public $PARTNER_URI;

    /**
     * Конструктор модуля.
     * Загружает данные из файла version.php и языковых файлов.
     */
    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('OTUS_HOMEWORK_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('OTUS_HOMEWORK_MODULE_DESC');
        $this->PARTNER_NAME = Loc::getMessage('OTUS_HOMEWORK_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('OTUS_HOMEWORK_PARTNER_URI');
    }

    /**
     * Проверяет, соответствует ли версия ядра требованиям D7.
     *
     * @return bool true, если версия ядра >= 20.00.00
     */
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '20.00.00');
    }

    /**
     * Устанавливает модуль:
     * - Проверяет версию ядра
     * - Регистрирует модуль
     * - Копирует компоненты
     * - Регистрирует события
     *
     * @return bool|null
     */
    public function DoInstall()
    {
        global $APPLICATION;

        if (!$this->isVersionD7()) {
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_HOMEWORK_INSTALL_ERROR'));
            return false;
        }

        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

        $this->installFiles();
        $this->InstallEvents();
    }

    /**
     * Удаляет модуль:
     * - Удаляет события
     * - Удаляет компоненты
     * - Разрегистрирует модуль
     */
    public function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->uninstallFiles();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * Копирует пользовательские компоненты из модуля в local/components.
     *
     * @throws \Exception если не найдена папка с компонентами.
     */
    public function installFiles()
    {
        $source = $this->GetPath() . '/components';
        $destination = $_SERVER['DOCUMENT_ROOT'] . '/local/components';

        if (!\Bitrix\Main\IO\Directory::isDirectoryExists($source)) {
            throw new \Exception('Ошибка: исходная папка с компонентами не найдена.');
        }

        CopyDirFiles($source, $destination, true, true);
    }

    /**
     * Удаляет пользовательские компоненты модуля из local/components.
     */
    public function uninstallFiles()
    {
        $componentPath = $_SERVER['DOCUMENT_ROOT'] . '/local/components/garage';
        if (\Bitrix\Main\IO\Directory::isDirectoryExists($componentPath)) {
            \Bitrix\Main\IO\Directory::deleteDirectory($componentPath);
        }
    }

    /**
     * Регистрирует обработчики событий модуля (например, вкладка в CRM).
     */
    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Garage\\Mod\\GarageHandlers',
            'updateTabs'
        );
    }

    /**
     * Отменяет регистрацию обработчиков событий модуля.
     */
    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Garage\\Mod\\GarageHandlers',
            'updateTabs'
        );
    }

    /**
     * Возвращает путь к директории модуля.
     *
     * @param bool $notDocumentRoot Если true — путь без DOCUMENT_ROOT.
     * @return string Абсолютный или относительный путь к папке модуля.
     */
    public function GetPath($notDocumentRoot = false)
    {
        return $notDocumentRoot
            ? str_ireplace(Application::getDocumentRoot(), '', __DIR__)
            : __DIR__;
    }
}
