<?php

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Application;

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
        $this->copyLocalFiles();
    }

    public function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    protected function copyLocalFiles()
    {
        $moduleDir = __DIR__;
        $sourceDir = $moduleDir . '/local';
        $targetDir = Application::getDocumentRoot() . '/local';

        if (!is_dir($sourceDir)) {
            return;
        }

        $this->copyRecursive($sourceDir, $targetDir);
    }

    protected function copyRecursive($source, $target)
    {
        $dir = opendir($source);
        @mkdir($target, 0775, true);

        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcPath = $source . '/' . $file;
            $dstPath = $target . '/' . $file;

            if (is_dir($srcPath)) {
                $this->copyRecursive($srcPath, $dstPath);
            } else {
                // создаем директорию, если нужно
                @mkdir(dirname($dstPath), 0775, true);

                // копируем только если файл ещё не существует
                if (!file_exists($dstPath)) {
                    copy($srcPath, $dstPath);
                }
            }
        }

        closedir($dir);
    }
}
