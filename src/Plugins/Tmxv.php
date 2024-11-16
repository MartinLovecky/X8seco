<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\Services\Aseco;
use Yuhzel\X8seco\Services\HttpClient;
use Yuhzel\X8seco\Core\Types\Challenge;
use Yuhzel\X8seco\Core\Types\ChatCommand;

class Tmxv
{
    private const PLUGIN_NAME = 'Tmxv';
    private const API_URL = 'https://tmnf.exchange/api/';
    //private string $CERT_PATH = '';
    private array $videos = [];
    private array $commands = [];

    public function __construct(
        private HttpClient $httpClient,
        private Challenge $challenge
    ) {
        // $this->CERT_PATH = Aseco::path() . 'app/cacert.pem';
        $this->commands = [
            ['videos',  [$this, 'videos'], 'Sets up the tmx videos command environment'],
            ['video',  [$this, 'video'], 'Gives latest video in chat'],
            ['gps',  [$this, 'gps'], 'Gives latest video in chat']
        ];
    }

    public function onStartup(): void
    {
        Aseco::console('Plugin TMX Video initialized.');
        ChatCommand::registerCommands($this->commands, self::PLUGIN_NAME);
    }

    public function onNewChallenge(): void
    {
        $this->onNewTrack();
    }


    public function videos()
    {
    }
    public function video()
    {
    }
    public function gps()
    {
    }


    private function onNewTrack(): void
    {
        if (isset($this->challenge->tmx->id)) {
            $this->loadVideos($this->challenge->tmx->id);
        }
    }

    private function loadVideos(int $tmxid): void
    {
        Aseco::console('Requesting videos for track with TMX ID ' . $tmxid);

        $this->httpClient->baseUrl = self::API_URL;
        $output = $this->httpClient->get('videos', [
            'fields' => 'LinkId,Title,PublishedAt',
            'trackid' => $tmxid
        ]);

        if ($output === false) {
            Aseco::console('Failed to fetch videos from ' . self::API_URL);
            $this->videos = [];
            return;
        }

        $result = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Aseco::console('Failed to parse JSON response: ' . json_last_error_msg());
            $this->videos = [];
            return;
        }

        if (isset($result['Results']) && count($result['Results']) > 0) {
            $this->videos = $result['Results'];
            $this->sortVideosByPublishedDate($this->videos);
        } else {
            $this->videos = [];
        }

        Aseco::console('Found ' . count($this->videos) . ' videos for track with TMX ID ' . $tmxid);
    }

    private function sortVideosByPublishedDate(array &$videos): void
    {
        usort($videos, fn ($a, $b) => strtotime($b['PublishedAt']) - strtotime($a['PublishedAt']));
    }
}
