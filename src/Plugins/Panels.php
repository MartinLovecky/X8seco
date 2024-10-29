<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Yuhzel\Xaseco\Core\Types\ChatCommand;
use Yuhzel\Xaseco\Services\Basic;

/**
 * Panels plugin (TMF).
 * Selects ManiaLink panel templates.
 * Created by Xymph
 *
 *  used by chat.admin.php, plugin.dedimania.php,
 *          plugin.localdatabase.php, plugin.rasp_votes.php
 *  requires plugin.donate.php (if donate panels in use)
 *
 * Updated by Yuhzel
 */
class Panels
{
    private const PLUGIN_NAME = 'Panels';
    public array $panels = [
        'admin' => '',
        'donate' => '',
        'records' => '',
        'vote' => ''
    ];
    private array $commands = [];

    public function __construct()
    {
        $this->commands = [
            ['donpanel',  [$this, 'donpanel'], 'Selects donate panel (see: /donpanel help)'],
            ['recpanel', [$this, 'recpanel'], 'Selects records panel (see: /recpanel help)'],
            ['votepanel',  [$this, 'votepanel'], 'Selects vote panel (see: /votepanel help)']
        ];
    }

    // BelowChat default
    public function onStartup(): void
    {
        ChatCommand::registerCommands($this->commands, self::PLUGIN_NAME);
        // admin panel
        $panelFile = Basic::path() . "app/xml/panels/{$_ENV['adminPanel']}.xml";
        Basic::console('Load default admin panel [{1}]', $panelFile);

        if (!$this->panels['admin'] = @file_get_contents($panelFile)) {
            Basic::console('Could not read admin panel file ' . $panelFile . ' !', E_USER_ERROR);
        }
        // donate Panel
        $panelFile = Basic::path() . "app/xml/panels/{$_ENV['donatePanel']}.xml";
        Basic::console('Load default admin panel [{1}]', $panelFile);

        if (!$this->panels['donate'] = @file_get_contents($panelFile)) {
            Basic::console('Could not read donate panel file ' . $panelFile . ' !', E_USER_ERROR);
        }
        // records panel
        $panelFile = Basic::path() . "app/xml/panels/{$_ENV['recordsPanel']}.xml";
        Basic::console('Load default admin panel [{1}]', $panelFile);

        if (!$this->panels['records'] = @file_get_contents($panelFile)) {
            Basic::console('Could not read records panel file ' . $panelFile . ' !', E_USER_ERROR);
        }
        // vote panel
        $panelFile = Basic::path() . "app/xml/panels/{$_ENV['votePanel']}.xml";
        Basic::console('Load default admin panel [{1}]', $panelFile);

        if (!$this->panels['vote'] = @file_get_contents($panelFile)) {
            Basic::console('Could not read vote panel file ' . $panelFile . ' !', E_USER_ERROR);
        }
    }

    public function onSync(): void
    {
        $enabled = filter_var($_ENV['statsPanels'], FILTER_VALIDATE_BOOLEAN);
        if ($enabled) {
            $panelFile = 'panels/StatsUnited';
            Basic::console('Load stats panel [{1}]', $panelFile);
            $this->panels['statspanel'] = @file_get_contents($panelFile);
        }
    }

    // public function onNewChallenge2($data)
    // {
    //     $playerList = [];
    //     foreach ($playerList as &$player) {
    //         if ($data) {
    //             //NOTE THIS LOOK LIKE SHIT giga puke
    //             //$pb = $rasp->getPb($player->login, $aseco->server->challenge->id);
    //             //$player->panels['pb'] = $pb['time'];
    //         }
    //     }

    //     $this->update_recpanel($player, $player->panels['pb']);
    //     $this->display_alldonpanels();
    // }

    // private function update_recpanel($player, $pb)
    // {
    //     if ($player->panels['records'] != '') {
    //         if ($pb != 0) {
    //             $pb =  Basic::formatTime($pb);
    //         } else {
    //             $pb =  '--.--';
    //         }
    //         $this->maniaLinks->display_recpanel($player, $pb);
    //     }
    // }

    // // called @ onNewChallenge2
    // private function display_alldonpanels()
    // {
    //     //  $aseco->server->rights ??
    //     $donation_values = []; // global $donation_values; from plugin.donate.php  // $this->donate->chat_donate()
    //     $playerList = []; // should be Class
    //     foreach ($playerList as &$player) {
    //         if ($player->rights && $player->panels['donate'] != '') {
    //             $this->maniaLinks->display_donpanel($player, $donation_values);
    //         }
    //     }
    // }


    public function donpanel()
    {
    }
    public function recpanel()
    {
    }
    public function votepanel()
    {
    }
}