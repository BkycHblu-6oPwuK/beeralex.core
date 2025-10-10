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