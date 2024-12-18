<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\App;

use Yuhzel\X8seco\App\PluginManager;
use Yuhzel\X8seco\Services\Aseco;
use Yuhzel\X8seco\Core\Types\{Player, Server};
use Yuhzel\X8seco\Core\Xml\{XmlArrayObject, XmlParser};

class X8seco
{
    public string $path = '';
    public bool $startup_phase = true;
    public bool $warmup_phase = false;
    public bool $changingmode = false;
    public bool $debug = false;
    public int $restarting = 0;
    public int $currstatus = 0;
    public int $currsecond = 0;
    public int $prevsecond = 0;
    public array $panels = [];
    public ?XmlArrayObject $settings = null;

    public function __construct(
        private Player $player,
        private PluginManager $pluginManager,
        private Server $server,
        private XmlParser $xmlParser,
    ) {
    }

    public function run()
    {
        $config = Aseco::path() . "app/xml/config.xml";
        Aseco::console('[X8seco] Load settings from [{1}]', $config);
        $this->loadSettings();

        Aseco::console('[X8seco] Load admin/ops lists [{1}]', $this->settings->adminops_file);
        $this->readLists();

        Aseco::console('[X8seco] Load banned IPs list [{1}]', $this->settings->bannedips_file);
        $this->readIPs();

        if (!$this->connect()) {
            trigger_error('Connection could not be established!', E_USER_ERROR);
        }
        Aseco::console('Connection established successfully!');

        if (!empty($this->settings->lock_password->storage)) {
            Aseco::console("[X8seco] Locked admin commands & features with password '{1}'", $this->settings->lock_password);
        }

        $this->pluginManager->onStartup();
        $this->panels = $this->pluginManager->getPlugin('Panels')->panels;

        $this->serverSync();

        $this->sendHeader();

        // while(true){
        //     $this->executeCallbacks();
        //     $this->executeCalls();
        // }
    }

    private function loadSettings(): void
    {
        $this->settings = $this->xmlParser->parseXml('config.xml')->aseco;
        unset($this->settings->message);
    }

    private function readLists(): void
    {
        $this->settings->adminops = $this->xmlParser->parseXml($this->settings->adminops_file);
    }

    private function readIPs(): void
    {
        $xml = $this->xmlParser->parseXml($this->settings->bannedips_file);
        if ($xml->array_key_exists('ipaddress')) {
            $this->settings->bannedips = $xml;
        } else {
            unset($xml);
        }
    }

    private function connect(): bool
    {
        if ($this->server->ip && $this->server->port && $this->server->login && $this->server->pass) {
            Aseco::console(
                'Try to connect to TM dedicated server on {1}:{2} timeout {3}s',
                $this->server->ip,
                $this->server->port,
                $this->server->timeout
            );

            if (!$this->server->client->init()) {
                trigger_error('[' . $this->server->client->getErrorCode() . '] init - ' . $this->server->client->getErrorMessage(), E_USER_WARNING);
                return false;
            }

            Aseco::console(
                "Try to authenticate with login '{1}' and password '{2}'",
                $this->server->login,
                $this->server->pass
            );

            if (!$this->server->client->query('Authenticate', $this->server->login, $this->server->pass)) {
                trigger_error('[' . $this->server->client->getErrorCode() . '] Authenticate - ' . $this->server->client->getErrorMessage(), E_USER_WARNING);
                return false;
            }

            $this->server->client->query('EnableCallbacks', true);
            $this->waitServerReady();

            return true;
        }

        return false;
    }

    private function waitServerReady(): void
    {
        $status = $this->server->client->query('GetStatus');
        if ($status->Code != 4) {
            Aseco::console("Waiting for dedicated server to reach status 'Running - Play'...");
            Aseco::console('Status: ' . $status->Name);
            $timeout = 0;
            $laststatus = $status->Name;
            while ($status->Code != 4) {
                sleep(1);
                $status = $this->server->client->query('GetStatus');
                if ($laststatus != $status->Name) {
                    Aseco::console('Status: ' . $status->Name);
                    $laststatus = $status->Name;
                }
                if (isset($this->server->timeout) && $timeout++ > $this->server->timeout) {
                    trigger_error('Timed out while waiting for dedicated server!', E_USER_ERROR);
                }
            }
        }
    }

    private function serverSync()
    {

        $this->server->client->query('SendHideManialinkPage');
        $this->server->setServerInfo();
        $this->server->gameInfo->setGameInfo();
        $this->server->challenge->setChallangeInfo();
        $this->currstatus = $this->server->client->query('GetStatus')->Code;
        $this->pluginManager->onSync();
        $players = $this->server->client->query('GetPlayerList', 300, 0, 2);
        $players = Aseco::filterResponse($players);

        if (!empty($players)) {
            foreach ($players as $player) {
                $this->playerConnect($player);
            }
        }
    }

    private function playerConnect(XmlArrayObject $playerObject)
    {
        $playerd = $this->server->client->query('GetDetailedPlayerInfo', $playerObject->Login);
        $version = str_replace(')', '', preg_replace('/.*\(/', '', $playerd->ClientVersion));

        if ($version === '') {
            $message = str_replace('{br}', "\n", Aseco::getChatMessage('clent_error'));
        }
        //SECTION - this can be improved
        $this->player->setPlayer($playerd);
        $this->player->panels['admin'] = $this->panels['admin'];
        $this->player->panels['donate'] = $this->panels['donate'];
        $this->player->panels['records'] = $this->panels['records'];
        $this->player->panels['vote'] = $this->panels['vote'];
        $this->server->players->addPlayer($this->player);
        //!SECTION
        Aseco::console(
            '<< player {1} joined the game [{2} : {3} : {4} : {5} : {6}]',
            $this->player->pid,
            $this->player->login,
            $this->player->nickname,
            $this->player->nation,
            $this->player->ladderrank,
            $this->player->ip
        );
        // version eh
        preg_match('/\((.*?)\)/', $playerd->ClientVersion, $matches);
        $version = $matches[1];

        $message = Aseco::formatText(
            Aseco::getChatMessage('welcome'),
            Aseco::stripColors($this->player->nickname),
            $this->server->name,
            $version
        );

        $message = preg_replace('/XASECO.+' . $playerd->ClientVersion . '/', '$l[http://www.gamers.org/tmn/]$0$l', $message);
        $message = str_replace('{br}', "\n", Aseco::formatColors($message));

        $this->server->client->query('ChatSendServerMessageToLogin', str_replace("\n", "", $message), $this->player->login);

        //$cur_record = $this->server->records->getRecord(0);
        // if ($cur_record !== null && $cur_record->score > 0) {
        //     $message = Aseco::formatText(
        //         Aseco::getChatMessage('record_current'),
        //         Aseco::stripColors($this->server->challenge->name),
        //         Aseco::formatTime($cur_record->score),
        //         Aseco::stripColors($cur_record->player->nickname)
        //     );
        // } else {
        //     $message = Aseco::formatText(
        //         Aseco::getChatMessage('record_none'),
        //         Aseco::stripColors($this->server->challenge->name)
        //     );
        // query('ChatSendServerMessageToLogin', Aseco::formatColors($message), $this->player->login);
        // }
        if($this->settings->show_recs_before === 2){
            $chatCmd = $this->pluginManager->getPlugin('ChatCmd');
            $chatCmd->trackrecs($this->player->login, 1);
        }
        //dd($this->settings);
        //FIXME we should check if player exist in players table
        //TODO - onPlayerConnect onPlayerConnect2
        $this->pluginManager->onPlayerConnect($this->player->login);
    }

    private function sendHeader(): void
    {
        Aseco::console('###############################################################################');
        Aseco::console('  XASECO v 2.11.26 running on {1}:{2}', $this->server->ip, $this->server->port);
        Aseco::console('  Name   : {1} - {2}', Aseco::stripColors($this->server->name, false), $this->server->serverLogin);
        Aseco::console(
            '  Game   : {1} {2} - {3} - {4}',
            $this->server->game,
            'United',
            $this->server->packmask,
            'TimeAttack'
        );
        Aseco::console('  Version: {1} / {2}', $this->server->version, $this->server->build);
        Aseco::console('  Authors: Florian Schnell & Assembler Maniac');
        Aseco::console('  Re-Authored: Xymph');
        Aseco::console('  Remake: Yuhzel');
        Aseco::console('###############################################################################');

        $startup_msg = Aseco::formatText(
            Aseco::getChatMessage('startup'),
            '2.11.26',
            $this->server->ip,
            $this->server->port
        );
        $this->server->client->query('ChatSendServerMessage', Aseco::formatColors($startup_msg));
    }
    //TODO -
    private function executeCallbacks()
    {
        dump("Callbacks:", $this->server->client->readCB());
    }
    //TODO
    private function executeCalls()
    {
        return $this->server->client->multiQuery();
    }
}
