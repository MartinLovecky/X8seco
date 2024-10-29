<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Core\Types;

use Yuhzel\Xaseco\Core\Types\Player;

class PlayerList
{
    public array $players = [];

    public function nextPlayer(): mixed
    {
        if (!empty($this->players)) {

            $player_item = current($this->players);
            next($this->players);
            return $player_item;
        }

        return $this->resetPlayers();
    }

    public function resetPlayers(): mixed
    {
        return reset($this->players);
    }

    public function addPlayer(Player $player): void
    {
        if (!empty($player->login)) {
            $this->players[$player->login] = $player;
        }
        // we could maybe log info message but eh why
    }

    public function removePlayer(string $login): void
    {
        if (isset($this->players[$login])) {
            unset($this->players[$login]);
        }
        // we could maybe log info message but eh why
    }

    public function getPlayer(string $login): Player|null
    {
        if (isset($this->players[$login])) {
            return $this->players[$login];
        }
        // we could maybe log info message but why
        // would you try get player that is not on server
        return null;
    }
}
