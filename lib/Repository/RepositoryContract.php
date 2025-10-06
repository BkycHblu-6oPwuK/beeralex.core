<?php

namespace Beeralex\Core\Repository;

use Bitrix\Main\ORM\Query\Query;

/**
 * @property-read class-string<\Bitrix\Main\ORM\Data\DataManager> $entityClass
 */
interface RepositoryContract
{
    public function query(): Query;
}
