# Dependency Injection (DI)

Модуль `beeralex.core` использует DI-контейнер Bitrix для управления зависимостями и предоставляет удобную глобальную функцию `service()` для доступа к сервисам.

## Содержание

- [Функция service()](#функция-service)
- [Регистрация сервисов](#регистрация-сервисов)
- [Типы регистрации](#типы-регистрации)
- [Примеры использования](#примеры-использования)
- [Лучшие практики](#лучшие-практики)

## Функция service()

Глобальная функция для получения сервисов из DI-контейнера:

```php
/**
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function service(string $class)
{
    return \Bitrix\Main\DI\ServiceLocator::getInstance()->get($class);
}
```

### Использование

```php
use Beeralex\Core\Service\IblockService;
use Beeralex\Core\Service\UserService;
use Beeralex\Core\Repository\IblockRepository;

// Получение сервиса
$iblockService = service(IblockService::class);

// Автоматическое определение типа благодаря PHPDoc
$userService = service(UserService::class); // IDE знает, что это UserService
```

### Преимущества

1. **Краткость** - `service(Class::class)` вместо `ServiceLocator::getInstance()->get(...)`
2. **Type-safe** - IDE автоматически определяет тип возвращаемого значения
3. **Autocompletion** - Автодополнение методов полученного сервиса
4. **Единая точка доступа** - Все зависимости получаются одинаково

## Регистрация сервисов

Сервисы регистрируются в файле `.settings.php` модуля.

### Структура .settings.php

```php
<?php

use Beeralex\Core\Service\IblockService;

return [
    'services' => [
        'value' => [
            // Регистрация сервисов
            IblockService::class => [
                'className' => IblockService::class
            ],
        ]
    ]
];
```

## Типы регистрации

### 1. По имени класса (простая регистрация)

Используется когда конструктор не требует параметров или они могут быть автоматически разрешены:

```php
IblockService::class => [
    'className' => IblockService::class
]
```

**Когда использовать:**
- Класс не имеет зависимостей
- Все зависимости могут быть автоматически разрешены из контейнера

**Пример сервиса:**

```php
class IblockService
{
    public function __construct()
    {
        // Нет зависимостей
    }
}
```

### 2. С использованием конструктора (сложная регистрация)

Используется когда нужно явно передать параметры в конструктор:

```php
LocationService::class => [
    'constructor' => static function () {
        return new LocationService(
            locationRepository: service(LocationRepository::class)
        );
    }
]
```

**Когда использовать:**
- Нужно передать конкретные значения
- Зависимости требуют настройки
- Требуется получить другие сервисы из контейнера

**Пример сервиса:**

```php
class LocationService
{
    public function __construct(
        protected LocationRepository $locationRepository
    ) {}
}
```

### 3. Фабричная регистрация

Используется для создания сервисов с использованием фабрик:

```php
UserRepository::class => [
    'constructor' => static function () {
        return new UserRepository(
            service(UserFactoryContract::class),
            service(FileService::class)
        );
    }
]
```

### 4. Регистрация через интерфейс

Позволяет заменять реализацию без изменения кода:

```php
UserRepositoryContract::class => [
    'constructor' => static function () {
        return new UserRepository(...);
    }
]
```

**Использование:**

```php
// В коде используется интерфейс
$userRepo = service(UserRepositoryContract::class);

// Фактически получаем UserRepository
```

## Примеры использования

### Пример 1: Простой сервис

```php
// Регистрация в .settings.php
FileService::class => [
    'className' => FileService::class
]

// Использование
$fileService = service(FileService::class);
$fileService->includeFile('catalog.index');
```

### Пример 2: Сервис с зависимостями

```php
// Регистрация в .settings.php
AuthService::class => [
    'constructor' => static function () {
        return new AuthService(
            authManager: service(AuthManager::class),
            jwtManager: service(JwtTokenManager::class)
        );
    }
]

// Использование
$authService = service(AuthService::class);
$result = $authService->login($credentials);
```

### Пример 3: Синглтон-сервис

По умолчанию все сервисы в контейнере - синглтоны:

```php
$service1 = service(IblockService::class);
$service2 = service(IblockService::class);

var_dump($service1 === $service2); // true - это один и тот же объект
```

### Пример 4: Условная регистрация

```php
CatalogService::class => [
    'constructor' => static function () {
        // Условная логика
        if (Loader::includeModule('catalog')) {
            return new CatalogService();
        }
        throw new \RuntimeException('Catalog module not installed');
    }
]
```

## Примеры из модулей проекта

### beeralex.user

```php
// .settings.php
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthManager;

return [
    'services' => [
        'value' => [
            AuthService::class => [
                'constructor' => static function () {
                    return new AuthService(
                        authManager: service(AuthManager::class),
                        jwtManager: service(JwtTokenManager::class)
                    );
                }
            ],
        ]
    ]
];

// Использование в контроллере
class AuthController extends ApiController
{
    protected AuthService $authService;

    protected function init(): void
    {
        parent::init();
        $this->authService = service(AuthService::class);
    }

    public function loginAction(AuthCredentialsDto $credentials): array
    {
        return $this->authService->login($credentials);
    }
}
```

### beeralex.catalog

```php
// .settings.php
use Beeralex\Catalog\Services\BasketService;

return [
    'services' => [
        'value' => [
            BasketService::class => [
                'constructor' => static function () {
                    return new BasketService(
                        priceService: service(PriceService::class),
                        catalogService: service(CatalogService::class)
                    );
                }
            ],
        ]
    ]
];

// Использование
$basketService = service(BasketService::class);
$items = $basketService->getBasketItems();
```

### beeralex.reviews

```php
// В сервисе
class ReviewsService
{
    protected Options $options;

    public function __construct()
    {
        $this->options = service(Options::class);
    }
}

// В любом месте
$reviewsService = service(ReviewsService::class);
```

## Лучшие практики

### 1. Используйте интерфейсы

```php
// Плохо
service(UserRepository::class);

// Хорошо  
service(UserRepositoryContract::class);
```

**Преимущества:**
- Легко заменить реализацию
- Улучшает тестируемость
- Следует принципу DIP (Dependency Inversion Principle)

### 2. Избегайте создания сервисов вручную

```php
// Плохо
$service = new MyService(new Dependency1(), new Dependency2());

// Хорошо
$service = service(MyService::class);
```

### 3. Инъекция зависимостей через конструктор

```php
// Плохо
class MyService
{
    public function doSomething()
    {
        $iblockService = service(IblockService::class);
        // ...
    }
}

// Хорошо
class MyService
{
    public function __construct(
        protected IblockService $iblockService
    ) {}

    public function doSomething()
    {
        $this->iblockService->...
    }
}
```

### 4. Ленивая загрузка

Сервисы создаются только при первом обращении:

```php
// Регистрация не создает объект
IblockService::class => [
    'className' => IblockService::class
]

// Объект создается здесь при первом вызове
$service = service(IblockService::class);
```

### 5. Избегайте циклических зависимостей

```php
// Плохо - циклическая зависимость
class ServiceA
{
    public function __construct(ServiceB $serviceB) {}
}

class ServiceB
{
    public function __construct(ServiceA $serviceA) {}
}

// Хорошо - используйте события или медиаторы
```

### 6. Документируйте возвращаемые типы

```php
/** @var IblockService $iblockService */
$iblockService = service(IblockService::class);
```

Или лучше используйте строгую типизацию:

```php
$iblockService = service(IblockService::class);
// IDE автоматически определит тип благодаря @template в функции service()
```

## Отладка

### Проверка зарегистрированных сервисов

```php
$locator = \Bitrix\Main\DI\ServiceLocator::getInstance();

// Проверить, зарегистрирован ли сервис
if ($locator->has(MyService::class)) {
    echo "Сервис зарегистрирован";
}
```

### Получение всех сервисов

```php
// Внутренний метод, используйте осторожно
$services = $locator->__debugInfo();
```

## Распространенные ошибки

### 1. Сервис не зарегистрирован

```php
// Ошибка: Service 'MyService' is not registered
$service = service(MyService::class);
```

**Решение:** Зарегистрируйте сервис в `.settings.php`

### 2. Зависимость не может быть разрешена

```php
// Ошибка при простой регистрации, если конструктор требует параметры
MyService::class => [
    'className' => MyService::class // Не сработает, если есть обязательные параметры
]
```

**Решение:** Используйте `constructor`:

```php
MyService::class => [
    'constructor' => static function () {
        return new MyService($param);
    }
]
```

### 3. Несоответствие типов

```php
// Интерфейс зарегистрирован, но возвращается не тот класс
MyServiceContract::class => [
    'className' => WrongImplementation::class // Не имплементирует MyServiceContract
]
```

**Решение:** Убедитесь, что класс реализует нужный интерфейс

## Дополнительные функции

### toFile() - Логирование в файл log.log

```php
toFile(['user_id' => 123, 'action' => 'login']);
toFile('Simple message');
```

### coreLog() - Логирование в логи Bitrix

```php
/**
 * Логирование с указанием модуля
 */
coreLog('Error message', 6, false);
```

## Заключение

Dependency Injection в `beeralex.core`:
- Упрощает управление зависимостями
- Улучшает тестируемость кода
- Способствует слабой связанности компонентов
- Предоставляет единую точку доступа к сервисам

Используйте функцию `service()` везде, где это возможно, и регистрируйте все сервисы в `.settings.php` вашего модуля.
