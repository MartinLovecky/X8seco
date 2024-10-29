<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Yuhzel\Xaseco\Core\Types\Challenge;
use Yuhzel\Xaseco\Core\Types\PlayerList;

class CpLiveAdvanced
{
    //private int $numberCps = 0;
    //private float $lastUpdate = 0;
    private array $list = [];

    public function __construct(
        // @phpstan-ignore-next-line
        private Challenge $challenge,
        private PlayerList $playerList
    ) {
    }

    public function onSync(): void
    {
        //$this->getTrackInfo();
        //$this->lastUpdate = $this->getMilliSeconds();
    }

    public function onPlayerConnect(string $login)
    {
        if(array_key_exists($login, $this->playerList->players)) {
            $spectator = $this->playerList->players[$login]->isspectator;
            if(!$spectator) {
                $this->list = $this->playerList->players;
            }
            if(empty($this->list)) {

            }
        }
    }
    // @phpstan-ignore-next-line
    private function getTrackInfo(): void
    {
        //$this->numberCps = $this->challenge->nbCheckpoints - 1;
    }
    // @phpstan-ignore-next-line
    private function getMilliSeconds(): float
    {
        return microtime(true) * 1000;
    }
    // @phpstan-ignore-next-line
    private function managePlayerConnect()
    {
    }
}
