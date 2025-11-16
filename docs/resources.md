Класс для удобного преобразования массива одного вида в другой.

Для использования вы должны создать свой класс и наследоваться от класса ```Resource``` и реализовать метод ```toArray```

```php
class Elements extends Resource
```

Далее используйте свой класс для создания объекта методом ```Elements::make```, массив должен быть ассоциативным

```php
Elements::make(['key' => 'value']);
```
или
```php
Elements::make(compact('name-array-variable'));
```

в методе ```toArray``` ключ key массива будет доступен через $this как свойство:

```php
$this->key;
```

Получить отформатированный массив, реализация которая должна быть написана вами в методе ```toArray```

```php
Elements::make(compact('name-array-variable'))->toArray();
```

## Resource

Класс реализует интерфейсы

- JsonSerializable
- ArrayAccess
- Countable

Поэтому вы можете работать с объектом как с массивом