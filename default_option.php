<?

use Beeralex\Core\Config\ConfigLoaderFactory;
use Beeralex\Core\Config\TabsFactory;

$schema = ConfigLoaderFactory::getOptionsLoader()->tryLoad('beeralex_core_options.php');
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
