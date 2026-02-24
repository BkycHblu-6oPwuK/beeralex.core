<?php
declare(strict_types=1);
namespace Beeralex\Core\Logger;

use Beeralex\Core\Traits\PathNormalizerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @deprecated Думаю лучше использовать сторонние библиотеки для логирования, например Monolog, так как они более гибкие и поддерживают множество форматов и обработчиков. Либо если подойдет, то реализацию Bitrix \Bitrix\Main\Diag\FileLogger
 */
class FileLogger implements LoggerInterface
{
    use PathNormalizerTrait;

    protected string $logFile;
    protected string $channel;

    public function __construct(string $channel, string $baseDir)
    {
        $this->channel = $channel;
        $normalizedDir = $this->normalizeBaseDir($baseDir);
        if (!is_dir($normalizedDir)) {
            mkdir($normalizedDir, 0777, true);
        }
        $this->logFile = rtrim($normalizedDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $channel . '.log';
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param string $level in Psr\Log\LogLevel
     */
    public function log($level, $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $message = $this->interpolate($message, $context);
        $contextString = !empty($context) ? print_r($context, true) : '';

        file_put_contents(
            $this->logFile,
            "[$date] $level: $message " . ($contextString ? "\nContext: " . $contextString : '') . PHP_EOL,
            FILE_APPEND
        );
    }

    protected function interpolate(string $message, array $context = []): string
    {
        foreach ($context as $key => $value) {
            if (!is_scalar($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
            $message = str_replace("{" . $key . "}", (string) $value, $message);
        }
        return $message;
    }
}
