<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

class Player
{
    public int $id = 0;
    public int $pid = 0;
    public string $login = '';
    public string $nickname = '';
    public string $ipport = '';
    public string $ip = '';
    public bool $prevstatus = false;
    public bool $isspectator = false;
    public bool $isofficial = false;
    public string $teamname = '';
    public string $zone = '';
    public string $nation = 'CZE';
    public int $ladderrank = 0;
    public float $ladderscore = 0.0;
    public string $client = '';
    public bool $rights = false;
    public string $language = '';
    public string $avatar = '';
    public int $teamid = 0;
    public int $created = 0;
    public int $wins = 0;
    public int $newwins = 0;
    public int $timeplayed = 0;
    public bool $unlocked = false;
    public array $msgs = [];
    public array $pmbuf = [];
    public array $mutelist = [];
    public array $mutebuf = [];
    public array $style = [];
    public array $panels = [];
    public string $speclogin = '';
    public int $dedirank = 0;

    public function setPlayer(XmlArrayObject $playerd): void
    {
        $this->pid = $playerd->PlayerId ?? $this->pid;
        $this->login = $playerd->Login ?? $this->login;
        $this->nickname = $playerd->NickName ?? $this->nickname;
        $this->ipport = $playerd->IPAddress ?? $this->ipport;
        $this->ip = preg_replace('/:\d+/', '', $playerd->IPAddress) ?? $this->ip;
        $this->isspectator = $playerd->IsSpectator ?? $this->isspectator;
        $this->isofficial = $playerd->IsInOfficialMode ?? $this->isofficial;
        $this->teamname = $playerd->LadderStats->TeamName ?? $this->teamname;
        $this->zone = strpos($playerd->Path, '|') ? substr($playerd->Path, 6) : $playerd->Path;
        $this->nation = strpos($playerd->Path, '|') ? explode('|', $playerd->Path)[1] : $this->nation;
        $this->ladderrank = $playerd->LadderStats->PlayerRankings[0]->Ranking ?? $this->ladderrank;
        $this->ladderscore = round($playerd->LadderStats->PlayerRankings[0]->Score, 2);
        $this->client = $playerd->ClientVersion ?? $this->client;
        $this->rights = ($playerd->OnlineRights === 3);
        $this->language = $playerd->Language ?? $this->language;
        $this->avatar = $playerd->Avatar->FileName ?? $this->avatar;
        $this->teamid = $playerd->TeamId ?? $this->teamid;
        $this->created = time();
    }

    public function getWins(): int
    {
        return $this->wins + $this->newwins;
    }

    public function getTimePlayed(): int
    {
        return $this->timeplayed + $this->getTimeOnline();
    }

    public function getTimeOnline(): int
    {
        return $this->created > 0 ? time() - $this->created : 0;
    }
}
