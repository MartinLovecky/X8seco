<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Yuhzel\Xaseco\Core\Gbx\GbxClient as Client;
use Yuhzel\Xaseco\Core\Types\Challenge;
use Yuhzel\Xaseco\Services\Basic;

class Cpll
{
    public array $cpll_array = [];
    public bool $cpll_enabled = true;
    public bool $cpll_filter = true;
    public int $cpll_trackcps = 0;

    public function __construct(
        private Client $client,
        private Challenge $challenge
    ) {
    }

    public function onNewChallenge(): void
    {
        $this->cpll_trackcps = $this->challenge->nbchecks - 1;
        $this->cpllReset();
    }

    public function onPlayerConnect(string $login): void
    {
        $message = '{#server}>> {#message}This server is running CPLL, use {#highlite}/cp {#message}and {#highlite}/mycp {#message}to view current standings';
        $this->client->query('ChatSendServerMessageToLogin', Basic::formatColors($message), $login);
    }

    private function cpllReset(): void
    {
        reset($this->cpll_array);
    }
}
