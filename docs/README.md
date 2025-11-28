# Модуль Core (beeralex.core)

Базовый модуль для разработки Bitrix-приложений с современным подходом к архитектуре. Предоставляет инструменты для работы с инфоблоками, хайлоад-блоками, репозиториями, сервисами, контроллерами и многое другое.

## Содержание

- [Установка](#установка)
- [Быстрый старт](#быстрый-старт)
- [Основные компоненты](#основные-компоненты)
- [Документация](#документация)
  - [Dependency Injection](dependency-injection.md)
  - [Репозитории](repositories.md)
  - [Сервисы](services.md)
  - [HTTP Контроллеры](controllers.md)
  - [Конфигурация](configuration.md)
  - [Настройки модулей через Schema](configuration_schema.md)
  - [Traits](traits.md)
  - [Model (ORM)](model.md)
  - [Logger (PSR-3)](logger.md)

## Установка

### Требования

- PHP >= 8.1
- 1С-Битрикс >= 14.0.0

### Через Composer (рекомендуется)

Добавьте в `composer.json`:

```json
{
  "require": {
    "beeralex/beeralex.core": "*"
  },
  "extra": {
    "installer-paths": {
      "local/modules/{$name}/": ["type:bitrix-module"]
    }
  }
}
```

Выполните:

```bash
composer require beeralex/beeralex.core
```

### Активация модуля

В административной панели: `Marketplace -> Установленные решения -> Установить "My base Bitrix module"`

Или подключите в `/local/php_interface/init.php`:

```php
\Bitrix\Main\Loader::includeModule('beeralex.core');
```

## Быстрый старт

### 1. Dependency Injection (DI)

Модуль предоставляет глобальную функцию `service()` для получения сервисов из DI-контейнера:

```php
use Beeralex\Core\Service\IblockService;
use Beeralex\Core\Repository\IblockRepository;

// Получение сервиса
$iblockService = service(IblockService::class);

// Работа с репозиторием
$repository = new IblockRepository('news');
$news = $repository->all(['ACTIVE' => 'Y']);
```

### 2. Работа с репозиториями

```php
use Beeralex\Core\Repository\IblockRepository;

$newsRepo = new IblockRepository('news');

// Получение списка элементов
$items = $newsRepo->all(
    ['ACTIVE' => 'Y'],
    ['ID', 'NAME', 'DATE_CREATE'],
    ['DATE_CREATE' => 'DESC'],
    0,
    false
);

// Или через query builder
$items = $newsRepo->query()
    ->where('ACTIVE', 'Y')
    ->setSelect(['ID', 'NAME', 'DATE_CREATE'])
    ->setLimit(10)
    ->setOrder(['DATE_CREATE' => 'DESC'])
    ->fetchAll();

// Получение одного элемента
$item = $newsRepo->getById(123);

// Добавление элемента
$id = $newsRepo->add([
    'NAME' => 'Новость',
    'ACTIVE' => 'Y',
    'PROPERTY_VALUES' => [
        'DESCRIPTION' => 'Описание новости'
    ]
]);
```

### 3. Создание API контроллера

```php
use Beeralex\Core\Http\Controllers\ApiController;

class MyApiController extends ApiController
{
    public function configureActions(): array
    {
        return [
            'getItems' => [
                'prefilters' => [],
            ],
        ];
    }
    
    public function getItemsAction(int $limit = 10): array
    {
        $repository = new \Beeralex\Core\Repository\IblockRepository('catalog');
        
        return [
            'items' => $repository->query()
                ->where('ACTIVE', 'Y')
                ->setLimit($limit)
                ->fetchAll()
        ];
    }
}
```

### 4. Работа с сервисами

```php
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Service\PaginationService;
use Beeralex\Core\Service\UserService;

// FileService - работа с файлами
$fileService = service(FileService::class);
$fileService->includeFile('catalog.index', ['productId' => 123]);

// PaginationService - пагинация
$paginationService = service(PaginationService::class);
$pages = $paginationService->getPages(1, 10);

// UserService - работа с пользователями
$userService = service(UserService::class);
$groups = $userService->getDefaultUserGroups();
```

## Функции-хелперы

Модуль предоставляет набор глобальных функций для упрощения разработки:

### service(string $class)

Получение сервиса из DI-контейнера с автоматическим определением типа:

```php
/**
 * @template T
 * @param class-string<T> $class
 * @return T
 */
$userService = service(UserService::class);
$logger = service(LoggerFactoryContract::class);
```

### firstNotEmpty(mixed $default, ...$values): mixed

Возвращает первое непустое значение или значение по умолчанию:

```php
// Полезно для fallback значений
$title = firstNotEmpty('Без названия', $item['NAME'], $item['TITLE'], $item['CODE']);

// Работает с любыми типами
$config = firstNotEmpty([], $userConfig, $defaultConfig);
```

### toFile(mixed $data): void

Быстрое логирование для отладки (пишет в default.log):

```php
// Простое логирование
toFile($debugData);

// Логирование массива
toFile([
    'user_id' => $userId,
    'action' => 'purchase',
    'amount' => $amount
]);

// Логирование объектов
toFile($request);
```

**Примечание:** Использует FileLogger через DI-контейнер.

### coreLog(string $message, int $traceDepth = 6, bool $showArgs = false): void

Логирование через встроенную систему Bitrix (AddMessage2Log):

```php
// Простое сообщение
coreLog('Ошибка при обработке заказа #123');

// С детальной трассировкой
coreLog('Критическая ошибка', 10, true);
```

**Параметры:**
- `$message` - текст сообщения
- `$traceDepth` - глубина трассировки стека (по умолчанию 6)
- `$showArgs` - показывать ли аргументы функций в трассировке

### isLighthouse(): bool

Определяет, является ли текущий запрос от Google Lighthouse:

```php
if (isLighthouse()) {
    // Отключить тяжелые скрипты для аудита производительности
    $loadHeavyScripts = false;
}

// Полезно для оптимизации метрик производительности
if (!isLighthouse()) {
    // Загружать аналитику только для реальных пользователей
    loadAnalytics();
}
```

### isImport(): bool

Проверяет, выполняется ли обмен с 1С:

```php
if (isImport()) {
    // Отключить обработчики событий при импорте
    $eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementAdd');
}

// Пропустить кеширование при импорте
if (!isImport()) {
    $cache->startDataCache();
}
```

**Как работает:** Проверяет наличие `$_REQUEST['mode'] == 'import'`.

### isCli(): bool

Проверяет запущен ли скрипт из под cli

```php
if (isCli()) {
    // Скрипт выполняется из под cli
}
```

### Примеры комбинированного использования

```php
// Безопасное получение конфигурации с fallback
$config = firstNotEmpty(
    ['timeout' => 30],
    service(Config::class)->apiConfig,
    $defaultApiConfig
);

// Условное логирование
if (!isImport() && !isLighthouse()) {
    toFile([
        'endpoint' => $endpoint,
        'response_time' => $responseTime
    ]);
}

// Получение сервиса с логированием
try {
    $service = service(PaymentService::class);
    $result = $service->process($order);
} catch (\Exception $e) {
    coreLog('Payment processing error: ' . $e->getMessage(), 10, true);
    toFile(['error' => $e->getMessage(), 'order_id' => $order->getId()]);
}
```

## Основные компоненты

### Dependency Injection

- **Функция `service()`** - Глобальная функция для получения сервисов
- **Регистрация сервисов** - В `.settings.php` модуля

[Подробная документация по DI](./dependency-injection.md)

### Репозитории

- **IblockRepository** - Работа с инфоблоками
- **IblockSectionRepository** - Работа с разделами инфоблоков
- **HighloadRepository** - Работа с хайлоад-блоками
- **AbstractRepository** - Базовый класс для всех репозиториев

[Подробная документация по репозиториям](./repositories.md)

### Сервисы

- **IblockService** - Работа с API инфоблоков
- **FileService** - Работа с файлами и подключение view
- **UserService** - Работа с пользователями
- **CatalogService** - Работа с каталогом
- **PaginationService** - Пагинация
- **LocationService** - Работа с локациями
- **ViteService** - Интеграция с Vite

[Подробная документация по сервисам](./services.md)

### HTTP/Controllers

- **ApiController** - Базовый контроллер для API с автоматической валидацией DTO
- **WebController** - Базовый контроллер для веб-страниц
- **Prefilters** - Фильтры для валидации файлов

[Подробная документация по контроллерам](./controllers.md)

### Конфигурация

- **Config** - Система конфигурации модуля
- **AbstractOptions** - Базовый класс для опций модуля
- **ConfigLoaderFactory** - Фабрика загрузчиков конфигурации

[Подробная документация по конфигурации](./configuration.md)

### Logger

- **FileLogger** - Логирование в файлы
- **FileLoggerFactory** - Фабрика для создания логгеров
- **PSR-3 совместимость** - Поддержка стандарта PSR-3

[Подробная документация по логированию](./logger.md)

### Трейты

- **Cacheable** - Кеширование результатов методов
- **Resourceble** - Преобразование объектов в массивы/JSON
- **PathNormalizerTrait** - Нормализация путей

[Подробная документация по трейтам](./traits.md)

## Документация

1. [Dependency Injection](./dependency-injection.md) - DI контейнер и регистрация сервисов
2. [Репозитории](./repositories.md) - Работа с данными через репозитории
3. [Сервисы](./services.md) - Все сервисы модуля
4. [Контроллеры](./controllers.md) - HTTP контроллеры и фильтры
5. [Конфигурация](./configuration.md) - Система конфигурации
6. [Настройки через Schema](./configuration_schema.md) - Декларативные настройки модулей
7. [Логирование](./logger.md) - PSR-3 логирование событий
8. [Трейты](./traits.md) - Переиспользуемые трейты
9. [Model (ORM)](./model.md) - Динамические ORM сущности

## Философия модуля

### Слои архитектуры

```
┌─────────────────────────────────┐
│   Controllers (HTTP Layer)      │  <- Обработка запросов
├─────────────────────────────────┤
│   Services (Business Logic)     │  <- Бизнес-логика
├─────────────────────────────────┤
│   Repositories (Data Access)    │  <- Доступ к данным
├─────────────────────────────────┤
│   Models/Entities (Data Layer)  │  <- Данные
└─────────────────────────────────┘
```

### Принципы

1. **Разделение ответственности** - Каждый класс отвечает за свою задачу
2. **Dependency Injection** - Зависимости внедряются через конструктор
3. **Repository Pattern** - Доступ к данным через репозитории
4. **Service Layer** - Бизнес-логика в сервисах
5. **DTO** - Передача данных через Data Transfer Objects

## Примеры из других модулей

### Использование в beeralex.api

```php
use Beeralex\Core\Service\FileService;
use Beeralex\Api\ApiResult;

public function getContentAction(string $pathName)
{
    service(FileService::class)->includeFile('v1.index', [
        'pathName' => $pathName,
    ]);
    
    service(ApiResult::class)->setSeo();
    return service(ApiResult::class);
}
```

### Использование в beeralex.user

```php
use Beeralex\Core\Service\UserService;

$userService = service(UserService::class);
$password = $userService->generatePassword();
$groups = $userService->getDefaultUserGroups();
```

### Использование в beeralex.catalog

```php
use Beeralex\Core\Service\CatalogService;

$catalogService = service(CatalogService::class);
$products = $catalogService->getProductsWithOffers([1, 2, 3]);
```

## Поддержка

При возникновении вопросов:
1. Изучите документацию в папке `docs/`
2. Посмотрите примеры использования в других модулях `local/modules/beeralex.*`
3. Проверьте логи в `/local/logs/`

## Лицензия

MIT

## Автор

Alexandr Belotsitsko (sanyabelyy020@gmail.com)
