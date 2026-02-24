<?php
declare(strict_types=1);
namespace Beeralex\Core\Logger;

use Psr\Log\LoggerInterface;

/**
 * @deprecated Здесь думаю лучше использовать Bitrix\Main\Diag\Logger::create или сторонние библиотеки для логирования, например Monolog, так как они более гибкие и поддерживают множество форматов и обработчиков.
 */
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
