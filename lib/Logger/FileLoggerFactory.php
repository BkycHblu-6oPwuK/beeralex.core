<?php

namespace Beeralex\Core\Logger;

class FileLoggerFactory implements LoggerFactoryInterface
{
    protected string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function channel(string $name): LoggerInterface
    {
        return new FileLogger($name, $this->baseDir);
    }
}
