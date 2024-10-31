<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\App;

use Yuhzel\X8seco\Core\Gbx\GbxClient as Client;
use Yuhzel\X8seco\Database\Fluent;
use Yuhzel\X8seco\Services\Basic;
use Yuhzel\X8seco\Core\Types\PlayerList;
use Yuhzel\X8seco\Core\Types\Player;

class ManiaLinks
{
    private array $command = [];

    public function __construct(
        private PlayerList $playerList,
        private Client $client,
        private Fluent $fluent,
    ) {
    }

    public function displayManialinkMulti(Player $player)
    {
        // fake current page event
        $this->eventManialink([0, $player->login, 1]);
    }

    public function eventManialink(array $answer)
    {

        if ($answer[2] < -6 || $answer[2] > 36) {
            return;
        }

        $login = $answer[1];
        $player = $this->playerList->getPlayer($login);

        switch ($answer[2]) {
            case 0:
                $this->mainwindowOff($login);
                return;
            case -5:
                // log clicked command
                Basic::console('player {1} clicked command "/active "', $player->login);
                // /stats field Time Played
                $this->command['author'] = $player;
                $this->chatActive();
                return;
            case -6:
                // log clicked command
                Basic::console('player {1} clicked command "/top100 "', $player->login);
                // /stats field Server Rank
                $command['author'] = $player;
                $this->chatTop100();
                return;
        }


        $tot = count($player->msgs) - 1;
        $ptr = $player->msgs['ptr'] ?? 0;
        $header = $player->msgs['header'] ?? '';
        $widths = $player->msgs['width'] ?? [1.3, 0.3, 1.0];
        $icon = $player->msgs['icon'] ?? ['Icons64x64_1', 'TrackInfo', -0.01];
        $style = $player->style;
        $xml = '';
        if (empty($style)) {
            $tsp = 'B';  // 'F' = solid, '0' = invisible
            $txt = '333' . $tsp;  // dark grey
            $bgd = 'FFF' . $tsp;  // white
            $spc = 'DDD' . $tsp;  // light grey

            // Start building manialink header
            $xml = '<manialink id="1" posx="' . ($widths[0] / 2) . '" posy="0.47">';
            $xml .= '<background bgcolor="' . $bgd . '" bgborderx="0.01" bgbordery="0.01"/>' . "\n";
            $xml .= '<format textsize="3" textcolor="' . $txt . '"/>' . "\n";

            // Add header
            $xml .= '<line>';
            $xml .= '<cell bgcolor="' . $spc . '" width="' . ($widths[0] - 0.12) . '">';
            $xml .= '<text> $o' . htmlspecialchars(Basic::validateUTF8($header)) . '</text></cell>';
            $xml .= '<cell bgcolor="' . $spc . '" width="0.12">';
            $xml .= '<text halign="right">$n(' . $ptr . '/' . $tot . ')</text></cell>';
            $xml .= '</line>' . "\n";

            // Add spacer
            $xml .= '<format textsize="2" textcolor="' . $txt . '"/>' . "\n";
            $xml .= '<line><cell bgcolor="' . $bgd . '" width="' . $widths[0] . '">';
            $xml .= '<text>$</text></cell></line>' . "\n";

            // Loop through each message and add to manialink
            foreach ($player->msgs as $line) {
                $xml .= '<line height=".046">';

                if (!empty($line)) {
                    // Check if there's only one message
                    if (count($player->msgs) === 1) {
                        // Handle the single message case
                        foreach ($line as $i => $value) {
                            $xml .= '<cell bgcolor="' . $bgd . '" width="' . $widths[$i + 1] . '">';
                            if (is_array($value)) {
                                $xml .= '<text action="' . htmlspecialchars($value[1]) . '">  $o' . htmlspecialchars(Basic::validateUTF8($value[0])) . '</text>';
                            } else {
                                $xml .= '<text>  $o' . htmlspecialchars(Basic::validateUTF8($value)) . '</text>';
                            }
                            $xml .= '</cell>';
                        }
                    } else {
                        // Handle the multiple messages case
                        $xml .= '<cell bgcolor="' . $bgd . '" width="' . $widths[0] . '">';
                        $xml .= '<text>  $o' . htmlspecialchars(Basic::validateUTF8($line)) . '</text></cell>';
                    }
                } else {
                    // Handle the case when line is empty or not a string
                    $xml .= '<cell bgcolor="' . $bgd . '" width="' . $widths[0] . '">';
                    $xml .= '<text>$</text></cell>';
                }

                $xml .= '</line>' . "\n";
            }

            // Add closing spacer
            $xml .= '<line><cell bgcolor="' . $bgd . '" width="' . $widths[0] . '">';
            $xml .= '<text>$</text></cell></line>' . "\n";

            // Add buttons
            $add5 = ($tot > 5);
            $butw = ($widths[0] - ($add5 ? 0.22 : 0)) / 3;
            $xml .= '<line height=".046">';

            // Add previous buttons if applicable
            if ($ptr > 1) {
                if ($add5) {
                    $xml .= '<cell bgcolor="' . $bgd . '" width="0.11">';
                    $xml .= '<text halign="center" action="-3">$oPrev5</text></cell>';
                    $xml .= '<cell bgcolor="' . $bgd . '" width="' . $butw . '">';
                    $xml .= '<text halign="center" action="-2">$oPrev</text></cell>';
                }
            } else {
                if ($add5) {
                    $xml .= '<cell bgcolor="' . $bgd . '" width="0.11"><text>$</text></cell>';
                    $xml .= '<cell bgcolor="' . $bgd . '" width="' . $butw . '"><text>$</text></cell>';
                }
            }

            // Add close button
            $xml .= '<cell bgcolor="' . $bgd . '" width="' . $butw . '">';
            $xml .= '<text halign="center" action="0">$oClose</text></cell>';

            // Add next buttons if applicable
            if ($ptr < $tot) {
                $xml .= '<cell bgcolor="' . $bgd . '" width="' . $butw . '">';
                $xml .= '<text halign="center" action="2">$oNext</text></cell>';
                if ($add5) {
                    $xml .= '<cell bgcolor="' . $bgd . '" width="0.11">';
                    $xml .= '<text halign="center" action="3">$oNext5</text></cell>';
                }
            } else {
                $xml .= '<cell bgcolor="' . $bgd . '" width="' . $butw . '"><text>$</text></cell>';
                if ($add5) {
                    $xml .= '<cell bgcolor="' . $bgd . '" width="0.11"><text>$</text></cell>';
                }
            }

            $xml .= '</line></manialink>';
        }

        $this->client->addCall(
            'SendDisplayManialinkPageToLogin',
            [
                'login' => $player->login,
                'manialink' => Basic::formatColors($xml),
                'duration' => 0,
                'display' => false
            ]
        );
    }

    public function allVotepanelsOff(): void
    {
        $xml = '<manialink id="5"></manialink>';
        $this->client->addCall('SendDisplayManialinkPage', [
            'manialink' => $xml,
            'duration' => 0,
            'display' => false
        ]);
    }

    private function mainwindowOff($login): void
    {
        $xml = '<manialink id="1"></manialink>';
        $this->client->addCall('SendDisplayManialinkPageToLogin', [
            'login' => $login,
            'manialink' => $xml,
            'duration' => 0,
            'display' => false
        ]);
    }

    private function chatActive(): void
    {
        $player = $this->command['author'];
        $head = 'TOP 100 Most Active Players:';
        $top = 100;
        $bgn = '{#black}';

        // Fetch the top players from the database
        $res = $this->fluent->query->from('players')
            ->select(['NickName', 'TimePlayed'])
            ->orderBy('TimePlayed DESC')
            ->limit($top)
            ->fetchAll();

        $active = [];
        $i = 1; // Initialize the index for player ranking
        $lines = 0; // Initialize line counter
        $player->msgs = []; // Initialize messages array
        $extra = 0.2; // Extra height for cells

        // Add header message
        $player->msgs[0] = [
            1,
            $head,
            [0.8 + $extra, 0.1, 0.45 + $extra, 0.25],
            ['BgRaceScore2', 'LadderRank']
        ];

        foreach ($res as $row) {
            $nick = $row['NickName'];
            $active[] = [
                str_pad((string)$i, 2, '0', STR_PAD_LEFT) . '.', // Player rank
                $bgn . $nick, // Player nickname
                Basic::formatTimeH($row['TimePlayed'] * 1000, false) // Formatted time played
            ];
            $i++; // Increment player ranking

            // If the number of lines exceeds 14, store the current active list and reset
            if (++$lines >= 14) {
                $player->msgs[] = $active; // Add active messages to player messages
                $lines = 0; // Reset line count
                $active = []; // Reset active messages
            }
        }

        // If there are any remaining active messages after the loop, add them
        if (!empty($active)) {
            $player->msgs[] = $active;
        }

        // Display ManiaLink message
        $this->displayManialinkMulti($player);
    }

    private function chatTop100()
    {
        $player = $this->command['author'];
        $head = 'Current TOP 100 Players:';
        $top = 100;
        $bgn = '{#black}';  // nickname begin
    }
}
