# Модуль Core (beeralex.core)

Базовый модуль для разработки Bitrix-приложений с современным подходом к архитектуре. Предоставляет инструменты для работы с инфоблоками, репозиториями, сервисами, контроллерами и многое другое.

[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://www.php.net/)
[![Bitrix](https://img.shields.io/badge/Bitrix-25.0.0+-orange.svg)](https://www.bitrix24.ru/)

## Возможности

- 🎯 **Dependency Injection** - Управление зависимостями через DI-контейнер
- 📦 **Repository Pattern** - Удобная работа с данными (инфоблоки, хайлоад-блоки)
- ⚙️ **Сервисы** - Готовые сервисы для типовых задач
- 🎮 **Контроллеры** - Базовые контроллеры с автовалидацией
- 🔧 **Конфигурация** - Типобезопасная система настроек
- 🚀 **Vite Integration** - Интеграция с современным фронтендом

## Быстрый старт

### Установка

Добавьте в `composer.json` настройку для установки в `local/modules` (или в bitrix, это на ваше усмотрение):

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

### Версии

до версии 1.2 минимальная версия php - 8.1

- ~1.1 - php 8.1
- ~1.2 - php 8.2

### Активация

В админке: `Marketplace -> Установленные решения -> Установить`

И в `/local/php_interface/init.php`:

```php
\Bitrix\Main\Loader::includeModule('beeralex.core');
```

### Первый код

```php
use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\UserService;

// Работа с репозиториями
$newsRepo = new IblockRepository('news');
$news = $newsRepo->getList(['ACTIVE' => 'Y']);

// Использование сервисов
$userService = service(UserService::class);
$password = $userService->generatePassword([2]);
```

## Функции-хелперы

Модуль предоставляет глобальные функции-помощники для упрощения работы:

### service()

Получение сервиса из DI-контейнера с поддержкой типизации:

```php
// Автоматическое определение типа благодаря @template
$userService = service(UserService::class);
```

### firstNotEmpty()

Возвращает первое непустое значение или значение по умолчанию:

```php
$value = firstNotEmpty('default', $var1, $var2, $var3);
// Вернет первое непустое из $var1, $var2, $var3 или 'default'
```

### toFile()

Быстрое логирование данных в файл (для отладки):

```php
toFile($data); // Логирует в log.log
toFile(['user_id' => 123, 'action' => 'login']); // Логирует массив
```

### coreLog()

Логирование через встроенную систему Bitrix:

```php
coreLog('Сообщение об ошибке');
coreLog('Детальная информация', 10, true); // С трассировкой и аргументами
```

### isLighthouse()

Определяет, является ли запрос от Google Lighthouse:

```php
if (isLighthouse()) {
    // Специальная логика для аудита производительности
}
```

### isImport()

Проверяет, идет ли обмен с 1С:

```php
if (isImport()) {
    // Логика для импорта из 1С
}
```

### isCli()

Проверяет запущен ли скрипт из под cli

```php
if (isCli()) {
    // Скрипт выполняется из под cli
}
```

## Документация

📚 **[Полная документация](./docs/README.md)**

- [Dependency Injection](./docs/dependency-injection.md) - DI контейнер
- [Репозитории](./docs/repositories.md) - Работа с данными
- [Сервисы](./docs/services.md) - Все сервисы модуля
- [Контроллеры](./docs/controllers.md) - HTTP контроллеры
- [Конфигурация](./docs/configuration.md) - Система настроек

## Примеры использования

### Repository Pattern

```php
use Beeralex\Core\Repository\IblockRepository;

$newsRepo = new IblockRepository('news');

// Получение данных с фильтром и сортировкой
$items = $newsRepo->getList(
    ['ACTIVE' => 'Y'],
    [
        'select' => ['ID', 'NAME', 'DATE_CREATE'],
        'order' => ['DATE_CREATE' => 'DESC'],
        'limit' => 10
    ]
);

// Добавление элемента
$id = $newsRepo->add([
    'NAME' => 'Новость',
    'ACTIVE' => 'Y',
    'PROPERTY_VALUES' => [
        'CATEGORY' => 5
    ]
]);
```

### Dependency Injection

```php
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Service\PaginationService;

// Получение сервисов из DI-контейнера
$fileService = service(FileService::class);
$paginationService = service(PaginationService::class);

// Использование
$fileService->includeFile('catalog.index', ['productId' => 123]);
$pages = $paginationService->getPages(1, 10);
```

### API Controller

```php
use Beeralex\Core\Http\Controllers\ApiController;

class ProductController extends ApiController
{
    public function listAction(int $limit = 10): array
    {
        $repository = new IblockRepository('catalog');
        
        return [
            'items' => $repository->getList(
                ['ACTIVE' => 'Y'],
                ['limit' => $limit]
            )
        ];
    }
}
```

## Архитектура

```
┌─────────────────────────────────┐
│   Controllers (HTTP)            │  <- Обработка запросов
├─────────────────────────────────┤
│   Services (Business Logic)     │  <- Бизнес-логика
├─────────────────────────────────┤
│   Repositories (Data Access)    │  <- Доступ к данным
├─────────────────────────────────┤
│   Models/Entities               │  <- Данные
└─────────────────────────────────┘
```

## Требования

- PHP >= 8.2
- 1С-Битрикс >= 25.0.0 - рекомендуемая версия поддерживающая php 8.2
- Composer

установка модуля пройдет без ошибок, даже если версия битрикса ниже требуемой, но могут быть не стабильности в работе

## Лицензия

MIT

## Автор

Alexandr Belotsitsko (sanyabelyy020@gmail.com)
