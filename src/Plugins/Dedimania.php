<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\Core\Types\PlayerList;
use Yuhzel\X8seco\Plugins\Checkpoints;
use Yuhzel\X8seco\Core\Xml\{XmlArrayObject, XmlParser};
use Yuhzel\X8seco\Services\{DedimaniaClient, Basic};

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

    public string $mode = 'TA';
    public bool $recsValid = false;
    public array $bannedLogins = [];
    private ?XmlArrayObject $dedi = null;
    private ?XmlArrayObject $masterServer = null;
    //private ?XmlArrayObject $messages = null;

    public function __construct(
        private DedimaniaClient $dediamaniaClient,
        private XmlParser $xmlParser,
        // @phpstan-ignore-next-line
        private Checkpoints $checkpoints,
        private PlayerList $playerList,
<<<<<<< HEAD
    ) {}
=======
        private HttpClient $httpClient,
    ) {
    }
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8

    public function onStartup(): void
    {
        $config = $this->xmlParser->parseXml('dedimania.xml');
        $this->dedi = $config->database;
        $this->masterServer = $config->masterserver_account;
        $this->masterServer->login = $_ENV['dedi_username'];
        $this->masterServer->password = $_ENV['dedi_code'];
        $this->masterServer->nation = $_ENV['dedi_nation'];
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
        $this->dedimaniaLogin();
        Basic::console('------------- (Dedimania) -------------');
    }

    public function onPlayerConnect(string $login)
    {
        $pinfo = $this->dedimania_playerinfo($login);
    }

    public function onNewChallenge()
    {
        // dd($this->dediDB);
    }

    private function dedimaniaLogin(): void
    {
        $params = [
            'Game' => 'TMU',
            'Login' => $this->masterServer->login,
            'Password' => $this->masterServer->password,
            'Tool' => 'Xaseco',
            'Version' => '1.16',
            'Nation' =>  $this->masterServer->nation,
            'Packmask' => 'United'
        ];
<<<<<<< HEAD
=======
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: en-US,en;q=0.8',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded',
            'Origin: http://dedimania.net',
            'Pragma: no-cache',
            'Referer: http://dedimania.net/tmstats/?do=auth',
            'Sec-GPC: 1',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
            'Cookie: punbb_cookie=a%3A2%3A%7Bi%3A0%3Bs%3A8%3A%2215074862%22%3Bi%3A1%3Bs%3A32%3A%22f4e62b1ce858d19f07f7f503fcd0fe3c%22%3B%7D; PHPSESSID=o2qkg4bs7sgfha5n8i81ed5880'
        ];
        //login
        $response = $this->httpClient->post($endpoint, $data, $headers);
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8

        if (!$this->dediamaniaClient->connectionAlive('http://dedimania.net:8002/Dedimania')) {
            $response = $this->dediamaniaClient->authenticate($params);
            if ($response->array_key_exists('faultString')) {
                Basic::console("    !!! \n !!! Error response: {$response['faultString']} \n  !!!");
                return;
            }
            $this->dedi->db = $response;
            Basic::console("* Connection and status ok! {$this->dedi->db->Status}");
        }
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
