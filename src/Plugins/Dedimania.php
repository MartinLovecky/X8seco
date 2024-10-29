<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Yuhzel\Xaseco\Core\Types\PlayerList;
use Yuhzel\Xaseco\Plugins\Checkpoints;
use Yuhzel\Xaseco\Services\Basic;
use Yuhzel\Xaseco\Services\HttpClient;
use Yuhzel\Xaseco\Core\Xml\{XmlArrayObject, XmlParser};

class Dedimania
{
    public array $dedi_db_defaults = [
        'Name' => 'Dedimania',
        'LogNews' => false,
        'ShowWelcome' => true,
        'ShowMinRecs' => 8,
        'ShowRecsBefore' => 1,
        'ShowRecsAfter' => 1,
        'ShowRecsRange' => true,
        'DisplayRecs' => true,
        'RecsInWindow' => false,
        'ShowRecLogins' => true,
        'LimitRecs' => 10,
    ];

    // how many seconds before retrying connection
    public $dedi_timeout = 1800;  // 30 mins
    // how many seconds before reannouncing server
    public $dedi_refresh = 240;   // 4 mins
    // minimum author & finish times that are still accepted
    public $dedi_minauth = 8000;  // 8 secs
    public $dedi_mintime = 6000;  // 6 secs
    public $dedi_debug = 0;  //max debug level = 5:
    public string $mode = 'TA';
    public bool $recsValid = false;
    public array $bannedLogins = [];
    private ?XmlArrayObject $dediDB = null;
    private ?XmlArrayObject $masterServer = null;
    //private ?XmlArrayObject $messages = null;

    public function __construct(
        private XmlParser $xmlParser,
        // @phpstan-ignore-next-line
        private Checkpoints $checkpoints,
        private PlayerList $playerList,
        private HttpClient $httpClient,
    ) {
    }

    public function onStartup(): void
    {
        $config = $this->xmlParser->parseXml('dedimania.xml');
        $this->dediDB = $config->database;
        $this->masterServer = $config->masterserver_account;
        $this->masterServer->login = $_ENV['dediUsername'];
        $this->masterServer->password = $_ENV['dediCode'];
        $this->masterServer->nation = $_ENV['dediNation'];
        //$this->messages = $config->messages;
        if (
            $this->masterServer->login === '' ||
            $this->masterServer->login === 'YOUR_SERVER_LOGIN' ||
            $this->masterServer->password === '' ||
            $this->masterServer->password === 'YOUR_SERVER_PASSWORD' ||
            $this->masterServer->nation === '' ||
            $this->masterServer->nation === 'YOUR_SERVER_NATION'
        ) {
            trigger_error('Dedimania not configured! <masterserver_account> contains default or empty value(s)', E_USER_ERROR);
        }

        Basic::console('************* (Dedimania) *************');
        $this->dedimania_connect();
        Basic::console('------------- (Dedimania) -------------');
        $dedi_lastsent = time();
    }

    public function onPlayerConnect(string $login)
    {
        $pinfo = $this->dedimania_playerinfo($login);
    }

    public function onNewChallenge()
    {
        // dd($this->dediDB);
    }

    private function dedimania_connect()
    {
        $time = time();
        Basic::console('* Dataserver connection on ' . $this->dediDB['name'] . ' ...');
        Basic::console('* Try connection on ' . $this->dediDB['url'] . ' ...');
        //TODO establish Dedimania connection and login
        //dd($this->dediDB);
        $endpoint = 'http://dedimania.net/SITE/login.php';
        $data = [
            'action' => 'in',
            'login' => $_ENV['dediUsername'],
            'code' => $_ENV['dediCode'],
            'game' => 'TMF',
        ];
        $response = $this->httpClient->post($endpoint, $data);
        //dd($response);
    }

    private function dedimania_playerinfo(string $login): array
    {
        $player = $this->playerList->getPlayer($login);
        return [
            'Login' => $player->login,
            'Nation' => $player->nation,
            'TeamName' => $player->teamname,
            'TeamId' => $player->teamid,
            'IsSpec' => $player->isspectator,
            'Ranking' => $player->ladderrank,
            'IsOff' => $player->isofficial
        ];
    }
}
