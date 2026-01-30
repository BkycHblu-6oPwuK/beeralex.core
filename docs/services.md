# Сервисы (Services)

Модуль предоставляет множество сервисов для работы с различными аспектами Bitrix. Все сервисы доступны через функцию `service()`.

## Список сервисов

### Работа с данными
- [IblockService](#iblockservice) - Работа с API инфоблоков
- [HlblockService](#hlblockservice) - Работа с хайлоад-блоками
- [CatalogService](#catalogservice) - Работа с каталогом товаров
- [QueryService](#queryservice) - Расширенные запросы к ORM

### Работа с пользователями и локациями
- [UserService](#userservice) - Работа с пользователями
- [LocationService](#locationservice) - Работа с местоположениями
- [FuserService](#fuserservice) - Работа с анонимными пользователями

### Файлы и представления
- [FileService](#fileservice) - Работа с файлами и подключение view
- [ViteService](#viteservice) - Интеграция с Vite.js

### Утилиты
- [PaginationService](#paginationservice) - Пагинация
- [LanguageService](#languageservice) - Работа с множественными числами
- [PathService](#pathservice) - Работа с путями
- [UrlService](#urlservice) - Работа с URL
- [ControllerService](#controllerservice) - Утилиты для контроллеров
- [WebService](#webservice) - Веб-утилиты

### API и HTTP
- [ClientService](#clientservice) - HTTP-клиент для API
- [ApiService](#apiservice) - Базовый класс для API сервисов

## IblockService

Сервис для работы с инфоблоками через Bitrix API.

### Использование

```php
use Beeralex\Core\Service\IblockService;

$iblockService = service(IblockService::class);

// Получить ID инфоблока по коду
$iblockId = $iblockService->getIblockIdByCode('news');

// Получить класс таблицы элементов
$elementTable = $iblockService->getElementApiTable($iblockId);

// Получить сущность инфоблока
$entity = $iblockService->getIblockEntity($iblockId);
```

### Основные методы

```php
// Получение ID инфоблока
getIblockIdByCode(string $code): int

// Получение класса таблицы элементов
getElementApiTable(int $iblockId): string

// Получение сущности по ID свойства
getTableEntityByPropertyId(int $propertyId): ?string

// Получение инфоблока
getIblock(int $iblockId): ?array
```

### Пример

```php
$iblockService = service(IblockService::class);

// Получение данных инфоблока
$newsIblockId = $iblockService->getIblockIdByCode('news');
$iblock = $iblockService->getIblock($newsIblockId);

echo "Название инфоблока: " . $iblock['NAME'];
```

## HlblockService

Сервис для работы с хайлоад-блоками.

### Использование

```php
use Beeralex\Core\Service\HlblockService;

$hlblockService = service(HlblockService::class);

// Получить ID хайлоад-блока по имени
$hlblockId = $hlblockService->getHlblockIdByName('Settings');

// Получить класс сущности хайлоад-блока
$entityClass = $hlblockService->getHlblockById($hlblockId);
```

### Основные методы

```php
getHlblockIdByName(string $name): int
getHlblockById(int $id): string
getHlblockByName(string $name): string
```

## CatalogService

Сервис для работы с каталогом товаров.

### Использование

```php
use Beeralex\Core\Service\CatalogService;

$catalogService = service(CatalogService::class);

// Получить товары с предложениями
$products = $catalogService->getProductsWithOffers([1, 2, 3]);
```

### Основные методы

```php
getProductsWithOffers(array $productIds): array
```

### Пример

```php
$catalogService = service(CatalogService::class);

$products = $catalogService->getProductsWithOffers([16, 17]);

foreach ($products as $product) {
    echo "Товар: " . $product['NAME'] . "\n";
    if (!empty($product['OFFERS'])) {
        foreach ($product['OFFERS'] as $offer) {
            echo "  - Предложение: " . $offer['NAME'] . "\n";
        }
    }
}
```

## UserService

Сервис для работы с пользователями.

### Использование

```php
use Beeralex\Core\Service\UserService;

$userService = service(UserService::class);

// Генерация пароля
$password = $userService->generatePassword($userService->getDefaultUserGroups());

// Генерация логина из email
$login = $userService->generateLogin('user@example.com');

// Получение групп по умолчанию
$groups = $userService->getDefaultUserGroups();

// Валидация пароля
$isValid = $userService->validatePassword('MyPass123!', [2]);
```

### Основные методы

```php
// Генерация пароля согласно политике безопасности
generatePassword(array $groupIds, ?int $length = null): string

// Генерация уникального логина из email
generateLogin(string $email): string

// Получение групп по умолчанию
getDefaultUserGroups(): array

// Валидация пароля
validatePassword(string $password, array $groupIds): bool
```

### Пример

```php
$userService = service(UserService::class);

// Создание нового пользователя
$groups = $userService->getDefaultUserGroups();
$password = $userService->generatePassword($groups, 12);
$login = $userService->generateLogin('new.user@example.com');

$user = new \CUser();
$userId = $user->Add([
    'LOGIN' => $login,
    'PASSWORD' => $password,
    'EMAIL' => 'new.user@example.com',
    'GROUP_ID' => $groups
]);
```

## FileService

Сервис для работы с файлами и подключения view-файлов.

### Использование

```php
use Beeralex\Core\Service\FileService;

$fileService = service(FileService::class);

// Подключение view файла
$fileService->includeFile('catalog.index', ['productId' => 123]);

// Форматирование массива файлов из $_FILES
$formattedFiles = $fileService->getFormattedToSafe($_FILES);

// Копирование директории рекурсивно
$fileService->copyRecursive('/source/path', '/target/path');
```

### Основные методы

```php
// Подключение view-файла с параметрами
includeFile(string $path, array $params = [], string $basePath = '/include/'): void

// Форматирование $_FILES для безопасного сохранения
getFormattedToSafe(?array $files): array

// Рекурсивное копирование директорий
copyRecursive(string $source, string $target): void

// Добавление поля PICTURE_SRC в Query
addPictireSrcInQuery(Query $query, string $thisFieldReference): Query
```

### Пример

```php
$fileService = service(FileService::class);

// В контроллере API
public function getContentAction(string $pathName)
{
    $fileService->includeFile('v1.index', [
        'pathName' => $pathName,
        'userId' => \CUser::GetID()
    ]);
    
    return service(\Beeralex\Api\ApiResult::class);
}

// View файл: /include/v1/index.php
<?php
/** @var string $pathName */
/** @var int $userId */

echo "Path: $pathName, User: $userId";
```

## PaginationService

Сервис для работы с пагинацией.

### Использование

```php
use Beeralex\Core\Service\PaginationService;

$paginationService = service(PaginationService::class);

// Из CIBlockResult
$pagination = $paginationService->toArray($nav);

// Получение массива страниц
$pages = $paginationService->getPages(5, 20); // текущая 5, всего 20

// Пагинация по количеству элементов
$pagination = $paginationService->getPagination(
    itemsCnt: 150,
    pageSize: 10,
    pageUrlParam: 'page'
);
```

### Основные методы

```php
// Преобразование CIBlockResult в массив пагинации
toArray(?\CIBlockResult $nav, int $pageWindow = 5): array

// Получение массива страниц
getPages(int $currentPage, int $pageCount, int $pageWindow = 5): array

// Создание пагинации по количеству элементов
getPagination(int $itemsCnt, int $pageSize, string $pageUrlParam = 'page'): array
```

### Пример

```php
$paginationService = service(PaginationService::class);

// Пример с CIBlockResult
$arSelect = ['ID', 'NAME'];
$arFilter = ['IBLOCK_ID' => 5, 'ACTIVE' => 'Y'];
$res = \CIBlockElement::GetList([], $arFilter, false, ['nPageSize' => 10], $arSelect);

$pagination = $paginationService->toArray($res);

// Результат:
// [
//     'pages' => [
//         ['pageNumber' => 1, 'isSelected' => true],
//         ['pageNumber' => 2, 'isSelected' => false],
//         ...
//     ],
//     'pageSize' => 10,
//     'currentPage' => 1,
//     'pageCount' => 15,
//     'paginationUrlParam' => 'PAGEN_1'
// ]
```

## ViteService

Сервис для интеграции с Vite.js (сборщик фронтенда).

### Использование

```php
use Beeralex\Core\Service\ViteService;

$viteService = service(ViteService::class);

// Подключение одного entry point
$viteService->include('app/main.ts');

// Подключение нескольких entry point'ов
$viteService->includeAssets(['app/main.ts', 'app/admin.ts']);
```

### Основные методы

```php
// Подключение одного entry point
include(string $entry): void

// Подключение нескольких entry points (JS и CSS из Vite)
includeAssets(array $entries): void
```

### Пример

```php
// В шаблоне
$viteService = service(ViteService::class);

// Development: подключит localhost:5173
// Production: подключит собранные файлы из manifest
$viteService->include('app/main.ts');

// Подключение нескольких файлов
$viteService->includeAssets(['app/main.ts', 'app/styles.css']);
```

Важно подключать в некешируемой области!

## LocationService

Сервис для работы с локациями Bitrix.

### Использование

```php
use Beeralex\Core\Service\LocationService;

$locationService = service(LocationService::class);

// Получение локации по ID
$location = $locationService->getLocationById(123);

// Поиск локаций
$locations = $locationService->findLocations('Москва');
```

### Основные методы

```php
getLocationById(int $id): ?array
findLocations(string $query): array
```

## LanguageService

Сервис для работы с множественными числами (pluralization).

### Использование

```php
use Beeralex\Core\Service\LanguageService;

$languageService = service(LanguageService::class);

// Получение правильной формы слова
$text = $languageService->getPlural(5, ['товар', 'товара', 'товаров']);
// Результат: "товаров"

$text = $languageService->getPlural(1, ['товар', 'товара', 'товаров']);
// Результат: "товар"

$text = $languageService->getPlural(2, ['товар', 'товара', 'товаров']);
// Результат: "товара"
```

### Основные методы

```php
getPlural(int $count, array $forms): string
```

### Пример

```php
$languageService = service(LanguageService::class);

$reviewsCount = 23;
echo "$reviewsCount " . $languageService->getPlural(
    $reviewsCount,
    ['отзыв', 'отзыва', 'отзывов']
);
// Выведет: "23 отзыва"
```

## FuserService

Сервис для работы с анонимными пользователями (корзина, избранное для неавторизованных).

### Использование

```php
use Beeralex\Core\Service\FuserService;

$fuserService = service(FuserService::class);

// Получение Fuser ID
$fuserId = $fuserService->getFuserId();

// Обновление корзины пользователя после авторизации
$fuserService->updateBasketOnLogin($userId);
```

### Основные методы

```php
getFuserId(): int
updateBasketOnLogin(int $userId): void
```

## QueryService

Сервис для расширенных операций с ORM Query.

### Использование

```php
use Beeralex\Core\Service\QueryService;

$queryService = service(QueryService::class);

// Группировка множественных свойств
$items = $queryService->fetchGroupedEntities($query);
```

### Основные методы

```php
fetchGroupedEntities(Query $query): array
```

## PathService

Сервис для работы с путями файловой системы.

### Использование

```php
use Beeralex\Core\Service\PathService;

$pathService = service(PathService::class);

// Нормализация пути
$path = $pathService->normalizePath('/path//to///file');
// Результат: "/path/to/file"
```

## UrlService

Сервис для работы с URL.

### Использование

```php
use Beeralex\Core\Service\UrlService;

$urlService = service(UrlService::class);

// Получение текущего URL
$currentUrl = $urlService->getCurrentUrl();

// Построение URL с параметрами
$url = $urlService->buildUrl('/catalog/', ['page' => 2, 'filter' => 'new']);
```

## ClientService

HTTP-клиент для работы с внешними API.

### Использование

```php
use Beeralex\Core\Service\Api\ClientService;

$clientService = service(ClientService::class);

// GET запрос
$response = $clientService->get('https://api.example.com/data');

// POST запрос
$response = $clientService->post('https://api.example.com/submit', [
    'json' => ['key' => 'value']
]);
```

### Основные методы

```php
get(string $url, array $options = []): ResponseInterface
post(string $url, array $options = []): ResponseInterface
put(string $url, array $options = []): ResponseInterface
delete(string $url, array $options = []): ResponseInterface
```

### Пример из beeralex.gigachat

```php
use Beeralex\Core\Service\Api\ClientService;

$clientService = service(ClientService::class);

$response = $clientService->post('https://api.gigachat.ru/v1/chat', [
    'headers' => [
        'Authorization' => "Bearer {$token}",
        'Content-Type' => 'application/json'
    ],
    'json' => [
        'model' => 'GigaChat',
        'messages' => $messages
    ]
]);

$data = json_decode($response->getBody()->getContents(), true);
```

## Примеры комплексного использования

### Пример 1: API контроллер с сервисами

```php
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Service\PaginationService;
use Beeralex\Core\Repository\IblockRepository;

class NewsController extends ApiController
{
    public function listAction(int $page = 1): array
    {
        $repository = new IblockRepository('news');
        
        $items = $repository->query()
            ->where('ACTIVE', 'Y')
            ->setSelect(['ID', 'NAME', 'PREVIEW_TEXT'])
            ->setOrder(['DATE_CREATE' => 'DESC'])
            ->setLimit(10)
            ->setOffset(($page - 1) * 10)
            ->fetchAll();
        
        $total = $repository->query()
            ->setFilter(['ACTIVE' => 'Y'])
            ->queryCountTotal();
        
        $paginationService = service(PaginationService::class);
        $pagination = $paginationService->getPagination($total, 10);
        
        return [
            'items' => $items,
            'pagination' => $pagination
        ];
    }
}
```

### Пример 2: Регистрация пользователя

```php
use Beeralex\Core\Service\UserService;

$userService = service(UserService::class);

$email = 'new.user@example.com';
$groups = $userService->getDefaultUserGroups();
$password = $userService->generatePassword($groups, 12);
$login = $userService->generateLogin($email);

$user = new \CUser();
$userId = $user->Add([
    'LOGIN' => $login,
    'PASSWORD' => $password,
    'CONFIRM_PASSWORD' => $password,
    'EMAIL' => $email,
    'NAME' => 'Новый',
    'LAST_NAME' => 'Пользователь',
    'GROUP_ID' => $groups,
    'ACTIVE' => 'Y'
]);

if ($userId) {
    echo "Пользователь создан с ID: $userId";
} else {
    echo "Ошибка: " . $user->LAST_ERROR;
}
```

### Пример 3: Работа с файлами и представлениями

```php
use Beeralex\Core\Service\FileService;
use Beeralex\Api\ApiResult;

$fileService = service(FileService::class);

// В контроллере
public function getCatalogAction(string $section): array
{
    $fileService->includeFile('v1.catalog', [
        'sectionCode' => $section,
        'userId' => \CUser::GetID()
    ]);
    
    service(ApiResult::class)->setSeo();
    return service(ApiResult::class)->getData();
}

// В /include/v1/catalog.php
<?php
use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Api\ApiResult;

$catalogRepo = new IblockRepository('catalog');
$items = $catalogRepo->getList([
    'ACTIVE' => 'Y',
    'SECTION_CODE' => $sectionCode
]);

service(ApiResult::class)->addPageData([
    'items' => $items,
    'section' => $sectionCode
]);
```

## Заключение

Сервисы в `beeralex.core` предоставляют:
- Единообразный API для работы с Bitrix
- Повторно используемый код
- Легкое тестирование
- Чистую архитектуру

Используйте сервисы через функцию `service()` для улучшения качества кода.
