<?
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Beeralex\Core\Config\ConfigLoaderFactory;
use Beeralex\Core\Config\Module\TabsFactory;
use Bitrix\Main\Localization\Loc;

if(!$moduleDirPath) {
    throw new \InvalidArgumentException('Module dir path is required');
}

$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($POST_RIGHT < "S") {
    $APPLICATION->AuthForm('Недостаточные права доступа');
}
Loader::includeModule($module_id);

$moduleOptionsFileName = 'options_schema.php';
$configLoaderFactory = service(ConfigLoaderFactory::class);
$tabsFactory = service(TabsFactory::class);
$schemaModule = $configLoaderFactory->createOptionsLoader($moduleDirPath)->tryLoad($moduleOptionsFileName);
$tabs = [];
$localTabs = [];
$moduleTabs = [];
$addedAccessTab = false;

if ($localOptionsFileName) {
    $schemaLocal = $configLoaderFactory->createOptionsLoader()->tryLoad($localOptionsFileName);
    if ($schemaLocal) {
        $schemaLocal->tab("tab_rights", Loc::getMessage("MAIN_TAB_RIGHTS"), Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"));
        $addedAccessTab = true;
        $localTabs = $schemaLocal->toArray();
    }
}

if ($schemaModule) {
    if (!$addedAccessTab) {
        $schemaModule->tab("tab_rights", Loc::getMessage("MAIN_TAB_RIGHTS"), Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"));
    }
    $moduleTabs = $schemaModule->toArray();
}

$tabs = array_merge($moduleTabs, $localTabs);
$tabsCollection = $tabsFactory->fromSchema($tabs);
$tabs = $tabsCollection->getTabs();

if ($request->isPost() && check_bitrix_sessid()) {
    foreach ($tabs as $tab) {
        $fileds = $tab->getFields();
        if (!isset($fileds)) {
            continue;
        }
        foreach ($fileds as $filed) {
            if($name = $filed->getName()){
                if ($request["apply"]) {
                    $optionValue = $request->getPost($name);
                    $optionValue = is_array($optionValue) ? implode(",", $optionValue) : $optionValue;
                    Option::set($module_id, $name, $optionValue);
                }
                if ($request["default"]) {
                    Option::set($module_id, $name, $filed->getDefaultValue());
                }
            }
        }
    }
}

$tabControl = new CAdminTabControl(
    "tabControl",
    $tabsCollection->getTabsFormattedArray()
);

$tabControl->Begin();
?>

<form action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= $module_id ?>&lang=<?= LANG ?>" method="post">
    <? foreach ($tabs as $tab) {
        if ($options = $tab->getOptionsFormattedArray()) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $options);
        }
    }
    $tabControl->BeginNextTab();

    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php";

    $tabControl->Buttons();
    echo (bitrix_sessid_post());
    ?>
    <input class="adm-btn-save" type="submit" name="apply" value="Применить" />
    <input type="submit" name="default" value="По умолчанию" />
</form>
<?
$tabControl->End();