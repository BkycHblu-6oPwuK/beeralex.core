# Расширение DI провайдерами

Пусть сервисы каждого вашего пакета регистрируются провайдером. Для этого пусть ваш провайдер наследуется от ``` Beeralex\Core\DI\AbstractServiceProvider ``` и реализует метод ``` registerServices ``` в котором будет реализована регистрация сервисов

например 

```php
namespace Beeralex\Core\Logger;

use Beeralex\Core\DI\AbstractServiceProvider;

class LoggerServiceProvider extends AbstractServiceProvider
{
    public function registerServices(): void
    {
        $this->bind(LoggerFactoryContract::class, FileLoggerFactory::class, fn() => [$_SERVER['DOCUMENT_ROOT'] . '/local/logs']);
    }
}
```


в local/config/providers.php зарегистрируйте провайдеры приложения и при подключении модуля beeralex.core провайдеры из этого файла будут зарегистрированы