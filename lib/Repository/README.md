# Реализация паттерна репозиторий в bitrix

каждый репозиторий является абстракцией над ``` Bitrix\Main\ORM\Data\DataManager ``` битрикса и реализует интерфейс ``` Beeralex\Core\Repository\RepositoryContract ```

наследуйтесь от ``` Beeralex\Core\Repository\BaseRepository ``` и реализуйте свою бизнес логику, например:

```php
class StoreRepository extends BaseRepository implements StoreRepositoryContract
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
```


Хайлоад блоки и инфоблоки по сути являются компилируемыми сущностями, поэтому для них есть интерфейс ``` Beeralex\Core\Repository\CompiledEntityRepositoryContract ```, каждый свой репозиторий наследуйте от ``` Beeralex\Core\Repository\BaseIblockRepository ``` для сущности инфоблока, или ``` Beeralex\Core\Repository\BaseHighloadRepository ``` для сущности хайлоада.

для инфоблоков в констуктор репозитория нужно передать символьный код инфоблока или его id, так же для инфоблоков обязательно должен быть задан символьный код апи

```php
class ProductsRepository extends BaseIblockRepository implements ProductRepositoryContract
{
    public function __construct()
    {
        parent::__construct('catalog');
    }
}
```

для хайлоадов нужно передавать название блока, или его id

```php
class SmsCodeRepository extends BaseHighloadRepository implements SmsCodeRepositoryContract
{
    public function __construct()
    {
        parent::__construct('SmsBuilding');
    }
}
```