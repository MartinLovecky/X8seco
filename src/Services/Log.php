<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Services;

use Monolog\Level;
use LogicException;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;

class Log
{
    private static string $channelName = 'app_logger';
    private static array $loggers = [];

    public static function init(string $logChannelName, string $fileName, bool $setAsDefault = false): void
    {
        if (isset(self::$loggers[$logChannelName])) {
            return;
        }

        $path = Aseco::path() . "/app/logs/{$fileName}.log";
        $logger = new Logger($logChannelName);
        $logger->pushHandler(new RotatingFileHandler($path, 7, Level::Debug));
        $logger->pushProcessor(new IntrospectionProcessor(Level::Debug));
        self::$loggers[$logChannelName] = $logger;

        if ($setAsDefault) {
            self::$channelName = $logChannelName;
        }
    }

    public static function getLogger(string $logChannelName): Logger
    {
        if (!isset(self::$loggers[$logChannelName])) {
            throw new LogicException("Logger for channel '{$logChannelName}' not initialized.");
        }

        return self::$loggers[$logChannelName];
    }

    public static function info(string $message, array $context = []): void
    {
        self::getLogger(self::$channelName)->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getLogger(self::$channelName)->error($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getLogger(self::$channelName)->warning($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::getLogger(self::$channelName)->debug($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::getLogger(self::$channelName)->critical($message, $context);
    }
}
