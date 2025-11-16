# API сервисы

Базовый класс `ApiService` предоставляет типизированный способ работы с внешними API с поддержкой кэширования, логирования и обработки ошибок.

## Основные возможности

- ✅ Типизация через дженерики (Options + ClientService)
- ✅ Встроенное кэширование запросов
- ✅ Автоматическое логирование ошибок
- ✅ GET/POST методы с headers
- ✅ Обработка JSON ответов

---

## Базовый класс

```php
<?php
namespace Beeralex\Core\Service\Api;

/**
 * @template T of AbstractOptions
 * @template U of ClientService
 */
abstract class ApiService
{
    /** @var U */
    protected readonly ClientService $clientService;
    
    /** @var T */
    protected readonly AbstractOptions $options;

    /**
     * @param T $moduleOptions
     * @param U $clientService
     */
    public function __construct(AbstractOptions $moduleOptions, ClientService $clientService)
    {
        $this->clientService = $clientService;
        $this->options = $moduleOptions;
    }

    protected function get(Uri $uri, ?array $data = null, ?array $headers = null, ?CacheSettingsDto $cacheSettings = null): array
    protected function post(Uri $uri, mixed $data = null, ?array $headers = null, ?CacheSettingsDto $cacheSettings = null): array
    public function log(string $text, int $traceDepth = 6, bool $showArgs = false): void
}
```

---

## Создание API сервиса

### 1. Создайте Options класс

```php
<?php
namespace YourVendor\YourModule;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    public readonly string $apiUrl;
    public readonly string $apiKey;
    public readonly bool $logsEnable;

    protected function mapOptions(array $options): void
    {
        $this->apiUrl = $options['api_url'] ?? '';
        $this->apiKey = $options['api_key'] ?? '';
        $this->logsEnable = ($options['logs_enable'] ?? '') === 'Y';
    }

    public function getModuleId(): string
    {
        return 'yourvendor.yourmodule';
    }
}
```

### 2. Создайте ClientService (опционально)

Если нужна специальная настройка HTTP клиента:

```php
<?php
namespace YourVendor\YourModule\Services;

use Beeralex\Core\Service\Api\ClientService as BaseClientService;

class ClientService extends BaseClientService
{
    public function __construct()
    {
        parent::__construct();
        $this->setTimeout(30);
        $this->setHeaders([
            'User-Agent' => 'MyApp/1.0',
            'Accept' => 'application/json'
        ]);
    }
}
```

### 3. Создайте API сервис

```php
<?php
namespace YourVendor\YourModule\Services;

use Beeralex\Core\Service\Api\ApiService as CoreApiService;
use Beeralex\Core\Dto\CacheSettingsDto;
use Bitrix\Main\Web\Uri;
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
        
        // Дополнительная настройка клиента
        $this->clientService->setHeaders([
            'Authorization' => 'Bearer ' . $this->options->apiKey
        ]);
    }

    /**
     * Получить список пользователей
     */
    public function getUsers(?int $page = null): array
    {
        $uri = new Uri($this->options->apiUrl . '/users');
        
        $cacheSettings = new CacheSettingsDto(
            ttl: 3600,
            cacheId: 'api_users_' . ($page ?? 1)
        );
        
        return $this->get(
            uri: $uri,
            data: $page ? ['page' => $page] : null,
            cacheSettings: $cacheSettings
        );
    }

    /**
     * Создать пользователя
     */
    public function createUser(array $userData): array
    {
        $uri = new Uri($this->options->apiUrl . '/users');
        
        return $this->post(
            uri: $uri,
            data: $userData,
            headers: ['Content-Type' => 'application/json']
        );
    }
}
```

---

## Кэширование

Используйте `CacheSettingsDto` для настройки кэша:

```php
use Beeralex\Core\Dto\CacheSettingsDto;

$cacheSettings = new CacheSettingsDto(
    ttl: 3600,                          // Время жизни (секунды)
    cacheId: 'unique_cache_key',        // Уникальный ключ
    cacheDir: '/api/mymodule'           // Директория кэша
);

$result = $this->get($uri, null, null, $cacheSettings);
```

Без `CacheSettingsDto` кэш не используется.

---

## Обработка ошибок

Сервис автоматически обрабатывает исключения:

```php
try {
    $users = $apiService->getUsers();
} catch (\Beeralex\Core\Exceptions\ApiClientException $e) {
    // HTTP ошибка (4xx, 5xx)
    $statusCode = $e->getCode();
    $message = $e->getMessage();
} catch (\Throwable $e) {
    // Другие ошибки
}
```

Логирование происходит автоматически при включенном `logsEnable`:

```php
// В Options
public readonly bool $logsEnable;

// В ApiService автоматически
if ($this->options->logsEnable) {
    \AddMessage2Log($errorMessage, $this->options->moduleId);
}
```

---

## Типизация в наследниках

Используйте `@property-read` для правильной типизации:

```php
/**
 * @property-read Options $options
 * @property-read ClientService $clientService
 */
class ApiService extends CoreApiService
{
    // Теперь IDE покажет правильные типы при:
    // $this->options->apiKey
    // $this->clientService->setTimeout()
}
```

---

## Регистрация в DI

В `.settings.php` модуля:

```php
<?php
use YourVendor\YourModule\Options;
use YourVendor\YourModule\Services\ApiService;
use YourVendor\YourModule\Services\ClientService;

return [
    'services' => [
        'value' => [
            Options::class => [
                'className' => Options::class
            ],
            ClientService::class => [
                'className' => ClientService::class
            ],
            ApiService::class => [
                'className' => ApiService::class
            ],
        ],
    ],
];
```

Использование:

```php
$apiService = service(YourVendor\YourModule\Services\ApiService::class);
$users = $apiService->getUsers();
```

---

## Примеры использования

### GET запрос с параметрами

```php
public function searchProducts(string $query, int $limit = 10): array
{
    $uri = new Uri($this->options->apiUrl . '/products/search');
    
    return $this->get($uri, [
        'q' => $query,
        'limit' => $limit
    ]);
}
```

### POST с JSON

```php
public function updateOrder(int $orderId, array $data): array
{
    $uri = new Uri($this->options->apiUrl . '/orders/' . $orderId);
    
    return $this->post(
        uri: $uri,
        data: json_encode($data),
        headers: ['Content-Type' => 'application/json']
    );
}
```

### С кастомными headers

```php
public function getProtectedResource(): array
{
    $uri = new Uri($this->options->apiUrl . '/protected');
    
    return $this->get($uri, null, [
        'X-Api-Key' => $this->options->apiKey,
        'X-Client-Id' => $this->options->clientId
    ]);
}
```

---

## ClientService методы

```php
// Настройка таймаута
$this->clientService->setTimeout(30);

// Установка headers
$this->clientService->setHeaders([
    'Authorization' => 'Bearer token',
    'Accept' => 'application/json'
]);

// POST данные
$this->clientService->setPostData($data);

// Отключение SSL проверки (только для тестов!)
$this->clientService->disableSslVerification();

// Получение результата
$result = $this->clientService->request(Method::GET, $uri);
$data = $result->getResult();
$status = $this->clientService->getStatus();
$error = $this->clientService->getError();
```
