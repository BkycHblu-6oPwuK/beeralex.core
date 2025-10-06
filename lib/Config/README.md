Классы помошники для создания полей настроек в модуле.

Можно создавать табы и в них прокидывать поля.

Для каждого типа поля который обрабатывает битрикс (в bitrix/modules/main/admin/settings.php функция renderInput) был реализован класс.

## Пример

```php

$mainTab = new Tab("edit1", "Название вкладки в табах", "Главное название в админке");
$mainTab->addField((new Checkbox("hmarketing_checkbox1", "Поясняющий текс элемента checkbox"))->setLabel("Название секции checkbox"))
->addField((new Input("hmarketing_text", "Поясняющий текс элемента text"))->setSize(10)->setDefaultValue("Жми!"));

$mainTab->addField((new Password("hmarketing_Password", "Поясняющий текс элемента Password"))->setLabel('Название секции Password')->setDefaultValue("Password"));
$mainTab->addField(new StaticText("Поясняющий текс элемента StaticText", "StaticText"));
$mainTab->addField(new StaticHtml("Поясняющий текс элемента StaticHtml", "<a href='1221'>StaticHtml</a>"));
$mainTab->addField((new TextArea("hmarketing_text3", "Поясняющий текс элемента text"))->setSize([10,50])->setDefaultValue("Жми!"));
$mainTab->addField((new Input("hmarketing_text_dis", "Поясняющий текс элемента text dis", '10'))->setLabel('Название секции text dis')->setDefaultValue("Жми! dis")->isDisabled());
$mainTab->addField((new Select("hmarketing_selectbox", "Поясняющий текс элемента selectbox", [
    "460" => "460Х306",
    "360" => "360Х242"
]))->setDefaultValue("460"));
$mainTab->addField((new Select("hmarketing_selectbox dis", "Поясняющий текс элемента selectbox dus", [
    "460" => "460Х306",
    "360" => "360Х242"
]))->isDisabled()->setDefaultValue("460"));
$mainTab->addField((new MultiSelect("MultiSelect" ,"Поясняющий текс элемента multiselectbox", [
    "left" => "Лево",
    "right" => "Право",
    "top" => "Верх",
    "bottom" => "Низ"
]))->setDefaultValue(['left','bottom']));

$tabsBuilder = new TabsBuilder();
$tabsBuilder->addTab($mainTab);

$tabs = $tabsBuilder->getTabs();

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
    $tabsBuilder->getTabsFormattedArray()
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
```

## OptionsLoader

Можно дать возможность разработчикам создавать локальные настройки, для этого используйте ``` Beeralex\Core\Config\OptionsLoader ```.

В options.php вашего модуля подгружайте настройки из local/config/{name}.php

файл конфигурации должен возвращать объект ``` Beeralex\Core\Config\Schema ```, можно создавать табы через метод ``` tab ``` и в колбэке добавлять в ``` SchemaTab ``` поля, для каждого поля в ``` Beeralex\Core\Config\Fields ``` в SchemaTab определен метод для создания поля

```php
use Beeralex\Core\Config\Schema;
use Beeralex\Core\Config\SchemaTab;

return Schema::make()
    ->tab('general', 'Общие', 'Главные настройки', function (SchemaTab $tab) {
        $tab->select('DATE_FORMAT_SITE', 'Формат даты на сайте', [
            '0' => 'd.m.Y',
        ], default: 0)
            ->checkbox('SWITH_CATALOG_TYPES', 'Разделение каталога на типы', default: true);
    })->tab('catalog', 'catalog', 'Настройки каталога', function (SchemaTab $tab) {
        $tab->select('name', 'help', ['options'], 'label', false, 0)
            ->input('name2', 'help2', 'label2', '20', false, 'default');
    });
```

в options.php модуля подгрузите настройки из вашего файла, например:

```php
$schema = OptionsLoader::getInstance()->load('beeralex_core_options.php');
if(!$schema) return;
$tabsBuilder = TabsFactory::fromSchema($schema->toArray());
$accessTab = new Tab("edit2", Loc::getMessage("MAIN_TAB_RIGHTS"), Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"));
$tabsBuilder->addTab($accessTab);

$tabs = $tabsBuilder->getTabs();
```

и в default_options.php нужно так же реализовать подгрузку, чтобы настройки по умолчанию были

```php
$schema = OptionsLoader::getInstance()->load('beeralex_core_options.php');
if(!$schema) return;

$tabsBuilder = TabsFactory::fromSchema($schema->toArray());

$tabs = $tabsBuilder->getTabs();
$beeralex_core_default_option = [];
foreach($tabs as $tab) {
    $fields = $tab->getFields();
    foreach($fields as $field) {
        $value = $field->getDefaultValue();
        if($value !== null && $value !== '') {
            $beeralex_core_default_option[$field->getName()] = $field->getExtraOptions()[$value] ?? $value;
        }
    }
}
```

мерджите массив пользовательских настроек с своими.

# AbstractOptions

Наследуйтесь от ``` Beeralex\Core\Config\AbstractOptions ``` чтобы получать настройки вашего модуля через класс singleton, создавайте свои свойства в классе и заполняйте их в методе ``` mapOptions ```, а валидацию проводите в ``` validateOptions ```

например

```php
final class Options extends AbstractOptions
{
    public readonly string $authorizationKey;
    public readonly string $scope;
    public readonly string $baseOauthUrl;
    public readonly string $baseGigaChatUrl;
    public readonly string $defaultModel;
    public readonly bool $logsEnable;

    protected function mapOptions(array $options): void
    {
        $this->authorizationKey = $options['authorization_key'] ?? '';
        $this->scope = $options['scope'] ?? '';
        $this->baseOauthUrl = $options['base_oauth_url'] ?? '';
        $this->baseGigaChatUrl = $options['base_gigachat_url'] ?? '';
        $this->logsEnable = ($options['logs_enable'] ?? '') === 'Y';
        $this->defaultModel = $options['gigachat_model'] ?? '';
    }

    protected function validateOptions(): void
    {
        if(!$this->authorizationKey || !$this->scope || !$this->baseOauthUrl || !$this->baseGigaChatUrl){
            throw new \RuntimeException(
                "Не заполнены обязательные настройки модуля (Ключ авторизации, Scope, Базовый адрес запроса для получения токена или Базовый адрес запроса к GigaChat API)"
            );
        }
    }

    public function getModuleId(): string
    {
        return 'beeralex.gigachat';
    }
}
```