<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class garage_mod extends CModule
{
    public $MODULE_ID = 'garage.mod';
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

        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

        $this->installFiles();
        $this->InstallEvents();
    }

    public function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->uninstallFiles();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installFiles()
    {
        $source = $this->GetPath() . '/components';
        $destination = $_SERVER['DOCUMENT_ROOT'] . '/local/components';

        if (!\Bitrix\Main\IO\Directory::isDirectoryExists($source)) {
            throw new \Exception('Ошибка: исходная папка с компонентами не найдена.');
        }

        CopyDirFiles($source, $destination, true, true);
    }

    public function uninstallFiles()
    {
        $componentPath = $_SERVER['DOCUMENT_ROOT'] . '/local/components/garage';
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
            '\\Garage\\Mod\\GarageHandlers',
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
            '\\Garage\\Mod\\GarageHandlers',
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
