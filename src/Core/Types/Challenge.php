<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use Yuhzel\X8seco\Services\Aseco;
use Yuhzel\X8seco\Core\Gbx\{GbxClient, GbxChallMapFetcher, TmxInfoFetcher};

/**
 * Class Challenge
 * Represents a TrackMania challenge, storing information about the track.
 * These properites created with __set
 * @property string $uId
 * @property string $name
 * @property string $fileName
 * @property string $author
 * @property string $environnement
 * @property string $mood
 * @property int $bronzeTime
 * @property int $silverTime
 * @property int $goldTime
 * @property int $authorTime
 * @property int $copperPrice
 * @property bool $lapRace
 * @property int $nbLaps
 * @property int $nbCheckpoints
 * @package Yuhzel\X8seco\Core\Types
 */
class Challenge
{
    public int $id = 0;
    private string $trackDir = 'GameData/Tracks/';

    public function __construct(
        public GbxChallMapFetcher $gbx,
        public TmxInfoFetcher $tmx,
        private GbxClient $client,
    ) {}

    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }

    public function __get($name): mixed
    {
        return $this->$name ?? null;
    }

    public function setChallangeInfo(): void
    {
        // Set each challenge property via __set() magic method
        foreach ($this->client->query('GetCurrentChallengeInfo', []) as $challengekey => $challengeValue) {
            $this->__set(lcfirst($challengekey), $challengeValue);
        }

        $this->gbx->setXml(true);
        $this->gbx->processFile(Aseco::path(3) . $this->trackDir . $this->fileName);
        $this->tmx->setData($this->gbx->uid, true);
        $this->id = $this->tmx->id ?? $this->id;
    }
}
