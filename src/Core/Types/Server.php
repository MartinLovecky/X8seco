<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use Yuhzel\X8seco\Services\Aseco;
use Yuhzel\X8seco\Core\Gbx\GbxClient as Client;
use Yuhzel\X8seco\Core\Types\{
    Challenge,
    PlayerList,
    RecordList,
    GameInfo
};

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
    public int $timeout = 80;
    public int $startTime = 0;
    public int $id = 0;
    public int $rights = 0;
    public float $laddermin = 0.0;
    public float $laddermax = 0.0;
    public array $mutelist = [];
    public string $gamestate = self::RACE;
    public string $packmask = 'Stadium';
    public string $name = '';
    public string $comment = '';
    public int $currentMaxPlayers = 0;
    public int $currentMaxSpectators = 0;
    public int $currentLadderMode = 0;
    public int $currentVehicleNetQuality = 0;
    public int $currentCallVoteTimeOut = 0;

    private string $gamePath = '';

    public function __construct(
        public Challenge $challenge,
        public PlayerList $players,
        public RecordList $records,
        public GameInfo $gameInfo,
        public Client $client,
    ) {
        $this->startTime = time();
        $this->gamePath = Aseco::path(3);
        $this->gamedir = "{$this->gamePath}GameData/";
        $this->trackdir = "{$this->gamePath}GameData/Tracks/";
        $this->login = $_ENV['admin_login'];
        $this->pass = $_ENV['admin_password'];
        $this->serverLogin = $_ENV['server_login'];
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
        $this->id = $response['PlayerId'];
        $this->nickname = $response['NickName'];
        $this->zone = mb_strlen($response['Path']) > 5 ? substr($response['Path'], 6) : substr($response['Path'], 0);
        $this->rights = $response['OnlineRights'];
        $this->laddermin = $this->client->query('GetLadderServerLimits')['LadderServerLimitMin'];
        $this->laddermax = $this->client->query('GetLadderServerLimits')['LadderServerLimitMax'];
        $response = $this->client->query('GetServerOptions');
        $this->name = $response['Name'];
        $this->comment = $response['Comment'];
        $this->currentMaxPlayers = $response['CurrentMaxPlayers'];
        $this->currentMaxSpectators = $response['CurrentMaxSpectators'];
        $this->currentLadderMode = $response['CurrentLadderMode'];
        $this->currentVehicleNetQuality = $response['CurrentVehicleNetQuality'];
        $this->currentCallVoteTimeOut = $response['CurrentCallVoteTimeOut'];
    }
}
