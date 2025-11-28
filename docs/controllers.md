# HTTP Контроллеры и Фильтры

Модуль предоставляет базовые контроллеры для создания API и веб-приложений с автоматической валидацией и обработкой запросов.

## ApiController

Базовый контроллер для создания REST API с автоматической валидацией DTO.

### Создание контроллера

```php
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\Core\Repository\IblockRepository;

class ProductController extends ApiController
{
    public function configureActions(): array
    {
        return [
            'list' => [
                'prefilters' => [],
            ],
            'get' => [
                'prefilters' => [],
            ],
        ];
    }
    
    public function listAction(int $limit = 10): array
    {
        $repository = new IblockRepository('catalog');
        
        return [
            'items' => $repository->query()
                ->where('ACTIVE', 'Y')
                ->setLimit($limit)
                ->fetchAll()
        ];
    }
    
    public function getAction(int $id): array
    {
        $repository = new IblockRepository('catalog');
        $item = $repository->getById($id);
        
        if (!$item) {
            $this->addError(new \Bitrix\Main\Error('Product not found'));
            return [];
        }
        
        return $item;
    }
}
```

### Автоматическая валидация DTO

```php
use Beeralex\Core\Http\Request\AbstractRequestDto;

// DTO класс
class CreateProductDto extends AbstractRequestDto
{
    public string $name;
    public int $price;
    public ?string $description = null;
    
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}

// Контроллер
class ProductController extends ApiController
{
    public function createAction(CreateProductDto $dto): array
    {
        // DTO автоматически валидируется в processBeforeAction
        // Если валидация не прошла, запрос не дойдет до этой точки
        
        $repository = new IblockRepository('catalog');
        $id = $repository->add([
            'NAME' => $dto->name,
            'PROPERTY_VALUES' => [
                'PRICE' => $dto->price,
                'DESCRIPTION' => $dto->description,
            ]
        ]);
        
        return ['id' => $id];
    }
}
```

### Работа с JSON

ApiController автоматически обрабатывает JSON из тела запроса:

```php
// POST /api/product/create/
// Content-Type: application/json
// Body: {"name": "Product", "price": 1000}

public function createAction(CreateProductDto $dto): array
{
    // $dto->name === "Product"
    // $dto->price === 1000
    return ['success' => true];
}
```

## WebController

Базовый контроллер для веб-страниц.

```php
use Beeralex\Core\Http\Controllers\WebController;

class PageController extends WebController
{
    public function indexAction(): string
    {
        return $this->render('index', [
            'title' => 'Главная страница'
        ]);
    }
}
```

## Адаптеры Bitrix ↔ PSR

Модуль предоставляет адаптеры для конвертации между объектами Bitrix и стандартными PSR-7 HTTP-сообщениями.

### BitrixToPsrRequest

Конвертирует `Bitrix\Main\HttpRequest` в `Psr\Http\Message\ServerRequestInterface`:

```php
use Beeralex\Core\Http\Adapter\BitrixToPsrRequest;
use Beeralex\Core\Service\WebService;

$adapter = new BitrixToPsrRequest(service(WebService::class));
$psrRequest = $adapter->convert($bitrixRequest);

// Теперь можно использовать PSR-7 методы
$method = $psrRequest->getMethod();
$uri = $psrRequest->getUri();
$headers = $psrRequest->getHeaders();
$body = $psrRequest->getBody();
```

**Что конвертируется:**
- HTTP метод и URI
- Заголовки (через WebService::collectHttpHeaders)
- Query параметры
- POST данные
- Загруженные файлы (нормализация через ServerRequest::normalizeFiles)
- Cookies
- Server параметры

### PsrToBitrixRequest

Конвертирует PSR-7 запрос обратно в Bitrix формат:

```php
use Beeralex\Core\Http\Adapter\PsrToBitrixRequest;

$adapter = new PsrToBitrixRequest();
$bitrixRequest = $adapter->convert($psrRequest);

// Bitrix запрос готов к использованию
$query = $bitrixRequest->getQueryList()->getValues();
$post = $bitrixRequest->getPostList()->getValues();
```

### BitrixToPsrResponse

Конвертирует `Bitrix\Main\HttpResponse` в `Psr\Http\Message\ResponseInterface`:

```php
use Beeralex\Core\Http\Adapter\BitrixToPsrResponse;

$adapter = new BitrixToPsrResponse(service(WebService::class));
$psrResponse = $adapter->convert($bitrixResponse);

// PSR-7 response
$statusCode = $psrResponse->getStatusCode();
$content = (string)$psrResponse->getBody();
```

### PsrToBitrixResponse

Конвертирует PSR-7 ответ обратно в Bitrix:

```php
use Beeralex\Core\Http\Adapter\PsrToBitrixResponse;
use GuzzleHttp\Psr7\Response;

$psrResponse = new Response(200, ['Content-Type' => 'application/json'], '{"success": true}');

$adapter = new PsrToBitrixResponse();
$bitrixResponse = $adapter->convert($psrResponse);
```

**Применение адаптеров:**
- Интеграция с PSR-7 совместимыми библиотеками
- HTTP-клиенты (Guzzle)
- Middleware обработка запросов
- Тестирование с mock-объектами

## Request DTO

Request DTO (Data Transfer Object) используется для валидации и типизации входящих данных в контроллерах.

### RequestDtoContract

Интерфейс определяет контракт для всех Request DTO:

```php
interface RequestDtoContract
{
    public static function fromArray(array $data): static;
    public function getData(): array;
    public function isValid(): bool;
    public function getErrors(): array;
    public function setValidationResult(ValidationResult $result): static;
}
```

### AbstractRequestDto

Базовый класс для создания Request DTO с автоматической валидацией:

```php
use Beeralex\Core\Http\Request\AbstractRequestDto;

class CreateUserDto extends AbstractRequestDto
{
    public string $email;
    public string $password;
    public ?string $name = null;
    public ?string $phone = null;
    
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
        ];
    }
}
```

### Создание и использование

```php
// Создание из массива
$dto = CreateUserDto::fromArray($_POST);

// Валидация
if (!$dto->isValid()) {
    $errors = $dto->getErrors();
    foreach ($errors as $error) {
        echo $error->getMessage();
    }
}

// Получение данных
$data = $dto->getData(); // ['email' => '...', 'password' => '...', ...]
```

### Автоматическая валидация в ApiController

ApiController автоматически валидирует DTO в методе `processBeforeAction`:

```php
class UserController extends ApiController
{
    // DTO автоматически создается из $_POST + json_decode($_REQUEST['input'])
    // и валидируется перед вызовом метода
    public function createAction(CreateUserDto $dto): array
    {
        // Если дошли сюда - валидация прошла успешно
        
        return [
            'userId' => $this->userService->create(
                $dto->email,
                $dto->password,
                $dto->name
            )
        ];
    }
}
```

### Работа с JSON

Request DTO поддерживает данные из JSON body:

```http
POST /api/user/create/
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "secret123",
    "name": "John Doe"
}
```

ApiController автоматически декодирует JSON и передает в DTO.

## Resource DTO

Resource DTO используется для трансформации и сериализации выходных данных.

### ResourceContract

Интерфейс для всех Resource DTO:

```php
interface ResourceContract
{
    public static function make(array $data): static;
    public function toArray(): array;
}
```

### Resource

Базовый класс с магическими методами доступа и поддержкой JSON:

```php
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 */
class UserResource extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => (int)$data['ID'],
            'name' => (string)($data['NAME'] ?? ''),
            'email' => (string)($data['EMAIL'] ?? ''),
        ]);
    }
    
    public function toArray(): array
    {
        return $this->resource;
    }
}
```

### Использование Resource

```php
// Создание из данных Bitrix
$user = [
    'ID' => 1,
    'NAME' => 'John Doe',
    'EMAIL' => 'john@example.com',
];

$resource = UserResource::make($user);

// Доступ через свойства
echo $resource->name; // "John Doe"

// JSON сериализация
echo json_encode($resource); // {"id":1,"name":"John Doe","email":"john@example.com"}

// Массив
$array = $resource->toArray();

// ArrayAccess
$resource['name'] = 'Jane';
echo $resource['name']; // "Jane"
```

### Trait Resourceble

Resource использует `Resourceble` трейт для магических методов:

- `__get()`, `__set()` - доступ к полям как к свойствам
- `ArrayAccess` - доступ как к массиву `$resource['key']`
- `JsonSerializable` - автоматическая JSON сериализация
- `Countable` - подсчет элементов

### Сложный пример с вложенными данными

```php
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $previewText
 * @property string $detailPageUrl
 * @property PropertyItemDTO[] $properties
 */
class ProductResource extends Resource
{
    public static function make(array $data): static
    {
        // Трансформация свойств
        $props = [];
        foreach ($data['DISPLAY_PROPERTIES'] ?? [] as $prop) {
            $props[] = PropertyItemDTO::make($prop);
        }
        
        return new static([
            'id' => (int)$data['ID'],
            'code' => (string)$data['CODE'],
            'name' => (string)$data['NAME'],
            'previewText' => $data['PREVIEW_TEXT'] ?? '',
            'detailPageUrl' => service(UrlService::class)->cleanUrl($data['DETAIL_PAGE_URL']),
            'properties' => $props,
        ]);
    }
    
    public function toArray(): array
    {
        $data = $this->resource;
        
        // Рекурсивная сериализация вложенных ресурсов
        if (!empty($data['properties'])) {
            $data['properties'] = array_map(
                fn($prop) => $prop->toArray(),
                $data['properties']
            );
        }
        
        return $data;
    }
}
```

### Использование в контроллерах

```php
class ProductController extends ApiController
{
    public function listAction(): array
    {
        $repository = new IblockRepository('catalog');
        $items = $repository->all(['ACTIVE' => 'Y'], ['*'], [], 0, false);
        
        // Трансформация данных через Resource
        return [
            'items' => array_map(
                fn($item) => ProductResource::make($item),
                $items
            )
        ];
    }
}
```

**Преимущества Resource DTO:**
- Явная трансформация данных из формата Bitrix
- Типизированный доступ через PHPDoc
- Автоматическая JSON сериализация
- Скрытие внутренних полей (например, пароли)
- Единообразный формат API ответов

## Prefilters (Фильтры)

### FilesSize - Проверка размера файлов

```php
use Beeralex\Core\Http\Prefilters\FilesSize;

public function configureActions(): array
{
    return [
        'upload' => [
            'prefilters' => [
                new FilesSize(5 * 1024 * 1024) // Максимум 5MB
            ],
        ],
    ];
}
```

### FilesType - Проверка типов файлов

```php
use Beeralex\Core\Http\Prefilters\FilesType;

public function configureActions(): array
{
    return [
        'upload' => [
            'prefilters' => [
                new FilesType(['image/jpeg', 'image/png', 'image/gif'])
            ],
        ],
    ];
}
```

### Комбинирование фильтров

```php
public function configureActions(): array
{
    return [
        'upload' => [
            'prefilters' => [
                new FilesType(['image/jpeg', 'image/png']),
                new FilesSize(5 * 1024 * 1024),
            ],
        ],
    ];
}
```

## Примеры использования

### Пример из beeralex.api

```php
use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Core\Http\Controllers\ApiController;

class ArticlesController extends ApiController
{
    use ApiProcessResultTrait;
    
    public function indexAction()
    {
        return $this->process(function () {
            service(FileService::class)->includeFile('v1.articles.index');
            service(ApiResult::class)->setSeo();
            return service(ApiResult::class);
        });
    }
}
```

### Пример из beeralex.user

```php
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Dto\AuthCredentialsDto;

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
        $metadata = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];

        $result = $this->authService->login($credentials, $metadata);
        
        if (!$result->isSuccess()) {
            $this->addErrors($result->getErrors());
            return [];
        }

        return $result->getData();
    }
}
```

## Заключение

Контроллеры в `beeralex.core`:
- Автоматическая валидация запросов
- Поддержка JSON
- Встроенные фильтры безопасности
- Чистый и читаемый код
