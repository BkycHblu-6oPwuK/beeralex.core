<?php

namespace Beeralex\Core\Repository;

use Beeralex\Core\Helpers\IblockHelper;

/**
 * @todo дополнить бы методы add, update, delete на старом api
 */
abstract class BaseIblockRepository extends BaseRepository implements CompiledEntityRepositoryContract
{
    public readonly int $entityId;

    public function __construct(string|int $iblockCodeOrId)
    {
        if(is_string($iblockCodeOrId)) {
            $iblockCodeOrId = IblockHelper::getIblockIdByCode($iblockCodeOrId);
        }
        $this->entityId = $iblockCodeOrId;
        parent::__construct(IblockHelper::getElementApiTable($iblockCodeOrId));
    }

    public function add(array $data): int
    {
        throw new \RuntimeException("iblock not compatible");
    }

    public function update(int $id, array $data): void
    {
        throw new \RuntimeException("iblock not compatible");
    }

    public function delete(int $id): void
    {
        throw new \RuntimeException("iblock not compatible");
    }
}
