<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

class Rounds
{
    public function __construct(
        public int $roundsCount = 0,
        public array $roundTimes = [],
        public array $roundPbs = []
    ) {}

    public function onSync(): void
    {
        $this->roundsCount = 0;
        reset($this->roundTimes);
        reset($this->roundTimes);
    }
}
