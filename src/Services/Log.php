<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Services;

use Monolog\Level;
use LogicException;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;

class Log
{
    private static ?Logger $logger = null;

    public static function init(): void
    {
        if (self::$logger !== null) {
            return;
        }

        $path = Basic::path() . '/app/logs/log.log';
        self::$logger = new Logger('app_logger');
        self::$logger->pushHandler(new RotatingFileHandler($path, 7, Level::Debug));
        self::$logger->pushProcessor(new IntrospectionProcessor(Level::Debug));
    }

    private static function getLogger(): Logger
    {
        if (self::$logger === null) {
            throw new LogicException('Logger not initialized.');
        }
        return self::$logger;
    }

    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }
}
