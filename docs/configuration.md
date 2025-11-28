# Конфигурация и Опции

Система конфигурации модуля с поддержкой загрузки настроек из разных источников.

## AbstractOptions

Базовый класс для работы с опциями модуля.

### Создание класса опций

```php
<?php
namespace YourModule;

use Beeralex\Core\Config\AbstractOptions;

class Options extends AbstractOptions
{
    public bool $enableFeature;
    public string $apiKey;
    public int $timeout;
    
    public function getModuleId(): string
    {
        return 'yourmodule.name';
    }
    
    protected function mapOptions(array $options): void
    {
        $this->enableFeature = (bool)($options['enable_feature'] ?? false);
        $this->apiKey = (string)($options['api_key'] ?? '');
        $this->timeout = (int)($options['timeout'] ?? 30);
    }
    
    protected function validateOptions(): void
    {
        if ($this->enableFeature && empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key is required when feature is enabled');
        }
    }
}
```

### Использование

```php
$options = new Options();

if ($options->enableFeature) {
    $apiKey = $options->apiKey;
    $timeout = $options->timeout;
}

// Доступ как к массиву
$value = $options['apiKey'];

// JSON сериализация
$json = json_encode($options);
```

### Регистрация в DI

```php
// .settings.php
return [
    'services' => [
        'value' => [
            Options::class => [
                'className' => Options::class
            ]
        ]
    ]
];

// Использование
$options = service(Options::class);
```

## Config

Класс для работы с конфигурацией модуля из файлов.

### Использование

```php
use Beeralex\Core\Config\Config;

$config = service(Config::class);

// Получение значения
$vitePort = $config->vitePort;
$isProduction = $config->isProduction();
$modulePath = $config->getModulePath();
```

## ConfigLoaderFactory

Фабрика для создания загрузчиков конфигурации.

```php
use Beeralex\Core\Config\ConfigLoaderFactory;

$factory = service(ConfigLoaderFactory::class);
$loader = $factory->create('array');
$config = $loader->load($data);
```

## Примеры из проектов

### beeralex.user

```php
class Options extends AbstractOptions
{
    public bool $enableJwtAuth;
    public string $jwtSecret;
    public int $jwtAccessTtl;
    public int $jwtRefreshTtl;
    
    public function getModuleId(): string
    {
        return 'beeralex.user';
    }
    
    protected function mapOptions(array $options): void
    {
        $this->enableJwtAuth = (bool)($options['enable_jwt_auth'] ?? false);
        $this->jwtSecret = (string)($options['jwt_secret'] ?? '');
        $this->jwtAccessTtl = (int)($options['jwt_access_ttl'] ?? 900);
        $this->jwtRefreshTtl = (int)($options['jwt_refresh_ttl'] ?? 2592000);
    }
}

// Использование
$options = service(Options::class);
if ($options->enableJwtAuth) {
    $token = generateToken($options->jwtSecret, $options->jwtAccessTtl);
}
```

## Заключение

Система конфигурации:
- Типобезопасная работа с опциями
- Валидация настроек
- Поддержка разных источников
- Интеграция с DI контейнером
