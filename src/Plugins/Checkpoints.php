<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\Core\Types\{
    Challenge,
    RecordList,
    ChatCommand
};

class Checkpoints
{
    private const PLUGIN_NAME = 'Checkpoints';
    public int $loclrec = -1;
    public int $dedirec = -1;
    public int $best_time = 0;
    public int $best_fin = PHP_INT_MAX;
    public int $curr_cps = PHP_INT_MAX;
    public int $laps_cpcount = 0;
    public array $best_cps = [];
    public array $curr_fin = [];
    public array $speccers = [];
    public array $checkpoints = [];
    private array $commands = [];

    public function __construct(
        private Challenge $challenge,
        private RecordList $recordList,
        private LocalDatabase $localDatabase
    ) {
        $this->commands = [
            ['cps',  [$this, 'cps'], 'Sets local record checkpoints tracking'],
            ['cpsspec',  [$this, 'cpsspec'], 'Shows checkpoints of spectated player'],
            ['cptms',  [$this, 'cptms'], 'Displays all local records\' checkpoint times'],
            ['sectms',  [$this, 'sectms'], 'Displays all local records\' sector times']
        ];
    }

    public function onStartup(): void
    {
        ChatCommand::registerCommands($this->commands, self::PLUGIN_NAME);
    }

    public function onPlayerConnect(string $login)
    {
        $cps = $this->localDatabase->getCPS();
        $this->loclrec = $cps;
        $this->dedirec = $cps;
        $this->checkpoints[$login] = $this;
    }

    public function onNewChallenge(bool $displayCheckpoints = true): void
    {
        foreach ($this->checkpoints as $login => $cp) {
            $cp->best_cps = [];
            $cp->curr_cps = [];
            $cp->best_fin = PHP_INT_MAX;
            $cp->curr_fin = PHP_INT_MAX;
        }

        if ($displayCheckpoints) {
            foreach ($this->checkpoints as $login => $cp) {
                $lrec = $cp->loclrec - 1;

                // Check for specific record
                if ($lrec + 1 > 0) {
                    // If specific record unavailable, use the last one
                    $lrec = min($lrec, count($this->recordList->records) - 1);
                    $curr = $this->recordList->records[$lrec] ?? null;

                    // Check for valid checkpoints
                    if ($curr && !empty($curr->checks) && $curr->score === end($curr->checks)) {
                        $cp->best_fin = $curr->score;
                        $cp->best_cps = $curr->checks;
                    }
                } elseif ($lrec + 1 === 0) {
                    // Search for own/last record
                    foreach ($this->recordList->records as $curr) {
                        if ($curr->player->login === $login) {
                            // Check for valid checkpoints
                            if (!empty($curr->checks) && $curr->score === end($curr->checks)) {
                                $cp->best_fin = $curr->score;
                                $cp->best_cps = $curr->checks;
                            }
                            break;
                        }
                    }
                }
            }
        }

        $this->laps_cpcount = $this->challenge->nbchecks ?? 0;
    }

    public function cps()
    {
    }
    public function cpsspec()
    {
    }
    public function cptms()
    {
    }
    public function sectms()
    {
    }
}
