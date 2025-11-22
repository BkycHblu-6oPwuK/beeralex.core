<?php

use Beeralex\Core\Service\FileService;
use Beeralex\Core\UserType\IblockLinkType;
use Beeralex\Core\UserType\WebFormLinkType;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

class beeralex_core extends CModule
{
    public function __construct()
    {
        $this->MODULE_ID = 'beeralex.core';
        $this->MODULE_VERSION = '1.1.0';
        $this->MODULE_VERSION_DATE = '2024-04-09 12:00:00';
        $this->MODULE_NAME = 'beeralex.core';
        $this->MODULE_DESCRIPTION = 'beeralex.core module';
        $this->PARTNER_NAME = 'beeralex';
        $this->PARTNER_URI = '#';
    }

    public function DoInstall()
    {
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        Loader::includeModule($this->MODULE_ID);
        $this->InstallFiles();
        $this->InstallEvents();
    }

    public function DoUninstall()
    {
        Loader::includeModule($this->MODULE_ID);
        $this->UnInstallEvents();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallFiles()
    {
        $moduleDir = __DIR__;
        $sourceDir = $moduleDir . '/files';
        $targetDir = Application::getDocumentRoot();

        service(FileService::class)->copyRecursive($sourceDir, $targetDir);
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, IblockLinkType::class, 'GetUserTypeDescription');
        $eventManager->registerEventHandler('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, WebFormLinkType::class, 'GetUserTypeDescription');
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, IblockLinkType::class, 'GetUserTypeDescription');
        $eventManager->unRegisterEventHandler('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, WebFormLinkType::class, 'GetUserTypeDescription');
    }
}
