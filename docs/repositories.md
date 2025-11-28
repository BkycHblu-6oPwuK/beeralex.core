# Репозитории (Repositories)

Репозитории предоставляют удобный API для работы с данными Bitrix (инфоблоки, хайлоад-блоки, разделы). Они инкапсулируют логику доступа к данным и предоставляют чистый интерфейс для работы с ними.

## Содержание

- [Обзор](#обзор)
- [Типы репозиториев](#типы-репозиториев)
- [IblockRepository](#iblockrepository)
- [IblockSectionRepository](#iblocksectionrepository)
- [HighloadRepository](#highloadrepository)
- [AbstractRepository](#abstractrepository)
- [Создание собственных репозиториев](#создание-собственных-репозиториев)
- [Продвинутые возможности](#продвинутые-возможности)

## Обзор

### Философия Repository Pattern

Repository Pattern отделяет логику доступа к данным от бизнес-логики приложения:

```
Controller/Service  →  Repository  →  Data Source (Bitrix ORM)
   (Бизнес-логика)    (Доступ к данным)  (Хранилище данных)
```

### Преимущества

1. **Упрощение работы с данными** - Единый API для всех операций
2. **Повторное использование** - Одна логика для всех контроллеров
3. **Тестируемость** - Легко мокировать репозитории
4. **Кеширование** - Встроенная поддержка кеширования
5. **Безопасность типов** - Строгая типизация PHP 8.1+

## Типы репозиториев

### Repository
Базовый репозиторий для работы с любыми сущностями Bitrix ORM

### IblockRepository
Специализированный репозиторий для инфоблоков

### IblockSectionRepository
Репозиторий для разделов инфоблоков

### HighloadRepository
Репозиторий для хайлоад-блоков

## IblockRepository

Репозиторий для работы с элементами инфоблоков.

### Создание репозитория

```php
use Beeralex\Core\Repository\IblockRepository;

// По символьному коду
$newsRepo = new IblockRepository('news');

// По ID
$newsRepo = new IblockRepository(5);
```

### Базовые операции

#### Получение списка элементов

```php
// Все элементы
$items = $newsRepo->all();

// С фильтром
$items = $newsRepo->all([
    'ACTIVE' => 'Y',
    '>=DATE_CREATE' => '2025-01-01'
]);

// С выбором полей
$items = $newsRepo->all(
    ['ACTIVE' => 'Y'],
    ['ID', 'NAME', 'PREVIEW_TEXT']
);

// С сортировкой
$items = $newsRepo->all(
    ['ACTIVE' => 'Y'],
    ['ID', 'NAME'],
    ['DATE_CREATE' => 'DESC']
);
```

#### Получение одного элемента

```php
// По ID
$item = $newsRepo->getById(123);

// По условию
$item = $newsRepo->one([
    'CODE' => 'latest-news'
]);

// С выбором полей
$item = $newsRepo->one(
    ['CODE' => 'latest-news'],
    ['ID', 'NAME', 'DETAIL_TEXT']
);
```

#### Получение списка через getList (Bitrix ORM)

```php
// getList использует стандартные параметры Bitrix ORM
$result = $newsRepo->getList([
    'filter' => ['ACTIVE' => 'Y'],
    'select' => ['ID', 'NAME', 'PROPERTY_CATEGORY'],
    'limit' => 10,
    'offset' => 0,
    'order' => ['DATE_CREATE' => 'DESC'],
    'cache' => ['ttl' => 3600]
]);

$items = $result->fetchAll();
```

### Операции записи

#### Добавление элемента

```php
// Старый API (через CIBlockElement)
$id = $newsRepo->add([
    'NAME' => 'Новая новость',
    'ACTIVE' => 'Y',
    'PREVIEW_TEXT' => 'Краткое описание',
    'PROPERTY_VALUES' => [
        'CATEGORY' => 5,
        'TAGS' => ['новость', 'важное']
    ]
]);

// Новый API (через ORM) - если доступен
$id = $newsRepo->save([
    'NAME' => 'Новая новость',
    'ACTIVE' => 'Y'
]);
```

#### Обновление элемента

```php
// IblockRepository использует старый API (CIBlockElement)
$newsRepo->update(123, [
    'NAME' => 'Обновленное название',
    'PROPERTY_VALUES' => [
        'CATEGORY' => 6
    ]
]);

// Базовый Repository использует ORM
$baseRepo = new Repository(SomeTable::class);
$baseRepo->update(123, [
    'NAME' => 'Обновленное название'
]);
```

#### Удаление элемента

```php
$newsRepo->delete(123);
```

#### Сохранение (добавление или обновление)

```php
// Добавление (если нет ID)
$id = $newsRepo->save([
    'NAME' => 'Новый элемент'
]);

// Обновление (если есть ID)
$id = $newsRepo->save([
    'ID' => 123,
    'NAME' => 'Обновленный элемент'
]);
```

### Работа с разделами

```php
// Получение репозитория разделов
$sectionRepo = $newsRepo->getIblockSectionRepository();

// Получение всех разделов
$sections = $sectionRepo->all(['ACTIVE' => 'Y']);

// Получение раздела по ID
$section = $sectionRepo->getById(10);
```

### Примеры использования

#### Пример 1: Получение активных новостей

```php
$newsRepo = new IblockRepository('news');

$news = $newsRepo->getList(
    ['ACTIVE' => 'Y'],
    [
        'select' => [
            'ID',
            'NAME',
            'PREVIEW_TEXT',
            'DATE_CREATE',
            'PROPERTY_CATEGORY'
        ],
        'order' => ['DATE_CREATE' => 'DESC'],
        'limit' => 10
    ]
);
```

#### Пример 2: Создание новости с проверкой

```php
$newsRepo = new IblockRepository('news');

try {
    $id = $newsRepo->add([
        'NAME' => 'Важное объявление',
        'ACTIVE' => 'Y',
        'PREVIEW_TEXT' => 'Текст объявления',
        'PROPERTY_VALUES' => [
            'IMPORTANT' => 'Y',
            'CATEGORY' => 5
        ]
    ]);
    
    echo "Создана новость с ID: $id";
} catch (\Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}
```

#### Пример 3: Массовое обновление

```php
$newsRepo = new IblockRepository('news');

$oldNews = $newsRepo->all([
    '<DATE_CREATE' => date('Y-m-d', strtotime('-1 year'))
]);

foreach ($oldNews as $item) {
    $newsRepo->update($item['ID'], [
        'ACTIVE' => 'N'
    ]);
}
```

## IblockSectionRepository

Репозиторий для работы с разделами инфоблоков.

### Создание

```php
use Beeralex\Core\Repository\IblockSectionRepository;

// По символьному коду инфоблока
$sectionRepo = new IblockSectionRepository('catalog');

// По ID инфоблока
$sectionRepo = new IblockSectionRepository(7);

// Через IblockRepository
$catalogRepo = new IblockRepository('catalog');
$sectionRepo = $catalogRepo->getIblockSectionRepository();
```

### Операции

```php
// Все разделы
$sections = $sectionRepo->all();

// Активные разделы первого уровня
$sections = $sectionRepo->all([
    'ACTIVE' => 'Y',
    'DEPTH_LEVEL' => 1
]);

// Раздел по ID
$section = $sectionRepo->getById(5);

// Раздел по коду
$section = $sectionRepo->one(['CODE' => 'electronics']);

// Добавление раздела
$id = $sectionRepo->add([
    'NAME' => 'Новая категория',
    'CODE' => 'new-category',
    'ACTIVE' => 'Y',
    'IBLOCK_SECTION_ID' => 10 // Родительский раздел
]);

// Обновление
$sectionRepo->update(5, [
    'NAME' => 'Обновленное название'
]);

// Удаление
$sectionRepo->delete(5);
```

### Пример: Построение дерева разделов

```php
$sectionRepo = new IblockSectionRepository('catalog');

$sections = $sectionRepo->all(
    ['ACTIVE' => 'Y'],
    ['ID', 'NAME', 'CODE', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'],
    ['LEFT_MARGIN' => 'ASC']
);

function buildTree(array $sections, ?int $parentId = null): array
{
    $branch = [];
    foreach ($sections as $section) {
        if ($section['IBLOCK_SECTION_ID'] == $parentId) {
            $children = buildTree($sections, $section['ID']);
            if ($children) {
                $section['CHILDREN'] = $children;
            }
            $branch[] = $section;
        }
    }
    return $branch;
}

$tree = buildTree($sections);
```

## HighloadRepository

Репозиторий для работы с хайлоад-блоками.

### Создание

```php
use Beeralex\Core\Repository\HighloadRepository;

// По названию хайлоад-блока
$settingsRepo = new HighloadRepository('Settings');

// По ID
$settingsRepo = new HighloadRepository(3);
```

### Операции

Все операции аналогичны `Repository`:

```php
// Получение всех записей
$items = $settingsRepo->all();

// С фильтром
$items = $settingsRepo->all([
    'UF_ACTIVE' => 1
]);

// Одна запись
$item = $settingsRepo->one(['UF_CODE' => 'main_settings']);

// По ID
$item = $settingsRepo->getById(123);

// Добавление
$id = $settingsRepo->add([
    'UF_NAME' => 'Setting Name',
    'UF_VALUE' => 'Setting Value'
]);

// Обновление
$settingsRepo->update(123, [
    'UF_VALUE' => 'New Value'
]);

// Удаление
$settingsRepo->delete(123);
```

### Пример: Справочник

```php
$directoryRepo = new HighloadRepository('Directory');

// Получение всех активных элементов
$items = $directoryRepo->all(
    ['UF_ACTIVE' => 1],
    ['UF_ID', 'UF_NAME', 'UF_CODE', 'UF_SORT'],
    ['UF_SORT' => 'ASC']
);

// Поиск по коду
$item = $directoryRepo->one(['UF_CODE' => 'delivery_types']);

// Добавление нового элемента
$id = $directoryRepo->add([
    'UF_NAME' => 'Новый тип доставки',
    'UF_CODE' => 'new_delivery',
    'UF_ACTIVE' => 1,
    'UF_SORT' => 500
]);
```

## AbstractRepository

Базовый абстрактный класс для всех репозиториев.

### Основные методы

```php
abstract class AbstractRepository
{
    // Создание Query объекта
    public function query(): Query;
    
    // Получение сервиса запросов
    public function getQueryService(): QueryService;
    
    // Получение класса сущности
    public function getEntity(): string;
}
```

### Использование query()

```php
$newsRepo = new IblockRepository('news');

$query = $newsRepo->query()
    ->setSelect(['ID', 'NAME', 'DATE_CREATE'])
    ->setFilter(['ACTIVE' => 'Y'])
    ->setOrder(['DATE_CREATE' => 'DESC'])
    ->setLimit(10);

$items = $query->fetchAll();
```

## Создание собственных репозиториев

### Простой репозиторий

```php
<?php
namespace App\Repository;

use Beeralex\Core\Repository\IblockRepository;

class NewsRepository extends IblockRepository
{
    public function __construct()
    {
        parent::__construct('news');
    }
    
    public function getActiveNews(int $limit = 10): array
    {
        return $this->getList(
            ['ACTIVE' => 'Y'],
            [
                'select' => ['ID', 'NAME', 'PREVIEW_TEXT', 'DATE_CREATE'],
                'order' => ['DATE_CREATE' => 'DESC'],
                'limit' => $limit
            ]
        );
    }
    
    public function getNewsByCategory(int $categoryId): array
    {
        return $this->getList([
            'ACTIVE' => 'Y',
            'PROPERTY_CATEGORY' => $categoryId
        ]);
    }
}
```

### Использование

```php
$newsRepo = new NewsRepository();

$activeNews = $newsRepo->getActiveNews(5);
$categoryNews = $newsRepo->getNewsByCategory(10);
```

### Регистрация в DI

```php
// .settings.php
use App\Repository\NewsRepository;

return [
    'services' => [
        'value' => [
            NewsRepository::class => [
                'className' => NewsRepository::class
            ]
        ]
    ]
];

// Использование
$newsRepo = service(NewsRepository::class);
```

## Продвинутые возможности

### Кеширование

```php
// Кеширование на 1 час (3600 секунд)
$items = $newsRepo->all(
    ['ACTIVE' => 'Y'],
    ['ID', 'NAME'],
    [],
    3600 // TTL кеша
);

// С кешированием join'ов
$items = $newsRepo->all(
    ['ACTIVE' => 'Y'],
    ['ID', 'NAME'],
    [],
    3600,  // TTL
    true   // Кеширование join'ов
);
```

### Использование QueryService

```php
$newsRepo = new IblockRepository('news');
$queryService = $newsRepo->getQueryService();

$query = $newsRepo->query()
    ->setSelect(['ID', 'NAME', 'PROPERTY_CATEGORY'])
    ->setFilter(['ACTIVE' => 'Y']);

// Группировка сущностей (декомпозиция множественных свойств)
$items = $queryService->fetchGroupedEntities($query);
```

### Сложные запросы

```php
$newsRepo = new IblockRepository('news');

$items = $newsRepo->getList(
    [
        'ACTIVE' => 'Y',
        [
            'LOGIC' => 'OR',
            ['PROPERTY_IMPORTANT' => 'Y'],
            ['>=DATE_CREATE' => date('Y-m-d', strtotime('-7 days'))]
        ]
    ],
    [
        'select' => [
            'ID',
            'NAME',
            'DATE_CREATE',
            'PROPERTY_CATEGORY',
            'PROPERTY_IMPORTANT'
        ],
        'order' => [
            'PROPERTY_IMPORTANT' => 'DESC',
            'DATE_CREATE' => 'DESC'
        ],
        'limit' => 20
    ]
);
```

### Транзакции

```php
use Bitrix\Main\Application;

$connection = Application::getConnection();
$connection->startTransaction();

try {
    $newsRepo = new IblockRepository('news');
    
    $id = $newsRepo->add([
        'NAME' => 'Новость 1'
    ]);
    
    $newsRepo->add([
        'NAME' => 'Новость 2',
        'PROPERTY_VALUES' => [
            'RELATED_NEWS' => $id
        ]
    ]);
    
    $connection->commitTransaction();
} catch (\Exception $e) {
    $connection->rollbackTransaction();
    throw $e;
}
```

## Примеры из проекта

### beeralex.reviews

```php
class ReviewRepository extends IblockRepository
{
    public function __construct()
    {
        parent::__construct('reviews');
    }
    
    public function getReviewsByProduct(int $productId, int $limit = 10): array
    {
        return $this->getList(
            [
                'ACTIVE' => 'Y',
                'PROPERTY_PRODUCT' => $productId
            ],
            [
                'select' => ['ID', 'NAME', 'PREVIEW_TEXT', 'PROPERTY_RATING'],
                'order' => ['DATE_CREATE' => 'DESC'],
                'limit' => $limit
            ]
        );
    }
}
```

### beeralex.favorite

```php
$favoriteRepo = new HighloadRepository('Favorites');

// Добавление в избранное
$favoriteRepo->add([
    'UF_USER_ID' => $userId,
    'UF_PRODUCT_ID' => $productId,
    'UF_DATE' => new \Bitrix\Main\Type\DateTime()
]);

// Проверка наличия в избранном
$exists = $favoriteRepo->one([
    'UF_USER_ID' => $userId,
    'UF_PRODUCT_ID' => $productId
]);
```

## Заключение

Репозитории в `beeralex.core`:
- Упрощают работу с данными Bitrix
- Предоставляют единый API для всех сущностей
- Поддерживают кеширование и оптимизацию запросов
- Улучшают читаемость и поддерживаемость кода
- Следуют Repository Pattern

Используйте репозитории вместо прямого обращения к API Bitrix для лучшей архитектуры приложения.
