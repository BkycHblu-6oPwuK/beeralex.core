# Реализация паттерна репозиторий в bitrix

каждый репозиторий является абстракцией над ``` Bitrix\Main\ORM\Data\DataManager ``` битрикса и реализует интерфейс ``` Beeralex\Core\Repository\RepositoryContract ```

наследуйтесь от ``` Beeralex\Core\Repository\Repository ``` и реализуйте свою бизнес логику, например:

```php
class StoreRepository extends Repository implements StoreRepositoryContract
{
    public function __construct()
    {
        parent::__construct(StoreTable::class);
    }

    public function getAllowedStores(): array
    {
        $stores = $this->query()
            ->setSelect([
                'ID',
                'ADDRESS',
                'PHONE',
                'SCHEDULE',
                'DESCRIPTION',
            ])
            ->setFilter([
                'ACTIVE' => 'Y',
                'ISSUING_CENTER' => 'Y',
                'SHIPPING_CENTER' => 'Y',
            ])
            ->setOrder('SORT')
            ->fetchAll();
        return $stores
    }

    public function getAllIds(): array
    {
    }
}
// или

new Repository(StoreTable::class)->query()
```


Хайлоад блоки и инфоблоки по сути являются компилируемыми сущностями, поэтому для них есть интерфейс ``` Beeralex\Core\Repository\CompiledEntityRepositoryContract ```, каждый свой репозиторий наследуйте от ``` Beeralex\Core\Repository\IblockRepository ``` для сущности инфоблока, или ``` Beeralex\Core\Repository\HighloadRepository ``` для сущности хайлоада.

для инфоблоков в констуктор репозитория нужно передать символьный код инфоблока или его id, так же для инфоблоков обязательно должен быть задан символьный код апи

```php
class ProductsRepository extends IblockRepository implements ProductRepositoryContract
{
    public function __construct()
    {
        parent::__construct('catalog');
    }
}

// или
new IblockRepository('catalog')->query();
```

Добавление, обновление и удаление реализовано через старое api, свойства нужно передавать под ключом ``` PROPERTY_VALUES ```

для хайлоадов нужно передавать название блока, или его id

```php
class SmsCodeRepository extends HighloadRepository implements SmsCodeRepositoryContract
{
    public function __construct()
    {
        parent::__construct('SmsBuilding');
    }
}
// или
new HighloadRepository('catalog')->query();
```