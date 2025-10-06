## Класс Vite

Для работы необходимо чтобы был настроен файл .env в - local/php/interface/include/.env

```bash
MODE = development|production # локально ставим development, на боевом через ftp устанавливаем production
VITE_BASE_PATH = local/js/vite # базовый путь до директории с vite
VITE_CLIENT_PATH = dist/client # директория с клиентскими ассетами относительно базовой директории
VITE_PORT = 5173 # порт сервера для режима development, в контейнер node так же нужно прокинуть порт
```

Получение объекта класса:
```php 
$vite = Vite::getInstance() 
```

Подключение ассетов:
```php 
$vite->includeAssets([
	'src/common/js/bundle.js',
]);
```

- ```Vite::includeAssets``` - принимает массив путей относительно корневой директории vite, вызывать его нужно в некешируемой области

Более подробно - https://git.beeralex-dev.ru/ITB-dev/example_template

## SSR

для получения html с node ssr сервера определить переменные 

```bash
VITE_SSR_ENABLE = 0|1 # включен ssr или нет
VITE_SSR_HOST = 'localhost'; # или название докер контейнера с node где запускается ssr
VITE_SSR_PORT = 5174 # в контейнер так же нужно прокинуть порт
```

метод для получения верстки ```getSsrContent``` доступен в классе ``` Beeralex\Core\Helpers\SsrHelper ```, параметром передается страница которая должа быть получена название страницы соответствует ключу из build.rollupOptions.input

Вторым параметром передаются данные для вашего vue приложения

Запрос на сервер делается методом post, а данные передаются в теле запроса под ключом data (в формате json)

Запрос вернет null если ```VITE_SSR_ENABLE``` == 0 или если не продакшен среда

```php
SsrHelper::getSsrContent('pagename', []|null)
```

Более подробно (основа для проета с основной ветки) - https://git.beeralex-dev.ru/ITB-dev/example_template

основа для vite с ветки ssr_version - https://git.beeralex-dev.ru/ITB-dev/example_template/src/branch/ssr_version