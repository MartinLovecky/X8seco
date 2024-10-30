<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\App\ManiaLinks;
use Yuhzel\X8seco\Services\Basic;
use Yuhzel\X8seco\Core\Types\Challenge;
use Yuhzel\X8seco\Core\Types\ChatCommand;
use Yuhzel\X8seco\Core\Types\Player;
use Yuhzel\X8seco\Core\Types\PlayerList;
use Yuhzel\X8seco\Core\Types\RecordList;
use Yuhzel\X8seco\Core\Gbx\GbxClient as Client;

class ChatCmd
{
    private const PLUGIN_NAME = 'ChatCmd';
    public array $commands = [];

    public function __construct(
        private Client $client,
        private PlayerList $playerList,
        private Player $player,
        private Challenge $challenge,
        private RecordList $recordList,
        private ManiaLinks $maniaLinks
    ) {
        $this->commands = [
            ['help', [$this, 'help'], 'Shows all available commands'],
            ['helpall', [$this, 'helpPall'], 'Displays help for available commands'],
            ['laston',  [$this, 'laston'], 'Shows when a player was last online'],
            ['lastwin', [$this, 'lastwin'], 'Re-opens the last closed multi-page window'],
            ['me',  [$this, 'me'], 'Can be used to express emotions'],
            ['wins',  [$this, 'wins'], 'Shows wins for current player'],
            ['song', [$this, 'song'], 'Shows filename of current track\'s song'],
            ['mod',  [$this, 'mod'], 'Shows (file)name of current track\'s mod'],
            ['players', [$this, 'players'], 'Displays current list of nicks/logins'],
            ['ranks',  [$this, 'ranks'], 'Displays list of online ranks/nicks'],
            ['clans',  [$this, 'clans'], 'Displays list of online clans/nicks'],
            ['topclans', [$this, 'topclans'], 'Displays top 10 best ranked clans'],
            ['recs', [$this, 'recs'], 'Displays all records on current track'],
            ['best', [$this, 'best'], 'Displays your best records'],
            ['worst', [$this, 'worst'], 'Displays your worst records'],
            ['summary',  [$this, 'summary'], 'Shows summary of all your records'],
            ['topsums', [$this, 'topsums'], 'Displays top 100 of top-3 record holders'],
            ['toprecs',  [$this, 'toprecs'], 'Displays top 100 ranked records holders'],
            ['newrecs',  [$this, 'newrecs'], 'Shows newly driven records'],
            ['liverecs', [$this, 'liverecs'], 'Shows records of online players'],
            ['firstrec', [$this, 'firstrec'], 'Shows first ranked record on current track'],
            ['lastrec',  [$this, 'lastrec'], 'Shows last ranked record on current track'],
            ['nextrec', [$this, 'nextrec'], 'Shows next better ranked record to beat'],
            ['diffrec', [$this, 'diffrec'], 'Shows your difference to first ranked record'],
            ['recrange', [$this, 'recrange'], 'Shows difference first to last ranked record'],
            ['server', [$this, 'server'], 'Displays info about this server'],
            ['xaseco',  [$this, 'xaseco'], 'Displays info about this XASECO'],
            ['plugins',  [$this, 'plugins'], 'Displays list of active plugins'],
            ['nations',  [$this, 'nations'], 'Displays top 10 most visiting nations'],
            ['stats',  [$this, 'stats'], 'Displays statistics of current player'],
            ['statsall', [$this, 'statsall'], 'Displays world statistics of a player'],
            ['settings',  [$this, 'settings'], 'Displays your personal settings']
        ];
    }

    public function onStartup(): void
    {
        ChatCommand::registerCommands($this->commands, self::PLUGIN_NAME);
    }

    public function help()
    {
        $this->player->msgs = ChatCommand::getHelp(self::PLUGIN_NAME);
        $this->maniaLinks->displayManialinkMulti($this->player);
    }

    public function helpPall() {}
    public function laston() {}
    public function lastwin() {}
    public function me() {}
    public function wins() {}
    public function song() {}
    public function mod() {}
    public function players() {}
    public function ranks() {}
    public function clans() {}
    public function topclans() {}
    public function recs() {}
    public function best() {}
    public function worst() {}
    public function summary() {}
    public function topsums() {}
    public function toprecs() {}

    public function trackrecs($login, int $mode)
    {
        $records = '$n';  // Use narrow font
        $totalNew = 0;

        // Check if records exist
        $total = count($this->recordList->records);
        if ($total === 0) {
            $totalNew = -1;
        } else {
            $first = $this->recordList->getRecord(0);
            $last = $this->recordList->getRecord($total - 1);

            $diff = $last->score - $first->score;
            $sec = intdiv($diff, 1000);
            $hun = ($diff % 1000) / 10;

            $player = $this->playerList->players[$login];

            foreach ($this->recordList->records as $i => $curRecord) {
                $recordMsg = $this->getRecordMessage($curRecord, $i, $mode, $total, $player);
                if ($recordMsg) {
                    $records .= $recordMsg;
                }
                if ($curRecord->new) {
                    $totalNew++;
                }
            }
        }

        $sec = $sec ?? 0;
        $hun = $hun ?? 0;
        $timing = $this->getTimingMessage($mode);
        $name = '$l[' . $this->challenge->tmx->pageurl . ']' . Basic::stripColors($this->challenge->name) . '$l';
        $message = $this->getFinalMessage($totalNew, $name, $timing, $sec, $hun, $records);

        if ($login) {
            $message = str_replace('{#server}>> ', '{#server}> ', $message); // Adjust message for player-specific message
            $this->client->query('ChatSendServerMessageToLogin', Basic::formatColors($message), $login);
        } else {
            $this->client->query('ChatSendServerMessage', Basic::formatColors($message));
        }
    }

    private function getRecordMessage(
        object $record,
        int $index,
        int $mode,
        int $total,
        array $players
    ): ?string {
        $isLastRecord = $index === $total - 1;
        $recordMsg = null;

        if ($record->new) {
            $recordMsg = Basic::formatText(
                Basic::getChatMessage('ranking_record_new_on'),
                $index + 1,
                Basic::stripColors($record->player->nickname),
                Basic::formatTime($record->score)
            );
        } elseif (in_array($record->player->login, $players)) {
            $recordMsg = Basic::formatText(
                Basic::getChatMessage('ranking_record_on'),
                $index + 1,
                Basic::stripColors($record->player->nickname),
                Basic::formatTime($record->score)
            );

            if ($mode === 0 && !$isLastRecord) {
                return null; // Skip non-last records in mode 0
            }
        } else {
            $recordMsg = Basic::formatText(
                Basic::getChatMessage('ranking_record'),
                $index + 1,
                Basic::stripColors($record->player->nickname),
                Basic::formatTime($record->score)
            );

            if (
                $isLastRecord ||
                ($mode === 2 && $index < 6) ||
                (($mode === 1 || $mode === 3) && $index < 8)
            ) {
                return $recordMsg;
            }

            return null; // Skip record
        }

        return $recordMsg;
    }

    private function getTimingMessage(int $mode): string
    {
        return match ($mode) {
            0, 2 => 'during',
            1 => 'before',
            3 => 'after',
            default => 'unknown',
        };
    }

    private function getFinalMessage(
        int $totalNew,
        string $name,
        string $timing,
        int $sec,
        float $hun,
        string $records
    ): string {
        if ($totalNew > 0) {
            return Basic::formatText(
                Basic::getChatMessage('ranking_new'),
                $name,
                $timing,
                $totalNew
            );
        }

        if ($totalNew === 0 && $records !== '$n') {
            return Basic::formatText(
                Basic::getChatMessage('ranking_range'),
                $name,
                $timing,
                sprintf("%d.%02d", $sec, $hun)
            );
        }

        if ($totalNew === 0 && $records === '$n') {
            return Basic::formatText(
                Basic::getChatMessage('ranking_nonew'),
                $name,
                $timing
            );
        }

        return Basic::formatText(
            Basic::getChatMessage('ranking_none'),
            $name,
            $timing
        );
    }

    public function newrecs() {}
    public function liverecs() {}
    public function firstrec() {}
    public function lastrec() {}
    public function nextrec() {}
    public function diffrec() {}
    public function recrange() {}
    public function server() {}
    public function xaseco() {}
    public function plugins() {}
    public function nations() {}
    public function stats() {}
    public function statsall() {}
    public function settings() {}
}
