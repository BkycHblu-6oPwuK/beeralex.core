<?php

namespace Beeralex\Core\Repository;

interface IblockRepositoryContract extends CompiledEntityRepositoryContract
{
    public function getIblockSectionRepository(?callable $factory = null): IblockSectionRepository;
}
