<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\Core\Gbx\GbxClient;
use Yuhzel\X8seco\Core\Types\Challenge;
use Yuhzel\X8seco\Core\Types\PlayerList;

class CpLiveAdvanced
{
    private int $numberCps = 0;
    private float $lastUpdate = 0;
    private array $list = [];

    public function __construct(
        private GbxClient $client,
        private Challenge $challenge,
        private PlayerList $playerList
    ) {
    }

    public function onSync(): void
    {
        $this->getTrackInfo();
        $this->lastUpdate = $this->getMilliSeconds();
    }

    public function onPlayerConnect(string $login)
    {
        if (array_key_exists($login, $this->playerList->players)) {
            $spectator = $this->playerList->players[$login]->isspectator;
            if (!$spectator) {
                $this->list = $this->playerList->players;
            }
        }
        if(empty($this->list))
        {
            usort($this->list, [$this, "compareCpNumbers"]);
            $this->list = array_slice($this->list, 0, 12);
        }
        $this->bindToggleKey($login);
    }

    public function compareCpNumbers($a, $b): int
    {
		if ($a["CPNumber"] == $b["CPNumber"]) {
			return 0;
		}
	
		return ($a["CPNumber"] > $b["CPNumber"]) ? -1 : 1;
	}

    private function bindToggleKey(string $login): void
    {
        $xml = '<manialink id="1928380">';
        $xml .= '<quad action="01928390" actionkey="1" sizen="0 0"  posn="70 70 1"/>';
		$xml .= '</manialink>';
        $this->client->addCall("SendDisplayManialinkPageToLogin", [$login, $xml, 0, false]);
    }

    // @phpstan-ignore-next-line
    private function getTrackInfo(): void
    {
        $this->numberCps = $this->challenge->nbCheckpoints - 1;
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
