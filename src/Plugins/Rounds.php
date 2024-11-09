<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

class Rounds
{
    public int $roundsCount = 0;
    public array $roundTimes = [];
    public array $roundPbs = [];

    // called in onNewChallenge, onRestartChallenge
    public function onSync(): void
    {
        $this->roundsCount = 0;
        $this->roundTimes = [];
        $this->roundPbs = [];
    }
}
