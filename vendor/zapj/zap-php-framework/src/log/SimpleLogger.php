<?php

namespace zap\log;

/**
 * 简单的内置 Logger 实现，当 Monolog 未安装时作为后备
 *
 * 兼容 Monolog\Logger 常用方法签名，Logger 级别 >= 配置级别时才记录
 */
class SimpleLogger
{
    public const DEBUG = 100;
    public const INFO = 200;
    public const NOTICE = 250;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;
    public const ALERT = 550;
    public const EMERGENCY = 600;

    protected string $name;
    protected int $minLevel;
    protected ?string $logFile;

    protected static array $levelNames = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::ALERT => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->minLevel = config('log.level', self::DEBUG);
        $this->logFile = config('log.path', null);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function log(int $level, $message, array $context = []): void
    {
        if ($level < $this->minLevel) {
            return;
        }

        $message = $this->formatMessage($level, $message, $context);
        $this->write($message);
    }

    protected function formatMessage(int $level, $message, array $context): string
    {
        $levelName = static::$levelNames[$level] ?? 'UNKNOWN';
        $date = date('Y-m-d H:i:s');
        $formatted = sprintf('[%s] %s.%s: %s', $date, $this->name, $levelName, (string) $message);

        if (!empty($context)) {
            $formatted .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $formatted;
    }

    protected function write(string $message): void
    {
        if ($this->logFile) {
            $dir = dirname($this->logFile);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            @file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        // 无日志文件时回退到 PHP 错误日志
        if (!$this->logFile) {
            error_log($message);
        }
    }
}
