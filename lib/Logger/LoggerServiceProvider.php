<?php
namespace Beeralex\Core\Logger;

use Beeralex\Core\DI\AbstractServiceProvider;

class LoggerServiceProvider extends AbstractServiceProvider
{
    public function registerServices(): void
    {
        $this->bind(LoggerFactoryContract::class, FileLoggerFactory::class, fn() => [$_SERVER['DOCUMENT_ROOT'] . '/local/logs']);
    }
}