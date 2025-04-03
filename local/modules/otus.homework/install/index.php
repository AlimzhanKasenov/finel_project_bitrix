<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class otus_homework extends CModule
{
    public $MODULE_ID = 'otus.homework';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    function __construct()
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

    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '20.00.00');
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!$this->isVersionD7()) {
            $APPLICATION->ThrowException(Loc::getMessage('OTUS_HOMEWORK_INSTALL_ERROR'));
            return false;
        }

        // Регистрируем модуль
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

        // Устанавливаем компоненты
        $this->installFiles();

        // Регистрируем обработчики событий
        $this->InstallEvents();
    }

    public function DoUninstall()
    {
        // Удаляем обработчики событий
        $this->UnInstallEvents();

        // Удаляем компоненты
        $this->uninstallFiles();

        // Снимаем регистрацию модуля
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installFiles()
    {
        $source = $this->GetPath() . '/components';
        $destination = $_SERVER['DOCUMENT_ROOT'] . '/local/components';

        // Проверяем, существует ли исходная папка
        if (!\Bitrix\Main\IO\Directory::isDirectoryExists($source)) {
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "Папка не найдена: $source\n", FILE_APPEND);
            throw new \Exception('Ошибка: исходная папка с компонентами не найдена.');
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "Копирование из $source в $destination\n", FILE_APPEND);

        // Копируем файлы
        if (!CopyDirFiles($source, $destination, true, true)) {
            throw new \Exception('Ошибка копирования компонентов.');
        }
    }


    public function uninstallFiles()
    {
        $componentPath = $_SERVER['DOCUMENT_ROOT'] . '/local/components/otus.homework';
        if (\Bitrix\Main\IO\Directory::isDirectoryExists($componentPath)) {
            \Bitrix\Main\IO\Directory::deleteDirectory($componentPath);
        }
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Homework\\Handlers',
            'updateTabs'
        );
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Homework\\Handlers',
            'updateTabs'
        );
    }

    public function GetPath($notDocumentRoot = false)
    {
        return $notDocumentRoot
            ? str_ireplace(Application::getDocumentRoot(), '', __DIR__)
            : __DIR__;
    }
}
