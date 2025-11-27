<?php
declare(strict_types=1);
namespace Beeralex\Core\Repository;

use Beeralex\Core\Model\SectionTableFactory;
use Beeralex\Core\Service\IblockService;

class IblockSectionRepository extends Repository implements CompiledEntityRepositoryContract
{
    public readonly int $entityId;

    public function __construct(string|int $iblockCodeOrId)
    {
        $iblockService = service(IblockService::class);
        $sectionFactory = service(SectionTableFactory::class);
        if (is_string($iblockCodeOrId)) {
            $iblockCodeOrId = $iblockService->getIblockIdByCode($iblockCodeOrId);
        }

        $this->entityId = $iblockCodeOrId;

        parent::__construct($sectionFactory->compileEntityByIblock($iblockCodeOrId), true);
    }
}
