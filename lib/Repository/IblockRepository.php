<?php

declare(strict_types=1);

namespace Beeralex\Core\Repository;

use Beeralex\Core\Model\SectionTableFactory;
use Beeralex\Core\Service\IblockService;
use Bitrix\Iblock\ORM\Query;
use Bitrix\Main\SystemException;

class IblockRepository extends Repository implements IblockRepositoryContract
{
    public readonly int $entityId;
    protected ?IblockSectionRepository $sectionRepository = null;

    public function __construct(string|int $iblockCodeOrId)
    {
        $iblockService = service(IblockService::class);
        if (is_string($iblockCodeOrId)) {
            $iblockCodeOrId = $iblockService->getIblockIdByCode($iblockCodeOrId);
        }

        $this->entityId = $iblockCodeOrId;

        parent::__construct($iblockService->getElementApiTable($iblockCodeOrId), true);
    }

    /**
     * Возвращает все записи, соответствующие фильтру
     * Добавлен дополнительный алиас для модели раздела инфоблока IBLOCK_MODEL_SECTION
     */
    public function all(array $filter = [], array $select = ['*'], array $order = [], int $cacheTtl = 0, bool $cacheJoins = false): array
    {
        $query = $this->query();
        if ($this->needsSectionJoin($select, array_keys($filter), array_keys($order))) {
            $query = $this->addSectionModelToQuery($query);
        }
        $query->setSelect($select)->setFilter($filter)->setOrder($order)->setCacheTtl($cacheTtl)->cacheJoins($cacheJoins);
        return $this->useDecompose ? $this->queryService->fetchGroupedEntities($query) : $query->fetchAll();
    }

    /**
     * Возвращает одну запись, соответствующую фильтру
     * Добавлен дополнительный алиас для модели раздела инфоблока IBLOCK_MODEL_SECTION
     */
    public function one(array $filter = [], array $select = ['*'], int $cacheTtl = 0, bool $cacheJoins = false): ?array
    {
        $query = $this->query();
        if ($this->needsSectionJoin($select, array_keys($filter))) {
            $query = $this->addSectionModelToQuery($query);
        }
        $query->setSelect($select)->setFilter($filter)->setLimit(1)->setCacheTtl($cacheTtl)->cacheJoins($cacheJoins);
        $result = $this->useDecompose ? ($this->queryService->fetchGroupedEntities($query)[0] ?? null) : $query->fetch();
        return empty($result) ? null : $result;
    }

    /**
     * Возвращает результат запроса с учетом возможного присоединения модели раздела инфоблока IBLOCK_MODEL_SECTION
     */
    public function getList(array $parameters = []): \Bitrix\Main\ORM\Query\Result
    {
        if (!isset($parameters['runtime']['IBLOCK_MODEL_SECTION'])) {
            $fields = array_merge(
                array_values($parameters['select'] ?? []),
                array_keys($parameters['filter'] ?? []),
                array_keys($parameters['order'] ?? []),
            );
            if ($this->needsSectionJoin($fields)) {
                $sectionModel = service(SectionTableFactory::class)->compileEntityByIblock($this->entityId);
                $parameters['runtime']['IBLOCK_MODEL_SECTION'] = [
                    'data_type' => $sectionModel,
                    'reference' => ['=this.IBLOCK_SECTION_ID' => 'ref.ID'],
                    'join_type' => 'LEFT',
                ];
            }
        }
        return parent::getList($parameters);
    }

    /**
     * Получение репозитория разделов инфоблока
     * @param callable|null $factory Фабрика для создания репозитория разделов
     */
    public function getIblockSectionRepository(?callable $factory = null): IblockSectionRepository
    {
        if ($factory !== null) {
            return $factory($this->entityId);
        }
        if ($this->sectionRepository === null) {
            $this->sectionRepository = new IblockSectionRepository($this->entityId);
        }
        return $this->sectionRepository;
    }

    /**
     * Добавление элемента через старое API
     * @param array $data поля инфоблока + свойства под ключом PROPERTY_VALUES
     */
    public function add(array|object $data): int
    {
        $el = new \CIBlockElement();
        $data = (array)$data;

        $data['IBLOCK_ID'] = $this->entityId;

        $propertyValues = $data['PROPERTY_VALUES'] ?? null;
        unset($data['PROPERTY_VALUES']);

        $id = $el->Add($data);

        if (!$id) {
            throw new SystemException("Ошибка добавления элемента инфоблока: " . $el->LAST_ERROR);
        }

        // Обновляем свойства, если они переданы
        if ($propertyValues && is_array($propertyValues)) {
            \CIBlockElement::SetPropertyValues($id, $this->entityId, $propertyValues);
        }

        return (int)$id;
    }

    /**
     * Обновление элемента через старое API
     * @param array $data поля инфоблока + свойства под ключом PROPERTY_VALUES
     */
    public function update(int $id, array|object $data): void
    {
        $el = new \CIBlockElement();
        $data = (array)$data;
        $propertyValues = $data['PROPERTY_VALUES'] ?? null;
        unset($data['PROPERTY_VALUES']);

        if (!empty($data)) {
            if (!$el->Update($id, $data)) {
                throw new SystemException("Ошибка обновления элемента инфоблока #{$id}: " . $el->LAST_ERROR);
            }
        }

        // Обновляем свойства, если есть
        if ($propertyValues && is_array($propertyValues)) {
            \CIBlockElement::SetPropertyValuesEx($id, $this->entityId, $propertyValues);
        }
    }

    /**
     * Удаление элемента через старое API
     */
    public function delete(int $id): void
    {
        if (!\CIBlockElement::Delete($id)) {
            throw new SystemException("Ошибка удаления элемента инфоблока #{$id}");
        }
    }

    /**
     * Добавляет к запросу присоединение модели раздела инфоблока IBLOCK_MODEL_SECTION
     */
    public function addSectionModelToQuery(Query $query): Query
    {
        $iblockService = service(IblockService::class);
        return $iblockService->addSectionModelToQuery($this->entityId, $query);
    }

    private function needsSectionJoin(array ...$fieldGroups): bool
    {
        foreach (array_merge(...$fieldGroups) as $field) {
            $clean = preg_replace('/^[=!<>%?@~]+/', '', (string)$field);
            if ($clean === 'IBLOCK_MODEL_SECTION' || str_starts_with($clean, 'IBLOCK_MODEL_SECTION.')) {
                return true;
            }
        }
        return false;
    }
}
