<?php
namespace Beeralex\Core\Logger;

use Psr\Log\LoggerInterface;

/**
 * @deprecated
 */
interface LoggerFactoryContract
{
    public function channel(string $name = 'default'): LoggerInterface;
}
