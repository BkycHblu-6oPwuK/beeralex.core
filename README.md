# Установка

добавьте в composer.json экстра опцию, чтобы композер поставил пакет в local/modules

```json
"extra": {
  "installer-paths": {
    "local/modules/{$name}/": ["type:bitrix-module"]
  }
}
```

```bash
composer require beeralex/beeralex.core
```

# beeralex.core

Удобные классы которые подойдут для любого проекта

- документация по билдеру настроек модуля - lib/Modules/Options/README.md
- документация по ресурсам - lib/Http/Resources/README.md
- документация по Vite - lib/Assets/README.md
- документация по DI - lib/DI/README.md

Классы Хэлперы

- DateHelper
- FilesHelper
- HlblockHelper
- IblockHelper
- LanguageHelper
- LocationHelper
- PaginationHelper
- WebHelper
- SsrHelper


Psr logger - простая реализация, интерфейсы psr лежат в модуле main bitrix

- Beeralex\Core\Logger\FileLogger

## подключение
в init.php после подключения autoload composer сделайте

```php
Bitrix\Main\Loader::includeModule('beeralex.core');
```

## Локальные настройки проекта

в local/config/beeralex_core_options.php реализуйте возврат объекта ``` Beeralex\Core\Config\Schema ``` который выведет настройки в админке. А через ``` Beeralex\Core\Config\Config ``` получайте значения настроек, работая с объектом класса как с массивом:

```php
Beeralex\Core\Config\Config::getInstance()['KEY_SETTINGS']
```


## urlrewrite

если хотите использовать роутинг в связке с обычным urlrewrite и при этом чтобы работали контроллеры вне модулей, то используйте urlrewrite модуля

```php
include_once($_SERVER['DOCUMENT_ROOT'] . '/local/modules/beeralex.core/routing_index.php');
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/404.php'))
	include_once($_SERVER['DOCUMENT_ROOT'] . '/404.php');
```