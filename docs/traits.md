# Traits

Модуль предоставляет набор переиспользуемых трейтов для расширения функциональности классов.

## Cacheable

Трейт для работы с кешированием данных через Bitrix Cache API.

### Использование

```php
use Beeralex\Core\Traits\Cacheable;
use Beeralex\Core\Dto\CacheSettings;

class ProductService
{
    use Cacheable;
    
    public function getProducts(): array
    {
        $cacheSettings = new CacheSettings(
            time: 3600,              // Время жизни кеша в секундах
            key: 'products_list',    // Ключ кеша
            dir: '/products'         // Директория кеша
        );
        
        return $this->getCached($cacheSettings, function() {
            // Тяжелая операция получения данных
            $repository = new IblockRepository('catalog');
            return $repository->all(['ACTIVE' => 'Y']);
        });
    }
}
```

### CacheSettings

DTO для настроек кеширования:

```php
$settings = new CacheSettings(
    time: 3600,           // Время жизни кеша
    key: 'cache_key',     // Уникальный ключ
    dir: '/cache/dir'     // Директория для хранения
);

// Проверка, данные из кеша или нет
if ($settings->fromCache) {
    echo "Данные получены из кеша";
}

// Принудительный сброс кеша
$settings->abortCache = true;
```

### Методы трейта

**initCacheInstance()**

Инициализирует инстанс кеша Bitrix. Вызывается автоматически.

**getCached(CacheSettings $settings, callable $callback)**

Получает данные с кешированием:
- Если кеш валиден - возвращает закешированные данные
- Если кеш невалиден - вызывает callback и кеширует результат
- При ошибке в callback - сбрасывает кеш и пробрасывает исключение

```php
$data = $this->getCached($cacheSettings, function() {
    // Логика получения данных
    return $heavyOperation();
});

// Проверка источника данных
if ($cacheSettings->fromCache) {
    // Данные из кеша
}
```

### Сброс кеша

```php
// Условный сброс кеша
$data = $this->getCached($cacheSettings, function() use ($cacheSettings) {
    $result = fetchData();
    
    // Если данные пустые - не кешируем
    if (empty($result)) {
        $cacheSettings->abortCache = true;
    }
    
    return $result;
});
```

### Пример в репозитории

```php
use Beeralex\Core\Repository\AbstractRepository;
use Beeralex\Core\Traits\Cacheable;

class CachedProductRepository extends AbstractRepository
{
    use Cacheable;
    
    public function getFeaturedProducts(): array
    {
        $cacheSettings = new CacheSettings(
            time: 86400, // 24 часа
            key: 'featured_products',
            dir: '/catalog/featured'
        );
        
        return $this->getCached($cacheSettings, function() {
            return $this->getList(
                ['ACTIVE' => 'Y', 'PROPERTY_FEATURED' => 'Y'],
                ['order' => ['SORT' => 'ASC'], 'limit' => 10]
            );
        });
    }
}
```

## Resourceble

Трейт для создания DTO-объектов с доступом к данным через свойства, массивы и JSON.

### Возможности

- Доступ к полям как к свойствам объекта
- ArrayAccess - работа как с массивом
- JsonSerializable - автоматическая JSON сериализация
- Countable - подсчет элементов
- Iterable - итерация по элементам

### Использование

```php
use Beeralex\Core\Traits\Resourceble;

class UserDto
{
    use Resourceble;
    
    public function __construct(array $data)
    {
        $this->resource = $data;
    }
}

$user = new UserDto([
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Доступ через свойства
echo $user->name; // "John Doe"
echo $user->email; // "john@example.com"

// Доступ через массив
echo $user['name']; // "John Doe"

// Проверка существования
if (isset($user->email)) {
    echo $user->email;
}

// Изменение значений
$user->name = 'Jane Doe';
$user['email'] = 'jane@example.com';

// JSON сериализация
echo json_encode($user); // {"id":1,"name":"Jane Doe","email":"jane@example.com"}

// Получение массива
$array = $user->toArray();

// Подсчет элементов
echo count($user); // 3

// Итерация
foreach ($user as $key => $value) {
    echo "$key: $value\n";
}
```

### Вспомогательные методы

**getString(string $key): ?string**

Безопасное получение строкового значения:

```php
$email = $user->getString('email'); // string|null
$missing = $user->getString('phone'); // null
```

**isEmpty(): bool**

Проверка на пустоту:

```php
if ($user->isEmpty()) {
    echo "Нет данных";
}
```

**toArray(): array**

Конвертация в массив:

```php
$data = $user->toArray();
// ['id' => 1, 'name' => 'John', 'email' => 'john@example.com']
```

### Применение в Resource

Используется в базовом классе `Resource`:

```php
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $name
 */
class ProductResource extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => (int)$data['ID'],
            'name' => (string)$data['NAME'],
        ]);
    }
}

$product = ProductResource::make(['ID' => 1, 'NAME' => 'Product']);
echo $product->name; // Работает через Resourceble trait
```

## PathNormalizerTrait

Трейт для нормализации путей к файлам и директориям.

### Методы

**normalizeBaseDir(string $path): string**

Нормализует путь к директории:
- Использует `realpath()` для получения абсолютного пути
- Если путь - файл, возвращает директорию
- Обрабатывает относительные пути

```php
use Beeralex\Core\Traits\PathNormalizerTrait;

class FileService
{
    use PathNormalizerTrait;
    
    public function getLogDirectory(string $path): string
    {
        return $this->normalizeBaseDir($path);
    }
}

$service = new FileService();

// Абсолютный путь к директории
echo $service->getLogDirectory('/var/www/logs');
// /var/www/logs

// Путь к файлу - вернет директорию
echo $service->getLogDirectory('/var/www/logs/app.log');
// /var/www/logs

// Относительный путь
echo $service->getLogDirectory('../logs');
// /var/www/logs (после разрешения)
```

### Применение в FileLogger

```php
class FileLogger
{
    use PathNormalizerTrait;
    
    public function __construct(string $channel, string $baseDir)
    {
        $normalizedDir = $this->normalizeBaseDir($baseDir);
        if (!is_dir($normalizedDir)) {
            mkdir($normalizedDir, 0777, true);
        }
        $this->logFile = rtrim($normalizedDir, DIRECTORY_SEPARATOR) 
            . DIRECTORY_SEPARATOR . $channel . '.log';
    }
}
```

## TableManagerTrait

Трейт для управления таблицами ORM сущностей.

### Методы

**dropTable(): void**

Удаляет таблицу из базы данных:

```php
use Bitrix\Main\ORM\Data\DataManager;
use Beeralex\Core\Traits\TableManagerTrait;

class CustomTable extends DataManager
{
    use TableManagerTrait;
    
    public static function getTableName()
    {
        return 'custom_table';
    }
}

// Удаление таблицы
CustomTable::dropTable();
```

**createTable(): void**

Создает таблицу в базе данных:

```php
// Создание таблицы по схеме ORM
CustomTable::createTable();
```

**tableExists(): bool**

Проверяет существование таблицы:

```php
if (CustomTable::tableExists()) {
    echo "Таблица существует";
} else {
    CustomTable::createTable();
}
```

### Применение в миграциях

```php
use Bitrix\Main\ModuleManager;

class MigrationHelper
{
    public static function install()
    {
        if (!CustomTable::tableExists()) {
            CustomTable::createTable();
        }
    }
    
    public static function uninstall()
    {
        if (CustomTable::tableExists()) {
            CustomTable::dropTable();
        }
    }
}
```

### Пример ORM сущности с трейтом

```php
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;
use Beeralex\Core\Traits\TableManagerTrait;

class OrderStatusTable extends DataManager
{
    use TableManagerTrait;
    
    public static function getTableName()
    {
        return 'custom_order_status';
    }
    
    public static function getMap()
    {
        return [
            new Fields\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Fields\StringField('CODE', [
                'required' => true,
            ]),
            new Fields\StringField('NAME', [
                'required' => true,
            ]),
        ];
    }
}

// Использование
if (!OrderStatusTable::tableExists()) {
    OrderStatusTable::createTable();
}

$result = OrderStatusTable::add([
    'CODE' => 'new',
    'NAME' => 'Новый',
]);
```

### Безопасное управление схемой

```php
class SchemaManager
{
    public static function updateSchema()
    {
        // Пересоздание таблицы
        if (CustomTable::tableExists()) {
            CustomTable::dropTable();
        }
        CustomTable::createTable();
    }
    
    public static function ensureTableExists()
    {
        // Создание только если не существует
        if (!CustomTable::tableExists()) {
            CustomTable::createTable();
        }
    }
}
```

## Комбинирование трейтов

Трейты можно использовать вместе в одном классе:

```php
use Beeralex\Core\Traits\Cacheable;
use Beeralex\Core\Traits\PathNormalizerTrait;

class CachedFileService
{
    use Cacheable, PathNormalizerTrait;
    
    protected string $baseDir;
    
    public function __construct(string $baseDir)
    {
        $this->baseDir = $this->normalizeBaseDir($baseDir);
    }
    
    public function getFilesList(): array
    {
        $cacheSettings = new CacheSettings(
            time: 300,
            key: 'files_list_' . md5($this->baseDir),
            dir: '/files'
        );
        
        return $this->getCached($cacheSettings, function() {
            return scandir($this->baseDir);
        });
    }
}
```
