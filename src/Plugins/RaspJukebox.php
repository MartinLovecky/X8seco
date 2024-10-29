<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use RuntimeException;
use Yuhzel\Xaseco\Core\Types\ChatCommand;
use Yuhzel\Xaseco\Core\Types\RaspType;

class RaspJukebox
{
    private array $buffer = [];
    private int $bufferSize = 0;
    private const PLUGIN_NAME = 'RaspJukebox';
    private array $commands = [];

    public function __construct(private RaspType $raspType)
    {
        $this->commands = [
            ['list', [$this, 'list'], 'Lists tracks currently on the server (see: /list help)'],
            ['jukebox', [$this, 'jukebox'], 'Sets track to be played next (see: /jukebox help)'],
            ['autojuke', [$this, 'autojuke'], 'Jukeboxes track from /list (see: /autojuke help)'],
            ['add', [$this, 'add'], 'Adds a track directly from TMX (<ID> {sec})'],
            ['y', [$this, 'y'], 'Votes Yes for a TMX track or chat-based vote'],
            ['history', [$this, 'history'], 'Shows the 10 most recently played tracks'],
            ['xlist', [$this, 'xlist'], 'Lists tracks on TMX (see: /xlist help)']
        ];
    }

    public function onStartup(): void
    {
        ChatCommand::registerCommands($this->commands, self::PLUGIN_NAME, false);
    }

    public function onSync(): void
    {
        $filePath = $this->raspType->trackdir . $_ENV['trackhistFile'];
        if (!is_readable($filePath)) {
            throw new RuntimeException("Failed to open track history file: {$filePath}");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->buffer = array_slice(array_filter($lines), -$this->bufferSize);
    }

    public function list()
    {
        $buffer = $this->buffer;
    }
    public function jukebox()
    {
    }
    public function autojuke()
    {
    }
    public function add()
    {
    }
    public function y()
    {
    }
    public function history()
    {
    }
    public function xlist()
    {
    }
}
