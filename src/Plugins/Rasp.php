<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Yuhzel\Xaseco\Services\Basic;
use Yuhzel\Xaseco\Database\Fluent;
use Yuhzel\Xaseco\Core\Types\ChatCommand;
use Yuhzel\Xaseco\Core\Types\RaspType;

/**
 * RASP plugin.
 * Provides rank & personal best handling, and related chat commands.
 * Updated by Xymph
 * Update by Yuhzel
 */
class Rasp
{
    private const PLUGIN_NAME = 'Rasp';
    private array $commands = [];

    public function __construct(
        private RaspType $typeRasp,
        private Fluent $fluent,
    ) {
        $this->commands = [
            ['rank', [$this, 'rank'], 'Shows your current server rank'],
            ['top10', [$this, 'top10'], 'Displays top 10 best ranked players'],
            ['top100', [$this, 'top100'], 'Displays top 100 best ranked players'],
            ['topwins', [$this, 'topwins'], 'Displays top 100 victorious players'],
            ['active', [$this, 'active'], 'Displays top 100 most active players']
        ];
    }

    //TODO - Prune only sometimes not all the time
    public function onStartup(): void
    {
        ChatCommand::registerCommands($this->commands, self::PLUGIN_NAME);
        $this->typeRasp->start();

        if (!$this->typeRasp->prune) {
            Basic::console('[RASP] Pruning records/rs_times for deleted tracks');
            $this->typeRasp->getChallenges();
            $track = $this->typeRasp->challenge;

            $query = $this->fluent->query->from('records')->select('ChallengeId')->fetchAll();

            if (!in_array($track, $query)) {
                $this->fluent->query->deleteFrom('records')->where('ChallengeId', $track);
                $this->fluent->query->deleteFrom('rs_times')->where('challengeID', $track);
            }
        }
    }

    public function onSync(): void
    {
        $this->typeRasp->event_onsync();
    }

    public function rank()
    {
    }
    public function top10()
    {
    }
    public function top100()
    {
    }
    public function topwins()
    {
    }
    public function active()
    {
    }
}