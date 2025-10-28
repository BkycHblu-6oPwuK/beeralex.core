<?php
namespace Beeralex\Core\Logger;

use Psr\Log\LoggerInterface;

interface LoggerFactoryContract
{
    public function channel(string $name = 'default'): LoggerInterface;
}
