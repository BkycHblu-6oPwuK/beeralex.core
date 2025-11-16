<?php
declare(strict_types=1);
namespace Beeralex\Core\Repository;

use Beeralex\Core\Service\HlblockService;

class HighloadRepository extends Repository implements CompiledEntityRepositoryContract
{
    public readonly int $entityId;

    public function __construct(string|int $highloadNameOrId)
    {
        $hlblockService = service(HlblockService::class);
        if (is_string($highloadNameOrId)) {
            $highloadNameOrId = $hlblockService->getHlblockIdByName($highloadNameOrId);
        }
        $this->entityId = $highloadNameOrId;
        parent::__construct($hlblockService->getHlblockById($highloadNameOrId));
    }
}
