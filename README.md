# beeralex.core

Набор инструментов и базовых классов для разработки Bitrix-модулей с современным подходом к архитектуре.

## Установка

Добавьте в `composer.json` настройку для установки в `local/modules`:

```json
{
  "extra": {
    "installer-paths": {
      "local/modules/{$name}/": ["type:bitrix-module"]
    }
  }
}
```

Установите пакет:

```bash
composer require beeralex/beeralex.core
```

Подключите модуль в `init.php`:

```php
Bitrix\Main\Loader::includeModule('beeralex.core');
```

---

## Основные возможности

### 🔧 Настройки модулей
- Декларативная схема настроек через `Schema` и `SchemaTab`
- Поддержка пользовательских настроек из `local/config/`
- Типизированный доступ через `AbstractOptions`
- [Подробная документация](docs/module-options.md)

### 📦 Репозитории
- `AbstractRepository` - базовый класс с дженериками
- `IblockRepository` - для работы с инфоблоками
- `HighloadRepository` - для highload-блоков
- Поддержка декомпозиции запросов
- [Документация по репозиториям](lib/Repository/README.md)

### 🌐 API сервисы
- `ApiService` - базовый класс с кэшированием
- `ClientService` - HTTP-клиент с настройкой
- Типизация через дженерики для Options и Client
- Автоматическая обработка ошибок и логирование

### 🔄 HTTP адаптеры
- Bitrix ↔ PSR-7 конвертеры
- `BitrixToPsrRequest` / `BitrixToPsrResponse`
- `PsrToBitrixRequest` / `PsrToBitrixResponse`
- [Документация по контроллерам](lib/Http/Controllers/README.md)

### 📝 Логирование
- PSR-совместимый `FileLogger`
- `LoggerFactoryContract` для создания логгеров
- Автоматическое логирование в API сервисах

### ⚡ Vite интеграция
- `ViteService` для работы с Vite манифестом
- Поддержка SSR режима
- Hot Module Replacement в dev режиме

### 🛠️ Сервисы
- `QueryService` - построитель ORM запросов
- `IblockService` - работа с инфоблоками
- `HlblockService` - работа с highload блоками
- `FileService` - работа с файлами
- `PaginationService` - пагинация
- `LocationService` - работа с локациями

---

## Быстрый старт

### Создание модуля с настройками

1. Создайте `options_schema.php`:
```php
<?php
use Beeralex\Core\Config\Module\Schema\Schema;

return Schema::make()
    ->tab('general', 'Настройки', 'Основные параметры', function ($tab) {
        $tab->input('api_key', 'API ключ', 'Key')
            ->checkbox('logs_enable', 'Включить логи', 'Логи');
    });
```

2. Создайте класс настроек:
```php
<?php
namespace YourVendor\YourModule;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    public readonly string $apiKey;
    public readonly bool $logsEnable;

    protected function mapOptions(array $options): void
    {
        $this->apiKey = $options['api_key'] ?? '';
        $this->logsEnable = ($options['logs_enable'] ?? '') === 'Y';
    }

    public function getModuleId(): string
    {
        return 'yourvendor.yourmodule';
    }
}
```

3. Зарегистрируйте в `.settings.php`:
```php
<?php
return [
    'services' => [
        'value' => [
            YourVendor\YourModule\Options::class => [
                'className' => YourVendor\YourModule\Options::class
            ],
        ],
    ],
];
```

4. Используйте:
```php
$options = service(YourVendor\YourModule\Options::class);
echo $options->apiKey;
```

### Создание API сервиса

```php
<?php
namespace YourVendor\YourModule\Services;

use Beeralex\Core\Service\Api\ApiService as CoreApiService;
use YourVendor\YourModule\Options;

/**
 * @property-read Options $options
 * @property-read ClientService $clientService
 */
class ApiService extends CoreApiService
{
    public function __construct()
    {
        parent::__construct(
            service(Options::class),
            service(ClientService::class)
        );
    }

    public function getData(): array
    {
        $uri = new \Bitrix\Main\Web\Uri('https://api.example.com/data');
        return $this->get($uri);
    }
}
```

### Создание репозитория

```php
<?php
namespace YourVendor\YourModule\Repository;

use Beeralex\Core\Repository\IblockRepository;
use YourVendor\YourModule\Entity\ProductTable;

class ProductRepository extends IblockRepository
{
    public function __construct()
    {
        parent::__construct(ProductTable::class);
    }

    public function findActive(): array
    {
        return $this->query()
            ->where('ACTIVE', 'Y')
            ->fetchAll();
    }
}
```

---

## Хелперы

### service()
Получение сервиса из DI контейнера с поддержкой дженериков:

```php
/**
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function service(string $class)
```

### firstNotEmpty()
Возвращает первое непустое значение:

```php
$value = firstNotEmpty('default', $var1, $var2, $var3);
```

### toFile()
Быстрое логирование в файл через PSR Logger:

```php
toFile(['debug' => $data, 'user' => $userId]);
```

### isLighthouse()
Проверка на PageSpeed Insights:

```php
if (isLighthouse()) {
    // Отключить тяжелые скрипты
}
```

### isImport()
Проверка на обмен с 1С:

```php
if (isImport()) {
    // Специальная логика для импорта
}
```

---

## Документация

- [Настройки модулей](docs/module-options.md) - создание схем настроек
- [API сервисы](docs/api-services.md) - работа с внешними API
- [Репозитории](docs/repositories.md) - работа с данными через ORM
- [HTTP контроллеры](docs/controllers.md) - REST API
- [Prefilters](docs/prefilters.md) - валидация запросов
- [Resources](docs/resources.md) - форматирование ответов

---

## Роутинг

Для использования роутинга вместе с стандартным `urlrewrite.php`:

```php
// В /bitrix/urlrewrite.php или local/urlrewrite.php
include_once($_SERVER['DOCUMENT_ROOT'] . '/local/modules/beeralex.core/routing_index.php');
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/404.php')) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/404.php');
}
```

---

## Архитектура

### DI контейнер
Все сервисы регистрируются в `.settings.php` модуля и доступны через `service()`.

### Дженерики
Активно используются PHPDoc дженерики для типизации:
- `AbstractRepository<T of DataManager>`
- `ApiService<T of AbstractOptions, U of ClientService>`

### Трейты
- `Cacheable` - кэширование методов
- `Resourceble` - преобразование в JSON/массив
- `PathNormalizerTrait` - нормализация путей
- `TableManagerTrait` - работа с ORM таблицами

---

## Лицензия

MIT
