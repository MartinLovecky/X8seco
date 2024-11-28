<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use RuntimeException;
use Yuhzel\X8seco\Core\Gbx\GbxClient;
use Yuhzel\X8seco\Core\Xml\{XmlArrayObject, XmlParser};
use Yuhzel\X8seco\Core\Types\{Player, PlayerList, Challenge};
use Yuhzel\X8seco\Services\Aseco;

class ManiaKarma
{
    private ?XmlArrayObject $config = null;
    private array $karma = [];

    public function __construct(
        private XmlParser $xmlParser,
        private Challenge $challenge,
        private GbxClient $client,
        private PlayerList $playerList
    ) {
    }

    public function onSync(): void
    {
        $this->config = $this->xmlParser->parseXml('mania_karma.xml');
        $this->config->manialinkId = 911;
        $this->config->retryTime = 0;
        $this->config->retryWait = 600;
        $this->config->login = $_ENV['server_login'];
        Aseco::console('************************(ManiaKarma)*************************');
        Aseco::console('plugin.mania_karma.php/ 2.11.26 for X8seco');
        Aseco::console(" => Set Server location to {$this->config->nation}");
        Aseco::console(" => Trying to authenticate with central database {$this->config->urls->api_auth}");

        // http://worldwide.mania-karma.com/api/tmforever-trackmania-v4.php?Action=Auth&login=%s&name=%s&game=%s&zone=%s&nation=%s
        //$this->httpClient->baseUrl = "http://worldwide.mania-karma.com/api/";
        // $response = $this->httpClient->get("tmforever-trackmania-v4.php", [
        //     'Action' => 'Auth',
        //     'login'  => urlencode($this->config->login),
        //     'name'   => base64_encode($_ENV['server_name']),
        //     'game'   => urlencode('TMF'),
        //     'zone'   => urlencode('World'),
        //     'nation' => urlencode($this->config->nation)
        // ]);

        //NOTE - We could modify XML RPC parser to handle this
        $responseData = null;
        //$this->httpClient->xmlResponse($response);
        //FIXME : Global karma is fucked use local one
        if ($responseData) {
            $status = $responseData['status'];
            switch ($status) {
                case '704':
                    Aseco::console("Error: Authentication failed. Please check your credentials.");
                    break;
                case '200':
                    Aseco::console("Success");
                    break;
                default:
                    # code...
                    break;
            }
        }
        Aseco::console('**********************************************************');

        // $this->config->reminder_window->race->pos_x;
        // $this->config->karma_widget->time_attack;

        $this->config->templates = $this->loadTeamplates();
        $this->config->currentMap  = $this->currentMapInfo();

        //TODO (yuhzel) Aseco::startup -> true when startup
        if (Aseco::$startupPhase) {
            $this->setEmptyKarma();
            $this->karma['uid']            = $this->config->currentMap['uid'];
            $this->karma['name']           = $this->config->currentMap['name'];
            $this->karma['author']         = $this->config['CurrentMap']['author'];
            $this->karma['env']            = $this->config['CurrentMap']['environment'];
            $this->karma['tmx']            = $this->challenge->id;
            $this->karma['new']['players'] = [];
        }

        $this->calculateKarma();

        $this->config->skeleton = $this->buildKarmaWidget();

        if ($this->config->retryTime === 0) {

            $this->sendWidgetCombination(['hide_window'], false);

            foreach ($this->playerList->players as $player) {
                $this->sendWidgetCombination(['player_marker'], $player);
            }

            $this->sendConnectionStatus(true, false);
        }

        $this->config->messages['karma_help'] = str_replace('{br}', "\n", Aseco::formatColors($this->config->messages['karma_help']));
    }

    private function loadTeamplates(): string
    {
        $templatePath = Aseco::path() . 'app/xml/maniaTemplates/costum/maniaKarma.xml';
        $template = @file_get_contents($templatePath);

        if (!$template) {
            throw new RuntimeException("Unable to load the template file: {$templatePath}");
        }

        $placeholders = [
            '%manialink_id%' => $this->config->manialinkId,
            // '%window_title%' => 'My Window Title', // Replace with actual title
            // '%version%' => '1.0.0', // Replace with actual version
            // '%prev_next_buttons%' => '<button>Previous</button><button>Next</button>' // Replace with actual buttons
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    private function currentMapInfo(): array
    {
        $gbx =  $this->challenge->gbx;
        $map = [];
        $map['id']            = false;
        $map['uid']           = $gbx->uid;
        $map['name']          = $gbx->name;
        $map['author']        = $gbx->author;
        $map['authortime']    = $gbx->authorTime;
        $map['authorscore']   = 0;
        $map['nblaps']        = $gbx->nbLaps;
        $map['nbchecks']      = $this->challenge->nbCheckpoints;
        $map['mood']          = $gbx->mood;
        $map['environment']   = $gbx->envir;
        $map['filename']      = $this->challenge->fileName;

        return $map;
    }

    private function setEmptyKarma(): void
    {
        $this->karma['uid'] = false;
        $this->karma['players'] = [];
        $this->karma['votes']['karma'] = 0;
        $this->karma['votes']['total'] = 0;
        $this->karma['fantastic']['percent'] = 0;
        $this->karma['fantastic']['count'] = 0;
        $this->karma['beautiful']['percent'] = 0;
        $this->karma['beautiful']['count'] = 0;
        $this->karma['good']['percent'] = 0;
        $this->karma['good']['count'] = 0;
        $this->karma['bad']['percent'] = 0;
        $this->karma['bad']['count'] = 0;
        $this->karma['poor']['percent'] = 0;
        $this->karma['poor']['count'] = 0;
        $this->karma['waste']['percent'] = 0;
        $this->karma['waste']['count'] = 0;

        foreach ($this->playerList->players as $player) {
            $this->karma['players'][$player->login]['vote'] = 0;
            $this->karma['players'][$player->login]['previous'] = 0;
        }
    }

    private function calculateKarma(): void
    {
        $types = [
            'fantastic' => 100,
            'beautiful' => 80,
            'good' => 60,
            'bad' => 40,
            'poor' => 20,
            'waste' => 0
        ];

        // Initialize karma structure with a flat array for each type
        foreach ($types as $type => $weight) {
            $this->karma[$type] = [
                'count' => $this->karma[$type]['count'] ?? 0,
                'percent' => 0
            ];
        }

        // Calculate total votes
        $totalVotes = array_sum(array_column($this->karma, 'count')) ?: 1e-10;

        $karmaScore = 0;
        foreach ($types as $type => $weight) {
            $count = $this->karma[$type]['count'];
            $this->karma[$type]['percent'] = sprintf("%.2f", ($count / $totalVotes * 100));
            $karmaScore += $count * $weight;
        }

        if ($this->config->karma_calculation_method === 'default') {
            $this->karma['karma'] = floor($karmaScore / $totalVotes);
        }

        $this->karma['total'] = (int) $totalVotes;
    }

    //TODO - remove anything from $this->config that can be hardoced
    private function buildKarmaWidget(): string
    {
        $xmlKamraPath = $this->xmlParser->xmlPath . 'dedimania/karmaWidget.xml';
        $xmlTemplate = @file_get_contents($xmlKamraPath);
        if (!$xmlTemplate) {
            throw new RuntimeException("Unable to load the template file: {$xmlTemplate}");
        }

        $replacements = [
            '%mapUid%' => $this->config->currentMap['uid'],
            '%mapEnv%' => $this->config->currentMap['environment'],
            '%game%' => $_ENV['server_game']
        ];

        $xmlTemplate = str_replace(array_keys($replacements), array_values($replacements), $xmlTemplate);

        return $xmlTemplate;
    }

    private function sendConnectionStatus(bool $status = true, bool $gamemode): void
    {
        $xml = '<manialink id="' . $this->config->manialinkId . '06">';
        if (!$status) {
            $this->sendLoadingIndicator($status, $gamemode);
            //$xml .= '<frame posn="'. $mk_config['widget']['states'][$gamemode]['pos_x'] .' '. $mk_config['widget']['states'][$gamemode]['pos_y'] .' 20">';
            //$xml .= '<quad posn="0.5 -5.2 0.9" sizen="1.4 1.4" style="Icons128x128_1" substyle="Multiplayer"/>';
            //$xml .= '</frame>';
        }
        $xml .= '</manialink>';
        $this->client->query('SendDisplayManialinkPage', $xml, 0, false);
    }

    private function sendLoadingIndicator(bool $status, bool $gamemode): void
    {
        $xml = '<manialink id="' . $this->config->manialinkId . '07">';
        if ($status) {
            //$xml .= '<frame posn="' . $mk_config['widget']['states'][$gamemode]['pos_x'] . ' ' . $mk_config['widget']['states'][$gamemode]['pos_y'] . ' 20">';
            //$xml .= '<quad posn="0.5 -5.2 0.9" sizen="1.4 1.4" image="' . $mk_config['images']['progress_indicator'] . '"/>';
            //$xml .= '</frame>';
        }
        $xml .= '</manialink>';
        $this->client->query('SendDisplayManialinkPage', $xml, 0, false);
    }

    private function sendWidgetCombination(array $options, Player|bool $player): void
    {
        //'hide_window', 'skeleton_score', 'cups_values'
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<manialinks>';

        if (in_array('hide_window', $options) && in_array('skeleton_score', $options)) {
            $xml .= '<manialink id="' . $this->config->manialinkId . '02"></manialink>';    // hide window
        } elseif (in_array('player_marker', $options)) {
            $xml .= $this->buildPlayerVoteMarker($player);
        }

        $xml .= '</manialinks>';
        if ($player === false) {
            $this->client->query('SendDisplayManialinkPage', $xml, 0, false);
        } else {
            $this->client->query('SendDisplayManialinkPageToLogin', $player->login, $xml, 0, false);
        }
    }

    private function buildPlayerVoteMarker(Player $player): string
    {
        $preset = [];
        $preset['fantastic']['bgcolor'] = '0000';
        $preset['fantastic']['action']  = 17;
        $preset['beautiful']['bgcolor'] = '0000';
        $preset['beautiful']['action']  = 17;
        $preset['good']['bgcolor']      = '0000';
        $preset['good']['action']       = 17;
        $preset['bad']['bgcolor']       = '0000';
        $preset['bad']['action']        = 17;
        $preset['poor']['bgcolor']      = '0000';
        $preset['poor']['action']       = 17;
        $preset['waste']['bgcolor']     = '0000';
        $preset['waste']['action']      = 17;

        //SECTION -
        //REVIEW - rest of stuff asumes player in $this->karma wich is done on PlayerConnect ???
        //!SECTION -

        // Init Marker
        $marker = false;

        $xml = '<manialink id="' . $this->config->manialinkId . '04">';

        if ($marker) {
            //NOTE - do something
        }
        $xml .= '</manialink>';

        return $xml;
    }
}
