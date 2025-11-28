# Model

Пакет Model содержит вспомогательные ORM классы для работы с динамическими сущностями Bitrix.

## SectionTableFactory

Фабрика для создания динамических ORM сущностей разделов инфоблоков с поддержкой пользовательских полей.

### Назначение

Создает runtime ORM классы для разделов инфоблоков с автоматической настройкой:
- Связей с родительскими разделами
- Пользовательских полей типа "список" (enumeration)
- Scope фильтра по IBLOCK_ID

### Создание сущности

```php
use Beeralex\Core\Model\SectionTableFactory;

$factory = new SectionTableFactory();

// По ID инфоблока
$sectionEntity = $factory->compileEntityByIblock(1);

// По объекту Iblock
$iblock = \Bitrix\Iblock\Iblock::wakeUp(1);
$sectionEntity = $factory->compileEntityByIblock($iblock);

// Проверка успешности
if ($sectionEntity === null) {
    throw new Exception('Инфоблок не найден');
}
```

### Использование динамической сущности

```php
$sectionEntity = $factory->compileEntityByIblock(1);

// Получение списка разделов
$sections = $sectionEntity::getList([
    'select' => ['ID', 'NAME', 'CODE', 'PARENT_SECTION'],
    'filter' => ['ACTIVE' => 'Y'],
])->fetchAll();

// Связь с родительским разделом
$section = $sectionEntity::getList([
    'select' => ['ID', 'NAME', 'PARENT_SECTION.NAME'],
    'filter' => ['=CODE' => 'electronics'],
])->fetch();

echo $section['NAME']; // "Электроника"
echo $section['PARENT_SECTION_NAME']; // "Каталог"
```

### Пользовательские поля

Для полей типа "список" автоматически создаются связи с таблицей значений:

```php
// Инфоблок с UF_DEPARTMENT (тип: список)
$sectionEntity = $factory->compileEntityByIblock(2);

$section = $sectionEntity::getList([
    'select' => [
        'ID',
        'NAME',
        'UF_DEPARTMENT',
        'UF_DEPARTMENT_ENUM.VALUE', // Значение из списка
    ],
    'filter' => ['=ID' => 5],
])->fetch();

echo $section['UF_DEPARTMENT_ENUM_VALUE']; // "Отдел продаж"
```

### Структура генерируемого класса

Пример сгенерированного класса для инфоблока с ID=1:

```php
namespace Beeralex\Core\Model;

class Section1Table extends \Bitrix\Iblock\SectionTable
{
    public static function getUfId()
    {
        return "IBLOCK_1_SECTION";
    }
    
    public static function getMap(): array
    {
        $fields = parent::getMap();
        
        // Связь с родительским разделом
        $fields["PARENT_SECTION"] = [
            "data_type" => "\\Bitrix\\Iblock\\Section1",
            "reference" => ["=this.IBLOCK_SECTION_ID" => "ref.ID"],
        ];
        
        // Связь для UF полей типа enumeration
        $fields["UF_DEPARTMENT_ENUM"] = [
            "data_type" => "\\Beeralex\\Core\\Model\\UserFieldEnumTable",
            "reference" => ["=this.UF_DEPARTMENT" => "ref.ID"],
        ];
        
        return $fields;
    }
    
    public static function setDefaultScope($query)
    {
        return $query->where("IBLOCK_ID", 1);
    }
}
```

### Кеширование сущностей

Сгенерированные классы кешируются в памяти:

```php
$factory = new SectionTableFactory();

// Первый вызов - генерация класса
$entity1 = $factory->compileEntityByIblock(1);

// Второй вызов - возврат из кеша
$entity2 = $factory->compileEntityByIblock(1);

// $entity1 === $entity2 (та же сущность)
```

### Применение в репозиториях

```php
use Beeralex\Core\Model\SectionTableFactory;

class CategoryRepository
{
    protected string $entityClass;
    
    public function __construct(int $iblockId)
    {
        $factory = new SectionTableFactory();
        $this->entityClass = $factory->compileEntityByIblock($iblockId);
    }
    
    public function getTree(): array
    {
        return $this->entityClass::getList([
            'select' => ['ID', 'NAME', 'CODE', 'DEPTH_LEVEL', 'PARENT_SECTION.NAME'],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['LEFT_MARGIN' => 'ASC'],
        ])->fetchAll();
    }
    
    public function getRootCategories(): array
    {
        return $this->entityClass::getList([
            'select' => ['ID', 'NAME', 'CODE'],
            'filter' => ['ACTIVE' => 'Y', 'DEPTH_LEVEL' => 1],
            'order' => ['SORT' => 'ASC'],
        ])->fetchAll();
    }
}

$repository = new CategoryRepository(1);
$categories = $repository->getTree();
```

## UserFieldEnumTable

ORM сущность для работы с таблицей значений списочных пользовательских полей.

### Структура таблицы

Представляет таблицу `b_user_field_enum`:

```php
use Beeralex\Core\Model\UserFieldEnumTable;

// Получение значения по ID
$enum = UserFieldEnumTable::getById(15)->fetch();
echo $enum['VALUE']; // "Москва"
echo $enum['SORT']; // "100"
echo $enum['XML_ID']; // "moscow"

// Получение всех значений для UF поля
$enums = UserFieldEnumTable::getList([
    'filter' => ['USER_FIELD_ID' => 5],
    'order' => ['SORT' => 'ASC'],
])->fetchAll();
```

### Поля таблицы

- **ID** - первичный ключ
- **USER_FIELD_ID** - ID пользовательского поля
- **VALUE** - значение элемента списка
- **DEF** - значение по умолчанию (Y/N)
- **SORT** - сортировка
- **XML_ID** - символьный код

### Связь с UserFieldTable

```php
use Beeralex\Core\Model\UserFieldEnumTable;
use Bitrix\Main\UserFieldTable;

// Получение UF поля с его значениями
$result = UserFieldEnumTable::getList([
    'select' => [
        'ID',
        'VALUE',
        'SORT',
        'USER_FIELD.FIELD_NAME',
        'USER_FIELD.ENTITY_ID',
    ],
    'filter' => ['USER_FIELD.ENTITY_ID' => 'IBLOCK_1_SECTION'],
    'order' => ['SORT' => 'ASC'],
]);

while ($enum = $result->fetch()) {
    echo "{$enum['USER_FIELD_FIELD_NAME']}: {$enum['VALUE']}\n";
}
```

### Использование с SectionTableFactory

`SectionTableFactory` автоматически создает связи с `UserFieldEnumTable`:

```php
$factory = new SectionTableFactory();
$sectionEntity = $factory->compileEntityByIblock(1);

// Получение раздела со значением списочного поля
$section = $sectionEntity::getList([
    'select' => [
        'ID',
        'NAME',
        'UF_CITY',                    // ID значения
        'UF_CITY_ENUM.VALUE',         // Текстовое значение
        'UF_CITY_ENUM.XML_ID',        // Символьный код
    ],
    'filter' => ['=ID' => 10],
])->fetch();

echo $section['UF_CITY_ENUM_VALUE']; // "Москва"
echo $section['UF_CITY_ENUM_XML_ID']; // "moscow"
```

### Получение списка значений для формы

```php
use Beeralex\Core\Model\UserFieldEnumTable;

function getEnumOptions(int $userFieldId): array
{
    $enums = UserFieldEnumTable::getList([
        'select' => ['ID', 'VALUE', 'XML_ID'],
        'filter' => ['USER_FIELD_ID' => $userFieldId],
        'order' => ['SORT' => 'ASC'],
    ])->fetchAll();
    
    return array_column($enums, 'VALUE', 'ID');
}

// В шаблоне формы
$options = getEnumOptions(5);
// [15 => "Москва", 16 => "Санкт-Петербург", ...]
```

### Обновление значений списка

```php
use Beeralex\Core\Model\UserFieldEnumTable;

// Добавление нового значения
$result = UserFieldEnumTable::add([
    'USER_FIELD_ID' => 5,
    'VALUE' => 'Новосибирск',
    'XML_ID' => 'novosibirsk',
    'SORT' => 300,
    'DEF' => 'N',
]);

if ($result->isSuccess()) {
    $id = $result->getId();
}

// Обновление
UserFieldEnumTable::update(15, [
    'VALUE' => 'Москва (обновлено)',
    'SORT' => 50,
]);

// Удаление
UserFieldEnumTable::delete(15);
```

### Фильтрация по XML_ID

```php
$enum = UserFieldEnumTable::getList([
    'filter' => [
        'USER_FIELD_ID' => 5,
        '=XML_ID' => 'moscow',
    ],
])->fetch();

echo $enum['VALUE']; // "Москва"
```

## Практические примеры

### Категории товаров с пользовательскими полями

```php
use Beeralex\Core\Model\SectionTableFactory;

class ProductCategoryService
{
    protected string $sectionEntity;
    
    public function __construct(int $catalogIblockId)
    {
        $factory = new SectionTableFactory();
        $this->sectionEntity = $factory->compileEntityByIblock($catalogIblockId);
    }
    
    public function getCategoryWithAttributes(int $categoryId): ?array
    {
        return $this->sectionEntity::getList([
            'select' => [
                'ID',
                'NAME',
                'CODE',
                'DESCRIPTION',
                'PARENT_SECTION.NAME',
                'UF_BRAND_ENUM.VALUE',
                'UF_SEASON_ENUM.VALUE',
            ],
            'filter' => ['=ID' => $categoryId],
        ])->fetch();
    }
    
    public function getCategoriesByBrand(string $brandXmlId): array
    {
        return $this->sectionEntity::getList([
            'select' => ['ID', 'NAME', 'CODE'],
            'filter' => [
                'ACTIVE' => 'Y',
                'UF_BRAND_ENUM.XML_ID' => $brandXmlId,
            ],
            'order' => ['SORT' => 'ASC'],
        ])->fetchAll();
    }
}

$service = new ProductCategoryService(1);
$category = $service->getCategoryWithAttributes(10);

echo $category['NAME']; // "Летняя обувь"
echo $category['PARENT_SECTION_NAME']; // "Обувь"
echo $category['UF_BRAND_ENUM_VALUE']; // "Nike"
echo $category['UF_SEASON_ENUM_VALUE']; // "Лето"
```

### Построение дерева категорий

```php
use Beeralex\Core\Model\SectionTableFactory;

class CategoryTreeBuilder
{
    protected string $sectionEntity;
    
    public function __construct(int $iblockId)
    {
        $factory = new SectionTableFactory();
        $this->sectionEntity = $factory->compileEntityByIblock($iblockId);
    }
    
    public function buildTree(): array
    {
        $sections = $this->sectionEntity::getList([
            'select' => [
                'ID',
                'NAME',
                'CODE',
                'IBLOCK_SECTION_ID',
                'DEPTH_LEVEL',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
            ],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['LEFT_MARGIN' => 'ASC'],
        ])->fetchAll();
        
        return $this->buildTreeRecursive($sections);
    }
    
    protected function buildTreeRecursive(array $sections, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($sections as $section) {
            if ($section['IBLOCK_SECTION_ID'] == $parentId) {
                $section['CHILDREN'] = $this->buildTreeRecursive($sections, $section['ID']);
                $tree[] = $section;
            }
        }
        return $tree;
    }
}

$builder = new CategoryTreeBuilder(1);
$tree = $builder->buildTree();
```
