# Репозитории

Система репозиториев для работы с Bitrix ORM с поддержкой дженериков, декомпозиции запросов и типизации.

## Иерархия классов

```
RepositoryContract (interface)
    ↓
AbstractRepository<T of DataManager>
    ↓
Repository
    ↓
├─ IblockRepository (CompiledEntityRepositoryContract)
└─ HighloadRepository (CompiledEntityRepositoryContract)
```

---

## AbstractRepository

Базовый класс с дженериками для типобезопасной работы с ORM.

```php
<?php
namespace Beeralex\Core\Repository;

/**
 * @template T of DataManager
 */
abstract class AbstractRepository implements RepositoryContract
{
    /** @var class-string<T> */
    public readonly string $entityClass;
    public readonly QueryService $queryService;
    public bool $useDecompose;

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(string $entityClass, bool $useDecompose = false)
    {
        if (!is_subclass_of($entityClass, DataManager::class)) {
            throw new SystemException("Invalid entity class: {$entityClass}");
        }
        $this->entityClass = $entityClass;
        $this->useDecompose = $useDecompose;
        $this->queryService = service(QueryService::class);
    }

    public function query(): Query
    {
        return $this->entityClass::query();
    }

    public function getList(array $parameters = []): \Bitrix\Main\ORM\Query\Result
    {
        return $this->entityClass::getList($parameters);
    }
}
```

---

## Создание репозитория

### 1. Простой репозиторий

```php
<?php
namespace YourVendor\YourModule\Repository;

use Beeralex\Core\Repository\AbstractRepository;
use YourVendor\YourModule\Entity\UserTable;

/**
 * @extends AbstractRepository<UserTable>
 */
class UserRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(UserTable::class);
    }

    public function findById(int $id): ?array
    {
        return $this->query()
            ->where('ID', $id)
            ->fetch();
    }

    public function findActive(): array
    {
        return $this->query()
            ->where('ACTIVE', 'Y')
            ->fetchAll();
    }
}
```

### 2. Репозиторий для инфоблоков

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

    public function findByCategory(int $categoryId): array
    {
        return $this->query()
            ->where('IBLOCK_SECTION_ID', $categoryId)
            ->where('ACTIVE', 'Y')
            ->setOrder(['SORT' => 'ASC'])
            ->fetchAll();
    }

    public function findWithPrice(): array
    {
        return $this->query()
            ->registerRuntimeField('PRICE', [
                'data_type' => PriceTable::class,
                'reference' => ['=this.ID' => 'ref.PRODUCT_ID']
            ])
            ->where('PRICE.CATALOG_GROUP_ID', 1)
            ->fetchAll();
    }
}
```

### 3. Репозиторий для Highload

```php
<?php
namespace YourVendor\YourModule\Repository;

use Beeralex\Core\Repository\HighloadRepository;
use YourVendor\YourModule\Entity\SettingsTable;

class SettingsRepository extends HighloadRepository
{
    public function __construct()
    {
        parent::__construct(SettingsTable::class);
    }

    public function findByKey(string $key): ?array
    {
        return $this->query()
            ->where('UF_KEY', $key)
            ->fetch();
    }
}
```

---

## Декомпозиция запросов

Для оптимизации сложных запросов с множеством связей:

```php
public function __construct()
{
    parent::__construct(
        entityClass: ProductTable::class,
        useDecompose: true  // Включить декомпозицию
    );
}
```

Это автоматически разбивает один сложный запрос на несколько простых.

---

## QueryService

Сервис для построения запросов с дополнительными возможностями:

```php
$this->queryService->createQuery($this->entityClass)
    ->setSelect(['ID', 'NAME', 'ACTIVE'])
    ->setFilter(['ACTIVE' => 'Y'])
    ->setOrder(['SORT' => 'ASC'])
    ->setLimit(10)
    ->setOffset(20);
```

---

## Примеры использования

### Базовые операции

```php
<?php
$repository = new ProductRepository();

// Получить по ID
$product = $repository->query()
    ->where('ID', 123)
    ->fetch();

// Список с фильтром
$products = $repository->query()
    ->whereIn('ID', [1, 2, 3])
    ->where('ACTIVE', 'Y')
    ->fetchAll();

// С сортировкой и лимитом
$products = $repository->query()
    ->setOrder(['SORT' => 'ASC', 'NAME' => 'ASC'])
    ->setLimit(10)
    ->fetchAll();

// Подсчет
$count = $repository->query()
    ->where('ACTIVE', 'Y')
    ->queryCountTotal();
```

### Работа со связями

```php
public function findWithSections(): array
{
    return $this->query()
        ->registerRuntimeField('SECTION', [
            'data_type' => SectionTable::class,
            'reference' => ['=this.IBLOCK_SECTION_ID' => 'ref.ID']
        ])
        ->setSelect(['*', 'SECTION'])
        ->fetchAll();
}
```

### Сложные фильтры

```php
public function findByFilter(array $filter): array
{
    $query = $this->query();
    
    if (!empty($filter['NAME'])) {
        $query->whereLike('NAME', '%' . $filter['NAME'] . '%');
    }
    
    if (!empty($filter['PRICE_FROM'])) {
        $query->where('PRICE', '>=', $filter['PRICE_FROM']);
    }
    
    if (!empty($filter['PRICE_TO'])) {
        $query->where('PRICE', '<=', $filter['PRICE_TO']);
    }
    
    return $query->fetchAll();
}
```

### Пагинация

```php
public function findPaginated(int $page = 1, int $perPage = 20): array
{
    $offset = ($page - 1) * $perPage;
    
    return $this->query()
        ->setLimit($perPage)
        ->setOffset($offset)
        ->fetchAll();
}

public function getTotalCount(): int
{
    return $this->query()->queryCountTotal();
}
```

---

## Интерфейсы

### RepositoryContract

```php
interface RepositoryContract
{
    public function query(): Query;
    public function getList(array $parameters = []): \Bitrix\Main\ORM\Query\Result;
}
```

### CompiledEntityRepositoryContract

Маркер-интерфейс для репозиториев с compiled entity (IblockRepository, HighloadRepository).

---

## Регистрация в DI

```php
<?php
use YourVendor\YourModule\Repository\ProductRepository;

return [
    'services' => [
        'value' => [
            ProductRepository::class => [
                'className' => ProductRepository::class
            ],
        ],
    ],
];
```

Использование:

```php
$productRepository = service(ProductRepository::class);
$products = $productRepository->findActive();
```

---

## Best Practices

### 1. Один репозиторий = одна сущность

```php
// ✅ Правильно
class ProductRepository extends IblockRepository { }
class OrderRepository extends IblockRepository { }

// ❌ Неправильно
class UniversalRepository { } // для всего
```

### 2. Инкапсулируйте логику запросов

```php
// ✅ Правильно
$products = $productRepository->findActiveInStock();

// ❌ Неправильно
$products = $productRepository->query()
    ->where('ACTIVE', 'Y')
    ->where('QUANTITY', '>', 0)
    ->fetchAll();
```

### 3. Используйте типизацию

```php
/**
 * @extends AbstractRepository<ProductTable>
 */
class ProductRepository extends AbstractRepository
{
    public function findById(int $id): ?array { }
    
    /** @return array[] */
    public function findAll(): array { }
}
```

### 4. Декомпозиция для сложных запросов

```php
// Если запрос с 5+ JOIN и работает медленно
public function __construct()
{
    parent::__construct(
        entityClass: ProductTable::class,
        useDecompose: true
    );
}
```

---

## Примеры из реальных проектов

### Репозиторий пользователей

```php
<?php
namespace Beeralex\User;

use Beeralex\Core\Repository\AbstractRepository;

class UserRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(UserTable::class);
    }

    public function getByEmail(string $email): ?array
    {
        return $this->query()
            ->where('EMAIL', $email)
            ->fetch();
    }

    public function getByPhone(string $phone): ?array
    {
        return $this->query()
            ->where('PHONE_AUTH.PHONE_NUMBER', $phone)
            ->fetch();
    }

    public function getById(int $userId): ?array
    {
        return $this->query()
            ->where('ID', $userId)
            ->fetch();
    }
}
```

### Репозиторий локаций

```php
<?php
namespace Beeralex\Core\Repository;

class LocationRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(LocationTable::class);
    }

    public function findByCode(string $code): ?array
    {
        return $this->query()
            ->where('CODE', $code)
            ->fetch();
    }

    public function findByType(string $type): array
    {
        return $this->query()
            ->where('TYPE.CODE', $type)
            ->fetchAll();
    }
}
```
