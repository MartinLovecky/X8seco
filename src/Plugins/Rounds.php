<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

class Rounds
{
<<<<<<< HEAD
    public int $roundsCount = 0;
    public array $roundTimes = [];
    public array $roundPbs = [];
    
    // called in onNewChallenge, onRestartChallenge
=======
    public function __construct(
        public int $roundsCount = 0,
        public array $roundTimes = [],
        public array $roundPbs = []
    ) {
    }

>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
    public function onSync(): void
    {
        $this->roundsCount = 0;
        $this->roundTimes = [];
        $this->roundPbs = [];
    }
}
