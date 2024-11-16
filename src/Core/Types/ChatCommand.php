<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use Yuhzel\X8seco\Services\Aseco;

class ChatCommand
{
    public static array $commands = [];

    public static function registerCommands(
        array $commands,
        string $plugin,
        bool $isAdmin = false
    ): void {
        foreach ($commands as $command) {
            [$name, $callback, $description] = $command;
            if (method_exists($callback[0], $callback[1])) {
                self::$commands[$name] = [
                    'callback' => $callback,
                    'help' => $description,
                    'isAdmin' => $isAdmin,
                    'plugin' => $plugin,
                ];
            } else {
                Aseco::console("Invalid callback for command: {$name} in {$plugin}");
            }
        }
    }

    public static function getCommandsByPlugin(string $plugin): array
    {
        return array_filter(self::$commands, fn ($command) => $command['plugin'] === $plugin);
    }

    public static function getHelp(?string $plugin = null): array
    {
        $filteredCommands = $plugin
            ? self::getCommandsByPlugin($plugin)
            : self::$commands;

        $helpTexts = array_map(fn ($cmd) => $cmd['help'], $filteredCommands);
        $helpTexts['ptr'] = 1;
        $helpTexts['header'] = "Currently supported commands:";
        $helpTexts['width'] = [1.3, 0.3, 1.0];
        $helpTexts['icon'] = ['Icons64x64_1', 'TrackInfo', -0.01];

        return $helpTexts;
    }
}
