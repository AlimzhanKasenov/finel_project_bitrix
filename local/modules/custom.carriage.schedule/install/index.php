<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

use Bitrix\Main\Loader;

class custom_carriage_schedule extends CModule
{
	public $MODULE_ID = "custom.carriage.schedule";
	public $MODULE_GROUP_RIGHTS = "Y";
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
		$this->MODULE_NAME = "Расписание вагонов";
		$this->MODULE_DESCRIPTION = "Модуль для отслеживания движения вагонов";
		$this->PARTNER_NAME = "asdasd";
		$this->PARTNER_URI = "https://asdasd.com";
	}

	public function isVersionD7()
	{
		return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '20.00.00');
	}

	public function DoInstall()
	{
		global $APPLICATION;

		if (!$this->isVersionD7()) {
			$APPLICATION->ThrowException('CARRIAGE_SCHEDULE_INSTALL_ERROR');
			return false;
		}

		ModuleManager::registerModule($this->MODULE_ID);

		$this->InstallDB();
		$this->installFiles(); // Установка файлов модуля
		$this->InstallEvents(); // Регистрируем обработчики событий
	}

	public function DoUninstall()
	{
		$this->UnInstallDB();
		$this->uninstallFiles();
		$this->UnInstallEvents(); // Удаляем компоненты

		ModuleManager::unRegisterModule($this->MODULE_ID);
	}

	public function GetPath($notDocumentRoot = false)
	{
		return $notDocumentRoot
			? str_ireplace(Application::getDocumentRoot(), '', __DIR__)
			: __DIR__;
	}

	// Установка файлов
	function installFiles()
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

	// Удаление файлов
	function uninstallFiles()
	{
		$componentPath = $_SERVER['DOCUMENT_ROOT'] . '/local/components/custom/carriage.schedule';
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
			'\\Custom\\CarriageSchedule\\Handlers',
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
			'\\Custom\\CarriageSchedule\\Handlers',
			'updateTabs'
		);
	}

	public function InstallDB()
	{
		global $DB, $APPLICATION;
		$errors = false;
		//создаем таблицы, если они еще не существуют
		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/db/mysql/install.sql");
		if (!empty($errors)) {
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		Loader::includeModule("custom.carriage.schedule");
		// Заполнение таблицы тестовыми данными
		\Custom\CarriageSchedule\CarriageSeeder::seed();

		return true;
	}

	public function UnInstallDB()
	{
		global $DB, $APPLICATION;

		$errors = false;
		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/db/mysql/uninstall.sql");

		if (!empty($errors)) {
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		return true;
	}
}
