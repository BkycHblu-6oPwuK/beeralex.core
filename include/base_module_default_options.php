<?php

use Beeralex\Core\Config\ConfigLoaderFactory;
use Beeralex\Core\Config\Module\TabsFactory;
use Bitrix\Main\Localization\Loc;

if(!$moduleDirPath) {
    throw new \InvalidArgumentException('Module dir path is required');
}

$moduleOptionsFileName = 'options_schema.php';
$configLoaderFactory = service(ConfigLoaderFactory::class);
$tabsFactory = service(TabsFactory::class);
$schemaModule = $configLoaderFactory->createOptionsLoader($moduleDirPath)->tryLoad($moduleOptionsFileName);
$tabs = [];
$localTabs = [];
$moduleTabs = [];

if ($localOptionsFileName) {
    $schemaLocal = $configLoaderFactory->createOptionsLoader()->tryLoad($localOptionsFileName);
    if ($schemaLocal) {
        $localTabs = $schemaLocal->toArray();
    }
}

if ($schemaModule) {
    $moduleTabs = $schemaModule->toArray();
}

$tabs = array_merge($moduleTabs, $localTabs);

$tabsCollection = $tabsFactory->fromSchema($tabs);

$tabs = $tabsCollection->getTabs();
$module_default_option = [];
foreach ($tabs as $tab) {
    $fields = $tab->getFields();
    foreach ($fields as $field) {
        $value = $field->getDefaultValue();
        if ($value !== null && $value !== '') {
            $module_default_option[$field->getName()] = $field->getExtraOptions()[$value] ?? $value;
        }
    }
}

return $module_default_option;
