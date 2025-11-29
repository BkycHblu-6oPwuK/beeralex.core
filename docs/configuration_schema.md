# Настройки модуля через Schema

Модуль предоставляет декларативную систему создания страниц настроек модулей через Schema API.

## Структура файлов настроек

Каждый модуль должен иметь следующие файлы:

```
module.name/
├── options.php              # Страница настроек (подключает base_module_options.php)
├── default_option.php       # Дефолтные значения (подключает base_module_default_options.php)
└── options_schema.php       # Схема настроек (описание полей)
```

### options.php

```php
<?php
$localOptionsFileName = 'module_name_options.php';
$moduleDirPath = __DIR__;
$bitrixPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix';
$localPath = $_SERVER['DOCUMENT_ROOT'] . '/local';
$basePathToOptions = '/modules/beeralex.core/include/base_module_options.php';

if(file_exists($bitrixPath . $basePathToOptions)) {
    include $bitrixPath . $basePathToOptions;
} elseif (file_exists($localPath . $basePathToOptions)) {
    include $localPath . $basePathToOptions;
}
```

### default_option.php

```php
<?php
$localOptionsFileName = 'module_name_options.php';
$moduleDirPath = __DIR__;
$bitrixPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix';
$localPath = $_SERVER['DOCUMENT_ROOT'] . '/local';
$basePathToOptions = '/modules/beeralex.core/include/base_module_default_options.php';

if(file_exists($bitrixPath . $basePathToOptions)) {
    include $bitrixPath . $basePathToOptions;
} elseif (file_exists($localPath . $basePathToOptions)) {
    include $localPath . $basePathToOptions;
}
```

### options_schema.php - Схема настроек

Основной файл, описывающий структуру настроек:

```php
<?php
declare(strict_types=1);

use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab(
        'edit1',
        'Основные настройки',
        'Общие настройки модуля',
        function (SchemaTab $tab) {
            $tab->checkbox(
                'MODULE_ENABLE',
                'Включить модуль',
                null,
                false,
                true
            );
            
            $tab->input(
                'MODULE_API_KEY',
                'API ключ',
                null,
                50,
                false,
                ''
            );
        }
    );
```

## Schema API

### Schema::make()

Создает новую схему настроек:

```php
$schema = Schema::make();
```

### tab()

Добавляет вкладку с настройками:

```php
$schema->tab(
    id: 'edit1',                    // ID вкладки (edit1, edit2, edit3...)
    title: 'Заголовок',             // Заголовок вкладки
    description: 'Описание',        // Описание вкладки
    callback: function (SchemaTab $tab) {
        // Добавление полей
    }
);
```

## SchemaTab - Типы полей

### checkbox() - Чекбокс

```php
$tab->checkbox(
    name: 'ENABLE_FEATURE',         // Имя опции
    help: 'Включить функцию',       // Подсказка
    label: null,                    // Метка (опционально)
    disabled: false,                // Отключено ли поле
    checkedDefault: false           // Значение по умолчанию (в реализации по умолчанию = false)
);
```

### input() - Текстовое поле

```php
$tab->input(
    name: 'API_KEY',
    help: 'API ключ для интеграции',
    label: null,
    size: 50,                       // Размер поля (int|string|array)
    disabled: false,
    default: ''                     // Значение по умолчанию
);
```

### password() - Поле пароля

```php
$tab->password(
    name: 'SECRET_KEY',
    help: 'Секретный ключ',
    label: null,
    // default опционален; в реализации по умолчанию = null
    default: null
);
```

### textArea() - Многострочное поле

```php
$tab->textArea(
    name: 'DESCRIPTION',
    help: 'Описание',
    label: null,
    // size — индексный массив [rows, cols], например [10, 50]
    size: [10, 50],
    default: ''
);
```

> Примечание: `textArea` в реализации ожидает индексный массив вида `[rows, cols]`. Если вы используете ассоциативную форму `['rows'=>..,'cols'=>..]`, `TabsFactory` передаст её дальше как есть, но `Fields\\TextArea::setSize()` ожидает индексный массив; рекомендуем использовать `[rows, cols]`.

### select() - Выпадающий список

```php
$tab->select(
    name: 'LOG_LEVEL',
    help: 'Уровень логирования',
    options: [
        'debug' => 'Debug',
        'info' => 'Info',
        'warning' => 'Warning',
        'error' => 'Error',
    ],
    label: null,
    disabled: false,
    default: 'info'
);
```

### multiSelect() - Множественный выбор

```php
$tab->multiSelect(
    name: 'ENABLED_FEATURES',
    help: 'Включенные функции',
    options: [
        'feature1' => 'Функция 1',
        'feature2' => 'Функция 2',
        'feature3' => 'Функция 3',
    ],
    label: null,
    default: ['feature1']
);
```

### staticText() - Статический текст

```php
$tab->staticText(
    help: 'Заголовок секции',
    text: 'Дополнительная информация'
);
```

### staticHtml() - HTML блок

```php
$tab->staticHtml(
    help: 'Инструкция',
    html: '<div class="adm-info-message">Важная информация</div>'
);
```

## Полный пример - JWT настройки

```php
<?php
declare(strict_types=1);

use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab(
        'edit2',
        'JWT',
        'Токеновая авторизация (JWT)',
        function (SchemaTab $tab) {
            $tab->checkbox(
                'BEERALEX_USER_ENABLE_JWT_AUTH',
                'Включить авторизацию по JWT токенам',
                null,
                false,
                true
            );

            $tab->password(
                'BEERALEX_USER_JWT_SECRET_KEY',
                'Секретный ключ для JWT',
                null,
                ''
            );

            $tab->input(
                'BEERALEX_USER_JWT_TTL',
                'Время жизни JWT токена (в секундах)',
                null,
                null,
                false,
                '3600'
            );

            $tab->input(
                'BEERALEX_USER_JWT_REFRESH_TTL',
                'Время жизни refresh токена (в секундах)',
                null,
                null,
                false,
                '2592000'
            );

            $tab->select(
                'BEERALEX_USER_JWT_ALGORITHM',
                'Алгоритм шифрования JWT',
                [
                    'HS256' => 'HS256',
                    'HS384' => 'HS384',
                    'HS512' => 'HS512',
                ],
                null,
                false,
                'HS256'
            );

            $tab->input(
                'BEERALEX_USER_JWT_ISSUER',
                'Издатель JWT токена (issuer)',
                null,
                null,
                false,
                'beeralex.user'
            );
        }
    );
```

## Получение значений настроек

После сохранения настроек через админку, значения доступны через `Option`:

```php
use Bitrix\Main\Config\Option;

// Получение значения
$isEnabled = Option::get('module.name', 'ENABLE_FEATURE', 'N') === 'Y';
$apiKey = Option::get('module.name', 'API_KEY', '');
```

## Интеграция с AbstractOptions

```php
use Beeralex\Core\Config\AbstractOptions;
use Bitrix\Main\Config\Option;

class JwtOptions extends AbstractOptions
{
    public readonly bool $enableJwtAuth;
    public readonly string $jwtSecret;
    public readonly int $jwtTtl;
    public readonly string $jwtAlgorithm;
    
    public function getModuleId(): string
    {
        return 'beeralex.user';
    }
    
    protected function mapOptions(array $options): void
    {
        $moduleId = $this->getModuleId();
        
        $this->enableJwtAuth = $options['BEERALEX_USER_ENABLE_JWT_AUTH'];
        $this->jwtSecret = $options['BEERALEX_USER_JWT_SECRET_KEY'];
        $this->jwtTtl = (int)$options['BEERALEX_USER_JWT_TTL'];
        $this->jwtAlgorithm = $options['BEERALEX_USER_JWT_ALGORITHM'];
    }
    
    protected function validateOptions(): void
    {
        if ($this->enableJwtAuth && empty($this->jwtSecret)) {
            throw new \InvalidArgumentException('JWT secret is required when JWT auth is enabled');
        }
        
        if ($this->jwtTtl < 60) {
            throw new \InvalidArgumentException('JWT TTL must be at least 60 seconds');
        }
    }
}

// Использование
$options = service(JwtOptions::class);

if ($options->enableJwtAuth) {
    $token = generateJwt($options->jwtSecret, $options->jwtTtl);
}
```

## Преимущества Schema API

1. **Декларативность** - описание настроек в одном файле
2. **Типобезопасность** - PHP 8.1+ типизация
3. **Единообразие** - одинаковый интерфейс настроек для всех модулей
4. **Валидация** - встроенная валидация полей
5. **Расширяемость** - легко добавлять новые типы полей
6. **Автоматизация** - автоматическая генерация HTML форм
