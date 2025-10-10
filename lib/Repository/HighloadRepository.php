<?php

namespace Beeralex\Core\Repository;

use Beeralex\Core\Helpers\HlblockHelper;

class HighloadRepository extends Repository implements CompiledEntityRepositoryContract
{
    public readonly int $entityId;

    public function __construct(string|int $highloadNameOrId)
    {
        if (is_string($highloadNameOrId)) {
            $highloadNameOrId = HlblockHelper::getHlblockIdByName($highloadNameOrId);
        }
        $this->entityId = $highloadNameOrId;
        parent::__construct(HlblockHelper::getHlblockById($highloadNameOrId));
    }
}
