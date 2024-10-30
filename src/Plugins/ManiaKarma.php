<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use RuntimeException;
use Yuhzel\X8seco\Services\Basic;
use Yuhzel\X8seco\Services\HttpClient;
use Yuhzel\X8seco\Core\Xml\XmlParser;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

class ManiaKarma
{
    private ?XmlArrayObject $config = null;
    //private array $karma = [];

    public function __construct(
        private XmlParser $xmlParser,
        private HttpClient $httpClient,
    ) {}

    public function onSync(): void
    {
        $this->config = $this->xmlParser->parseXml('mania_karma.xml');
        $this->config->manialinkId = 911;
        $this->config->retryTime = 0;
        $this->config->retryWait = 600;
        $this->config->login = $_ENV['server_login'];

        Basic::console('************************(ManiaKarma)*************************');
        Basic::console('plugin.mania_karma.php/ 2.11.26 for XAseco');
        Basic::console(" => Set Server location to {$this->config->nation}");
        Basic::console(" => Trying to authenticate with central database {$this->config->urls->api_auth}");

        // http://worldwide.mania-karma.com/api/tmforever-trackmania-v4.php?Action=Auth&login=%s&name=%s&game=%s&zone=%s&nation=%s
        $this->httpClient->baseUrl = "http://worldwide.mania-karma.com/api/";
        $response = $this->httpClient->get("tmforever-trackmania-v4.php", [
            'Action' => 'Auth',
            'login'  => urlencode($this->config->login),
            'name'   => base64_encode($_ENV['server_name']),
            'game'   => urlencode('TMF'),
            'zone'   => urlencode('World'),
            'nation' => urlencode($this->config->nation)
        ]);

        //NOTE - We could modify XML RPC parser to handle this
        $responseData = $this->httpClient->xmlResponse($response);

        if ($responseData) {
            $status = $responseData['status'];
            switch ($status) {
                case '704':
                    Basic::console("Error: Authentication failed. Please check your credentials.");
                    break;
                case '200':
                    Basic::console("Success");
                    break;
                default:
                    # code...
                    break;
            }
        }
        Basic::console('**********************************************************');

        // $this->config->reminder_window->race->pos_x;
        // $this->config->karma_widget->time_attack;

        $this->config->templates = $this->loadTeamplates();
        $this->config->currentMap  = null;
    }

    private function loadTeamplates(): string
    {
        $templatePath = Basic::path() . 'app/xml/maniaTemplates/costum/maniaKarma.xml';
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
}
