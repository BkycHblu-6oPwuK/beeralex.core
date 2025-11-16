## WebController

Наследуйтесь от него, если ваши экшены для web роутера возвращают view, то есть возвращают ``` renderView ``` или ``` renderComponent ```

Так же в нем реализован метод помощник, алиас к ``` renderView ```, ``` view ```.

```php
    public function indexAction()
    {
        return $this->view('index.index'); // /local/views/index/index.php
    }
```

метод начинает строить путь от ``` local/views ``` (базовый путь опеределен в константе ``` VIEWS_PATH ```) и сам подставит .php в конец.

Так же класс переопределяет вывод ошибок и выводит их на странице без json представления, шаблон лежит в ``` /local/views/errors/exception.php ```

## ApiController

Создавайте DTO от класса `Beeralex\Core\Http\Request\AbstractRequestDto`, реализуйте валидацию bitrix атрибутами пакета валидатора и пусть DTO будет парамаметром в экшене. Контроллер наследуйте от `Beeralex\Core\Http\Controllers\ApiController`, в этом классе реализована автоподмена параметра вашим ожидаемым объектом.

DTO:

```php
namespace App\User\Auth\Dto;

use Bitrix\Main\Validation\Rule\Email;
use Bitrix\Main\Validation\Rule\Length;
use App\User\Validation\Rule\UniqueEmailRule;
use Beeralex\Core\Http\Request\AbstractRequestDto;
use Bitrix\Main\Validation\Rule\NotEmpty;

class EmailRegisterRequestDto extends AbstractRequestDto
{
    #[NotEmpty(errorMessage: 'Email обязателен')]
    #[Email(errorMessage: 'Некорректный email')]
    #[UniqueEmailRule]
    public string $email = '';

    #[NotEmpty(errorMessage: 'Пароль обязателен')]
    #[Length(min: 6, max: 50, errorMessage: 'Пароль должен быть от 6 до 50 символов')]
    public string $password = '';

    #[NotEmpty(errorMessage: 'Имя обязательно')]
    public string $name = '';
}
```

Контроллер:
```php
namespace App\Http\Controllers\User;

use App\User\Auth\Dto\EmailRegisterRequestDto;
use Beeralex\Core\Http\Controllers\ApiController;

class UserController extends ApiController
{
    public function configureActions()
    {
        return [
            'register' => [
                'prefilters' => [],
            ],
        ];
    }
    
    public function registerAction(
        EmailRegisterRequestDto $dto)
    {
        dd($dto); // перед вызовом экшена происходит валидация объекта, свойства будут заполнены из тела запроса (POST, GET, INPUT)
    }
}

```