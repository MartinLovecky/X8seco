<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Plugins;

use Yuhzel\X8seco\App\ManiaLinks;
use Yuhzel\X8seco\Core\Gbx\GbxClient as Client;
use Yuhzel\X8seco\Core\Types\Player;
use Yuhzel\X8seco\Core\Types\RaspType;
use Yuhzel\X8seco\Services\Basic;

class RaspVotes
{
    //TODO I have curently no clue how this is used or if used
    /*
    private array $plrvotes = [];
    private int $replaysCounter = 0;
    private bool $auto_vote_starter = true;
    private bool $allow_spec_startvote = false;
    private bool $allow_spec_voting = false;
    // maximum number of rounds before a vote expires
    private array $r_expire_limit = [
        0 => 1,  // endround
        1 => 2,  // ladder
        2 => 3,  // replay
        3 => 2,  // skip
        4 => 3,  // kick
        5 => 3,  // add
        6 => 3,  // ignore
    ];
    // set to true to show a vote reminder at each of those rounds
    private bool $r_show_reminder = false;
    // maximum number of seconds before a vote expires
    private array $ta_expire_limit = [
        0 => 0,    // endround, N/A
        1 => 90,   // ladder
        2 => 120,  // replay
        3 => 90,   // skip
        4 => 120,  // kick
        5 => 120,  // add
        6 => 120,  // ignore
    ];
    // set to true to show a vote reminder at an (approx.) interval
    private bool $ta_show_reminder = true;
    // interval length at which to (approx.) repeat reminder
    private int $ta_show_interval = 240;  // seconds
    private bool $feature_votes = true;
    private int $global_explain = 2;
    // define the vote ratios for all types
    private array $vote_ratios = [
        0 => 0.4,  // endround
        1 => 0.5,  // ladder
        2 => 0.6,  // replay
        3 => 0.6,  // skip
        4 => 0.7,  // kick
        5 => 1.0,  // add - ignored, defined by $tmxvoteratio
        6 => 0.6,  // ignore
    ];
    private bool $vote_in_window = true;
    // disable voting commands while an admin (any tier) is online?
    private bool $disable_upon_admin = false;
    // disable voting commands during scoreboard at end of track?
    // allow kicks & allow user to kick-vote any admin?
    private bool $allow_kickvotes = false;
    private bool $allow_admin_kick = false;
    // allow ignores & allow user to ignore-vote any admin?
    private bool $allow_ignorevotes = false;
    private bool $allow_admin_ignore = false;
    // maximum number of these votes per track; set to 0 to disable a
    // vote type, or to some really high number for unlimited votes
    private int $max_laddervotes = 0;
    private int $max_replayvotes = 2;
    private int $max_skipvotes   = 1;
    private int $replays_limit = 2;
    // if true,  does restart via quick ChallengeRestart
    //           this is what most users are accustomed to, but it stops
    //           a track's music (if in use)
    // if false, does restart via jukebox prepend & NextChallenge
    //           this takes longer and may confuse users into thinking
    //           the restart is actually loading the next track, but
    //           it insures music resumes playing
    private bool $ladder_fast_restart = true;
    // enable Rounds points limits?  use this to restrict the use of the
    // track-related votes if the _first_ player already has reached a
    // specific percentage of the server's Rounds points limit
    private bool $r_points_limits = false;
    // percentage of Rounds points limit _after_ which /ladder is disabled
    private float $r_ladder_max = 0.4;
    // percentage of Rounds points limit _before_ which /replay is disabled
    private float $r_replay_min = 0.5;
    // percentage of Rounds points limit _after_ which /skip is disabled
    private float $r_skip_max   = 0.5;
    // enable Time Attack time limits?  use this to restrict the use of the
    // track-related votes if the current track is already _running_ for a
    // specific percentage of the server's TA time limit
    // this requires  function time_playing()  from plugin.track.php
    private bool $ta_time_limits = false;
    // percentage of TA time limit _after_ which /ladder is disabled
    private int $ta_ladder_max = 0;
    // percentage of TA time limit _before_ which /replay is disabled
    private float $ta_replay_min = 0.2;
    // percentage of TA time limit _after_ which /skip is disabled
    private float $ta_skip_max   = 1.0;
    */
    //private int $num_laddervotes = 0;
    //private int $num_replayvotes = 0;
    //private int $num_skipvotes = 0;
    //private bool $disable_while_sb = true;

    public function __construct(
        private Client $client,
        private RaspType $raspType,
        private Player $player,
        private ManiaLinks $maniaLinks,
    ) {}

    public function onSync(): void
    {
        $this->client->query('SetCallVoteRatios', -1);
        $this->resetVotes();
    }

    private function resetVotes(array $chatvote = []): void
    {
        if (!empty($chatvote)) {
            Basic::console(
                'Vote by {1} to {2} reset!',
                $this->player->login, // $chatvote['login'],
                'End this Round' // $chatvote['desc']
            );

            $message = $this->raspType->messages->vote_cancel;
            $this->client->query('ChatSendServerMessage', Basic::formatColors($message));
            $chatvote = [];
            $this->maniaLinks->allVotepanelsOff();
        }
    }
}
