<?php
declare(strict_types=1);
namespace Beeralex\Core\Logger;

use Psr\Log\LoggerInterface;

class FileLoggerFactory implements LoggerFactoryContract
{
    protected string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function channel(string $name = 'default'): LoggerInterface
    {
        return new FileLogger($name, $this->baseDir);
    }
}
