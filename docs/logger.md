# Logger

Пакет Logger предоставляет PSR-3 совместимую систему логирования в файлы.

## FileLogger

PSR-3 логгер для записи логов в файлы.

### Создание логгера

```php
use Beeralex\Core\Logger\FileLogger;

// Канал и директория логов
$logger = new FileLogger(
    channel: 'app',                              // Имя канала (имя файла)
    baseDir: $_SERVER['DOCUMENT_ROOT'] . '/local/logs'  // Директория
);

// Файл логов: /local/logs/app.log
```

### Уровни логирования

FileLogger поддерживает все PSR-3 уровни:

```php
// EMERGENCY - система неработоспособна
$logger->emergency('Database server is down');

// ALERT - требуется немедленное действие
$logger->alert('Website is down, immediate action required');

// CRITICAL - критическая ситуация
$logger->critical('Application component unavailable');

// ERROR - ошибки выполнения
$logger->error('User registration failed', ['user_id' => 123]);

// WARNING - предупреждения
$logger->warning('API rate limit exceeded');

// NOTICE - нормальное, но значимое событие
$logger->notice('User logged in', ['user_id' => 456]);

// INFO - информационные сообщения
$logger->info('Order created', ['order_id' => 789]);

// DEBUG - отладочная информация
$logger->debug('Cache miss for key', ['key' => 'products_list']);
```

### Контекстные данные

Передача дополнительных данных в контексте:

```php
$logger->error('Payment processing failed', [
    'order_id' => 12345,
    'amount' => 1500.00,
    'payment_method' => 'credit_card',
    'error_code' => 'INSUFFICIENT_FUNDS',
]);

// Лог файл:
// [2025-11-28 10:30:45] error: Payment processing failed 
// Context: Array
// (
//     [order_id] => 12345
//     [amount] => 1500
//     [payment_method] => credit_card
//     [error_code] => INSUFFICIENT_FUNDS
// )
```

### Интерполяция сообщений

Placeholder в фигурных скобках заменяются значениями из контекста:

```php
$logger->info('User {username} logged in from {ip}', [
    'username' => 'john_doe',
    'ip' => '192.168.1.100',
    'user_id' => 123,
]);

// Результат: User john_doe logged in from 192.168.1.100
// Context: [user_id => 123]
```

### Формат записи

Формат лога:
```
[YYYY-MM-DD HH:MM:SS] level: message
Context: array_data
```

Пример:
```
[2025-11-28 10:30:45] error: Payment processing failed 
Context: Array
(
    [order_id] => 12345
    [amount] => 1500
)

[2025-11-28 10:31:12] info: User john_doe logged in from 192.168.1.100 
Context: Array
(
    [user_id] => 123
)
```

## FileLoggerFactory

Фабрика для создания логгеров с общей директорией.

### Создание фабрики

```php
use Beeralex\Core\Logger\FileLoggerFactory;

$factory = new FileLoggerFactory(
    baseDir: $_SERVER['DOCUMENT_ROOT'] . '/local/logs'
);
```

### Создание каналов

```php
// Разные каналы логирования
$appLogger = $factory->channel('app');
$apiLogger = $factory->channel('api');
$paymentLogger = $factory->channel('payment');
$errorLogger = $factory->channel('errors');

// Логи пишутся в соответствующие файлы:
// /local/logs/app.log
// /local/logs/api.log
// /local/logs/payment.log
// /local/logs/errors.log

$appLogger->info('Application started');
$apiLogger->debug('API request', ['endpoint' => '/api/products']);
$paymentLogger->error('Payment failed', ['order_id' => 123]);
```

### Регистрация в DI

```php
use Bitrix\Main\DI\ServiceLocator;
use Beeralex\Core\Logger\FileLoggerFactory;
use Beeralex\Core\Logger\LoggerFactoryContract;

ServiceLocator::getInstance()->addInstanceLazy(
    LoggerFactoryContract::class,
    [
        'className' => FileLoggerFactory::class,
        'constructorParams' => [
            'baseDir' => $_SERVER['DOCUMENT_ROOT'] . '/local/logs',
        ],
    ]
);

// Использование через DI
$factory = service(LoggerFactoryContract::class);
$logger = $factory->channel('app');
```

## Практическое применение

### Логирование в сервисах

```php
use Beeralex\Core\Logger\FileLoggerFactory;
use Psr\Log\LoggerInterface;

class OrderService
{
    protected LoggerInterface $logger;
    
    public function __construct()
    {
        $factory = service(FileLoggerFactory::class);
        $this->logger = $factory->channel('orders');
    }
    
    public function createOrder(array $data): int
    {
        $this->logger->info('Creating order', ['data' => $data]);
        
        try {
            $orderId = $this->processOrder($data);
            
            $this->logger->info('Order created successfully', [
                'order_id' => $orderId,
                'amount' => $data['amount'],
            ]);
            
            return $orderId;
        } catch (\Exception $e) {
            $this->logger->error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
            
            throw $e;
        }
    }
}
```

### Логирование API запросов

```php
use Beeralex\Core\Http\Controllers\ApiController;
use Psr\Log\LoggerInterface;

class ProductController extends ApiController
{
    protected LoggerInterface $logger;
    
    public function __construct()
    {
        parent::__construct();
        
        $factory = service(FileLoggerFactory::class);
        $this->logger = $factory->channel('api');
    }
    
    public function listAction(): array
    {
        $startTime = microtime(true);
        
        $this->logger->info('API request: GET /api/product/list/', [
            'user_id' => $this->getCurrentUser()?->getId(),
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]);
        
        $products = $this->productRepository->getList();
        
        $duration = microtime(true) - $startTime;
        
        $this->logger->info('API response: GET /api/product/list/', [
            'count' => count($products),
            'duration' => round($duration, 3) . 's',
        ]);
        
        return ['items' => $products];
    }
}
```

### Логирование ошибок с уровнями

```php
use Beeralex\Core\Logger\FileLoggerFactory;
use Psr\Log\LoggerInterface;

class PaymentService
{
    protected LoggerInterface $logger;
    
    public function __construct()
    {
        $factory = service(FileLoggerFactory::class);
        $this->logger = $factory->channel('payment');
    }
    
    public function processPayment(int $orderId, float $amount): bool
    {
        $this->logger->debug('Starting payment processing', [
            'order_id' => $orderId,
            'amount' => $amount,
        ]);
        
        if ($amount <= 0) {
            $this->logger->warning('Invalid payment amount', [
                'order_id' => $orderId,
                'amount' => $amount,
            ]);
            return false;
        }
        
        try {
            $result = $this->chargeCard($orderId, $amount);
            
            if (!$result['success']) {
                $this->logger->error('Payment declined', [
                    'order_id' => $orderId,
                    'reason' => $result['error'],
                ]);
                return false;
            }
            
            $this->logger->info('Payment successful', [
                'order_id' => $orderId,
                'transaction_id' => $result['transaction_id'],
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->critical('Payment processing exception', [
                'order_id' => $orderId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}
```

### Структурированное логирование

```php
use Psr\Log\LoggerInterface;

class AuditLogger
{
    protected LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function logUserAction(string $action, int $userId, array $details = []): void
    {
        $this->logger->notice("User action: {action}", [
            'action' => $action,
            'user_id' => $userId,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details,
        ]);
    }
}

// Использование
$audit = new AuditLogger($factory->channel('audit'));

$audit->logUserAction('login', 123, ['method' => 'password']);
$audit->logUserAction('order_create', 123, ['order_id' => 456, 'amount' => 1500]);
$audit->logUserAction('profile_update', 123, ['fields' => ['email', 'phone']]);
```

### Ротация логов

Для ротации логов используйте системные инструменты (logrotate):

```bash
# /etc/logrotate.d/bitrix-app
/var/www/html/local/logs/*.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
    create 0644 www-data www-data
}
```

### Условное логирование

```php
class DebugLogger
{
    protected LoggerInterface $logger;
    protected bool $debugMode;
    
    public function __construct(LoggerInterface $logger, bool $debugMode = false)
    {
        $this->logger = $logger;
        $this->debugMode = $debugMode;
    }
    
    public function debug(string $message, array $context = []): void
    {
        if ($this->debugMode) {
            $this->logger->debug($message, $context);
        }
    }
    
    public function error(string $message, array $context = []): void
    {
        // Ошибки логируем всегда
        $this->logger->error($message, $context);
    }
}

$debugMode = defined('DEBUG_MODE') && DEBUG_MODE === true;
$logger = new DebugLogger($factory->channel('app'), $debugMode);

$logger->debug('Cache miss'); // Логируется только если DEBUG_MODE
$logger->error('Database error'); // Логируется всегда
```

### Агрегация логов из разных источников

```php
class MultiChannelLogger
{
    protected array $channels = [];
    
    public function __construct(FileLoggerFactory $factory)
    {
        $this->channels['app'] = $factory->channel('app');
        $this->channels['error'] = $factory->channel('errors');
        $this->channels['performance'] = $factory->channel('performance');
    }
    
    public function log(string $level, string $message, array $context = []): void
    {
        // В основной канал
        $this->channels['app']->log($level, $message, $context);
        
        // Ошибки дополнительно в отдельный файл
        if (in_array($level, ['error', 'critical', 'alert', 'emergency'])) {
            $this->channels['error']->log($level, $message, $context);
        }
        
        // Производительность
        if (isset($context['duration']) && $context['duration'] > 1.0) {
            $this->channels['performance']->warning(
                'Slow operation: ' . $message,
                $context
            );
        }
    }
}
```
