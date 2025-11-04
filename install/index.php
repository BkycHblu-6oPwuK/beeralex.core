<?php

use Beeralex\Core\Helpers\FilesHelper;
use Beeralex\Core\UserType\IblockLinkType;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

class beeralex_core extends CModule
{
    var $MODULE_ID = 'beeralex.core';
    var $MODULE_NAME = 'beeralex.core';
    var $MODULE_DESCRIPTION = "beeralex.core";
    var $MODULE_VERSION = "1.0";
    var $MODULE_VERSION_DATE = "2024-04-09 12:00:00";
    var $PARTNER_NAME = 'beeralex.core';
    var $PARTNER_URI = 'beeralex.core';

    public function DoInstall()
    {
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        Loader::includeModule($this->MODULE_ID);
        $this->copyLocalFiles();
        $this->InstallEvents();
    }

    public function DoUninstall()
    {
        Loader::includeModule($this->MODULE_ID);
        $this->UnInstallEvents();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    protected function copyLocalFiles()
    {
        $moduleDir = __DIR__;
        $sourceDir = $moduleDir . '/files';
        $targetDir = Application::getDocumentRoot();

        FilesHelper::copyRecursive($sourceDir, $targetDir);
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, IblockLinkType::class, 'GetUserTypeDescription');
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, IblockLinkType::class, 'GetUserTypeDescription');
    }
}
