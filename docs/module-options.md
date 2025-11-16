# Создание настроек модуля в админке Bitrix

Система для создания настроек модуля с использованием декларативной схемы. Поддерживает базовые настройки модуля и пользовательские настройки из `local/config/`.

## Быстрый старт

### 1. Создание схемы настроек модуля

Создайте файл `options_schema.php` в корне вашего модуля:

```php
<?php
use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab('general', 'Основные настройки', 'Главные параметры модуля', function (SchemaTab $tab) {
        $tab->input('api_key', 'API ключ для интеграции', 'API Key')
            ->checkbox('enable_logs', 'Включить логирование', 'Логирование', false, false)
            ->select('environment', 'Окружение', [
                'dev' => 'Разработка',
                'prod' => 'Продакшн'
            ], 'Окружение', false, 'dev');
    })
    ->tab('advanced', 'Расширенные', 'Дополнительные параметры', function (SchemaTab $tab) {
        $tab->textArea('custom_config', 'JSON конфигурация', 'Конфигурация', ['rows' => 10, 'cols' => 50])
            ->password('secret_key', 'Секретный ключ', 'Secret Key');
    });
```

### 2. Настройка options.php

Создайте файл `options.php` в корне модуля:

```php
<?php
$localOptionsFileName = 'your_module_options.php'; // Имя файла в local/config/
$moduleDirPath = __DIR__;

$baseModuleOptionsPath = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/beeralex.core/include/base_module_options.php';
if (!file_exists($baseModuleOptionsPath)) {
    $baseModuleOptionsPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/beeralex.core/include/base_module_options.php';
}
require $baseModuleOptionsPath;
```

### 3. Настройка default_option.php

Создайте файл `default_option.php` в корне модуля:

```php
<?php
$localOptionsFileName = 'your_module_options.php'; // Имя файла в local/config/
$moduleDirPath = __DIR__;

$baseModuleDefaultOptionsPath = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/beeralex.core/include/base_module_default_options.php';
if (!file_exists($baseModuleDefaultOptionsPath)) {
    $baseModuleDefaultOptionsPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/beeralex.core/include/base_module_default_options.php';
}
$your_module_default_option = require $baseModuleDefaultOptionsPath;
```

**Готово!** Теперь в админке Bitrix появятся настройки вашего модуля.

---

## Доступные типы полей

### Input (текстовое поле)
```php
$tab->input('field_name', 'Описание поля', 'Label', '20', false, 'default value');
```
Параметры: `name`, `help`, `label`, `size`, `disabled`, `default`

### Password (пароль)
```php
$tab->password('password_field', 'Введите пароль', 'Пароль', 'default');
```
Параметры: `name`, `help`, `label`, `default`

### Checkbox (чекбокс)
```php
$tab->checkbox('enable_feature', 'Включить функцию', 'Функция активна', false, true);
```
Параметры: `name`, `help`, `label`, `disabled`, `checked`

### Select (выпадающий список)
```php
$tab->select('status', 'Статус системы', [
    'active' => 'Активна',
    'inactive' => 'Неактивна',
    'maintenance' => 'Обслуживание'
], 'Статус', false, 'active');
```
Параметры: `name`, `help`, `options`, `label`, `disabled`, `default`

### MultiSelect (множественный выбор)
```php
$tab->multiSelect('permissions', 'Разрешения', [
    'read' => 'Чтение',
    'write' => 'Запись',
    'delete' => 'Удаление'
], 'Права доступа', ['read', 'write']);
```
Параметры: `name`, `help`, `options`, `label`, `default`

### TextArea (многострочное поле)
```php
$tab->textArea('description', 'Описание', 'Описание', ['rows' => 5, 'cols' => 40], 'text');
```
Параметры: `name`, `help`, `label`, `size`, `default`

### StaticText (статический текст)
```php
$tab->staticText('Информация', 'Это информационное сообщение');
```
Параметры: `help`, `text`

### StaticHtml (HTML контент)
```php
$tab->staticHtml('HTML блок', '<div class="alert">Важная информация</div>');
```
Параметры: `help`, `html`

---

## Пользовательские настройки из local/config/

Разработчики могут расширять настройки модуля, создав файл конфигурации в `local/config/`.

### Пример: local/config/beeralex_user_options.php

```php
<?php
use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab('custom', 'Пользовательские', 'Дополнительные настройки', function (SchemaTab $tab) {
        $tab->input('custom_field', 'Пользовательское поле', 'Custom Field')
            ->checkbox('custom_toggle', 'Включить функцию', 'Toggle', false, false);
    });
```

Эти настройки автоматически добавятся к настройкам модуля, если указано имя файла в `$localOptionsFileName`.

---

## Получение настроек в коде

### Через AbstractOptions и DI контейнер

Создайте класс для доступа к настройкам модуля:

```php
<?php
namespace YourVendor\YourModule;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    public readonly string $apiKey;
    public readonly bool $enableLogs;
    public readonly string $environment;

    protected function mapOptions(array $options): void
    {
        $this->apiKey = $options['api_key'] ?? '';
        $this->enableLogs = ($options['enable_logs'] ?? '') === 'Y';
        $this->environment = $options['environment'] ?? 'dev';
    }

    protected function validateOptions(): void
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('API ключ не настроен');
        }
    }

    public function getModuleId(): string
    {
        return 'yourvendor.yourmodule';
    }
}
```

Зарегистрируйте в DI контейнере в файле `.settings.php` модуля:

```php
<?php
use YourVendor\YourModule\Options;

return [
    'services' => [
        'value' => [
            Options::class => [
                'className' => Options::class
            ],
        ],
    ],
];
```

Использование через хелпер `service()`:

```php
$options = service(Options::class);
echo $options->apiKey;
```

---

## Структура файлов модуля

```
/local/modules/yourvendor.yourmodule/
├── .settings.php               # Регистрация сервисов в DI
├── options_schema.php          # Схема настроек модуля
├── options.php                 # Подключение base_module_options.php
├── default_option.php          # Подключение base_module_default_options.php
└── lib/
    └── Options.php             # Класс для доступа к настройкам
```

**Внешние файлы (опционально):**

```
/local/config/
└── yourmodule_options.php      # Пользовательские настройки
```

---

## Как это работает

1. **base_module_options.php** загружает:
   - Схему из `options_schema.php` (настройки модуля)
   - Схему из `local/config/{$localOptionsFileName}` (пользовательские настройки)
   - Объединяет табы и генерирует форму настроек

2. **base_module_default_options.php** загружает те же схемы и извлекает значения по умолчанию

3. **Schema** и **SchemaTab** - fluent API для декларативного описания полей

4. **TabsFactory** преобразует схему в коллекцию табов для Bitrix CAdminTabControl

5. **AbstractOptions** - базовый класс для типизированного доступа к настройкам модуля
