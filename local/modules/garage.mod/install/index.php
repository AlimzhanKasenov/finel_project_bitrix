<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

/**
 * Основной класс пользовательского модуля garage.mod
 *
 * Отвечает за установку, удаление, регистрацию событий и компонентов.
 */
class garage_mod extends CModule
{
    /** @var string Идентификатор модуля */
    public $MODULE_ID = 'garage.mod';

    /** @var string Версия модуля */
    public $MODULE_VERSION;

    /** @var string Дата версии */
    public $MODULE_VERSION_DATE;

    /** @var string Название модуля */
    public $MODULE_NAME;

    /** @var string Описание модуля */
    public $MODULE_DESCRIPTION;

    /** @var string Название партнёра */
    public $PARTNER_NAME;

    /** @var string Сайт партнёра */
    public $PARTNER_URI;

    /**
     * Конструктор.
     * Загружает информацию из version.php и языковых файлов.
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
     * Проверка, поддерживается ли D7.
     *
     * @return bool
     */
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '20.00.00');
    }

    /**
     * Установка модуля: регистрация, копирование файлов, события.
     *
     * @return bool|null
     * @throws \Exception
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
     * Деинсталляция модуля: удаление событий, файлов и снятие регистрации.
     *
     * @throws \Bitrix\Main\IO\InvalidPathException
     */
    public function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->uninstallFiles();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * Копирует компонент в local/components/custom/grid
     *
     * @throws \Exception
     */
    public function installFiles()
    {
        $source = $this->GetPath() . '/components/grid';
        $destination = Application::getDocumentRoot() . '/local/components/custom/grid';

        if (!\Bitrix\Main\IO\Directory::isDirectoryExists($source)) {
            throw new \Exception('Ошибка: исходная папка с компонентом не найдена.');
        }

        \Bitrix\Main\IO\Directory::createDirectory($destination);

        if (!CopyDirFiles($source, $destination, true, true)) {
            throw new \Exception('Ошибка копирования компонента.');
        }
    }

    /**
     * Удаляет компонент из local/components/custom/grid
     */
    public function uninstallFiles()
    {
        $componentPath = Application::getDocumentRoot() . '/local/components/custom/grid';

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($componentPath)) {
            \Bitrix\Main\IO\Directory::deleteDirectory($componentPath);
        }
    }

    /**
     * Регистрирует обработчики событий модуля.
     */
    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
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
        EventManager::getInstance()->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Garage\\Mod\\GarageHandlers',
            'updateTabs'
        );
    }

    /**
     * Возвращает путь до папки модуля.
     *
     * @param bool $notDocumentRoot Если true — путь без DOCUMENT_ROOT
     * @return string
     */
    public function GetPath($notDocumentRoot = false)
    {
        return $notDocumentRoot
            ? str_ireplace(Application::getDocumentRoot(), '', __DIR__)
            : __DIR__;
    }
}
