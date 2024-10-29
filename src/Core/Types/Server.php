<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Core\Types;

use Yuhzel\Xaseco\Services\Basic;
use Yuhzel\Xaseco\Core\Gbx\GbxClient as Client;
use Yuhzel\Xaseco\Core\Types\{
    Challenge,
    PlayerList,
    RecordList,
    GameInfo
};
/**
 * @property null|string $name
 */
class Server
{
    public const string RACE  = 'race';
    public string $ip = '127.0.0.1';
    public int $port = 5009;
    public string $login = '';
    public string $pass = '';
    public string $serverLogin = '';
    public string $game = 'TMF';
    public string $version = '2.11.26';
    public string $build = '2011-02-21';
    public string $nickname = '';
    public string $gamedir = '';
    public string $trackdir = '';
    public string $zone = '';
    public int $timeout = 180;
    public int $startTime = 0;
    public int $id = 0;
    public int $rights = 0;
    public float $laddermin = 0.0;
    public float $laddermax = 0.0;
    public array $mutelist = [];
    public string $gamestate = self::RACE;
    public string $packmask = 'Stadium';
    private string $gamePath = '';

    public function __construct(
        public Challenge $challenge,
        public PlayerList $players,
        public RecordList $records,
        public GameInfo $gameInfo,
        public Client $client,
    ) {
        $this->startTime = time();
        $this->gamePath = Basic::path(3);
        $this->gamedir = "{$this->gamePath}GameData/";
        $this->trackdir = "{$this->gamePath}GameData/Tracks/";
        $this->login = $_ENV['adminLogin'];
        $this->pass = $_ENV['adminPassword'];
        $this->serverLogin = $_ENV['serverLogin'];
    }

    public function __get(string $name): mixed
    {
        return $this->$name ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }

    public function setServerInfo(): void
    {
        $response = $this->client->query('GetDetailedPlayerInfo', $this->serverLogin);
        $this->id = $response->PlayerId;
        $this->nickname = $response->NickName;
        $this->zone = mb_strlen($response->Path) > 5 ? substr($response->Path, 6) : substr($response->Path, 0);
        $this->rights = $response->OnlineRights;
        $this->laddermin = $this->client->query('GetLadderServerLimits')->LadderServerLimitMin;
        $this->laddermax = $this->client->query('GetLadderServerLimits')->LadderServerLimitMax;
        $this->packmask = $this->client->query('GetServerPackMask');
        foreach ($this->client->query('GetServerOptions') as $key => $value) {
            $this->__set(lcfirst($key), $value);
        }
    }
}
