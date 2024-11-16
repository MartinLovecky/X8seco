<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use Yuhzel\X8seco\Core\Gbx\GbxClient;
use Yuhzel\X8seco\Core\Types\RaspType;
use Yuhzel\X8seco\Core\Gbx\TmxInfoFetcher;
use Yuhzel\X8seco\Core\Gbx\GbxChallMapFetcher;

/**
 * Class Challenge
 * Represents a TrackMania challenge, storing information about the track.
 * These properites created with __set
 * @property null|int $nbchecks
 * @property null|int $nbCheckpoints
 * @property null|string $name
 * @property null|string $fileName
 * @package Yuhzel\X8seco\Core\Types
 */
class Challenge
{
    public ?TmxInfoFetcher $tmx = null;

    public function __construct(
        public GbxChallMapFetcher $gbx,
        private GbxClient $client,
        private RaspType $raspType,
    ) {
    }

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
        $this->gbx->processFile($this->raspType->trackdir . $this->fileName);
        // Retrieve TMX data based on the processed track info
        $this->tmx = $this->gbx->findTMXdata($this->gbx->uid, true);
    }
}
