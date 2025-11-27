<?php
declare(strict_types=1);
namespace Beeralex\Core\Repository;

use Beeralex\Core\Service\IblockService;
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
     * Получение репозитория разделов инфоблока
     * @param callable|null $factory Фабрика для создания репозитория разделов
     */
    public function getIblockSectionRepository(?callable $factory = null) : IblockSectionRepository
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
            \CIBlockElement::SetPropertyValuesEx($id, $this->entityId, $propertyValues);
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
}
