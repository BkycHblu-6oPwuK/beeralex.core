<?php
/**
 * Файл для получения дефолтных опций модуля из схемы настроек
 * Схема может быть определена в самом модуле и/или в локальной папке. Локальная папка имеет приоритет над модулем, что позволяет переопределять дефолтные опции без изменения кода модуля
 * Схема должна быть в формате, поддерживаемом TabsFactory::fromSchema, т.е. содержать массив табов, каждый таб должен содержать массив полей, каждое поле должно содержать имя и дефолтное значение
 * Дефолтные опции возвращаются в виде массива, где ключом является имя опции, а значением - дефолтное значение из схемы.
 * Если дефолтное значение является ключом в массиве extra_options, то возвращается значение из extra_options, иначе возвращается само дефолтное значение
 */
use Beeralex\Core\Config\ConfigLoaderFactory;
use Beeralex\Core\Config\Module\TabsFactory;

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
