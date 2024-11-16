<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use Exception;
use Yuhzel\X8seco\Database\Fluent;
use Yuhzel\X8seco\Services\{Log, Aseco};
use Yuhzel\X8seco\Core\Xml\{XmlParser, XmlArrayObject};

class RaspType
{
    //##################################################################
    //#------------------------- Features -----------------------------#
    //#  Specify here which features you would like to be activated    #
    //#  You must enter true or false in lowercase only!               #
    //##################################################################
    public bool $feature_ranks = true; //Set to true if you want the rank system active
    public bool $nextrank_show_rp = true; //Set to true if you want /nextrank to show the difference in record positions,
    public bool $feature_stats = true; //Set to true if you want all times recorded, and /pb command to be active
    public bool $always_show_pb = true; //Set to true to always show PB at track start
    public bool $feature_karma = true; //Set to true ONLY if you use the karma feature.
    public bool $allow_public_karma = true; //Set to true if you allow ++ & -- votes as well as /++ & /--
    public bool $karma_show_start = true; //Set to true if you want to show the karma message at the start of each track
    public bool $karma_show_details = true; //Set to true if you want to show vote counts & percentages
    public bool $karma_show_votes = true; //Set to true if you want to show players their actual votes
    public int $karma_require_finish = 0; //Remind player to vote karma if [s]he hasn't yet
    public int $remind_karma = 0;  // 2 = every finish; 1 = at end of race; 0 = none
    public bool $feature_jukebox = true; //Set to true if you want jukebox functionality
    public bool $feature_tmxadd = false; //Set to true if you want jukebox to be extended to include the TMX /add feature
    public bool $jukebox_skipleft = true; //Set to true if you want jukebox to skip tracks requested by players that left
    public bool $jukebox_adminnoskip = false; //Set to true if you want jukebox to _not_ skip tracks requested by admins
    public bool $jukebox_permadd = false; //Set to true if you want /add to permanently add tracks to the server
    public bool $jukebox_adminadd = true; //Set to true if you want /admin add to automatically jukebox the downloaded track (just like a passed /add vote)
    public bool $jukebox_in_window = false; //Set to true if you want jukebox messages diverted to TMF message window
    public string $admin_contact = 'YOUR@EMAIL.COM'; //Set to contact (email, ICQ, etc) to show in /server command, leave empty to skip entry
    public string $autosave_matchsettings = '';  //Set to filename to enable autosaving matchsettings upon every track switch
    public bool $prune_records_times = false; //Only enable this if you know what you're doing!
    public bool $feature_votes = false; //Set to true if you want to disable normal CallVotes & enable chat-based votes
    public bool $uptodate_check = true; //Set to true to perform XASECO version check at start-up & MasterAdmin connect
    public bool $globalbl_merge = false; //Set to true to perform global blacklist merge at MasterAdmin connect
    public bool $globalbl_united = false; //Set to true to process only United accounts in global blacklist merge
    public string $globalbl_url = ''; //Set to global blacklist in XML format, same as <blacklist_url> in dedicated_cfg.txt (TMF)
    //##################################################################
    //#-------------------- Performance Variables ---------------------#
    //#  These variables are used in the main plugin.                  #
    //#  They specify how much data should be used for calculations    #
    //#                                                                #
    //#  If your server slows down considerably when calculating       #
    //#  ranks it is recommended that you lower/increase these values  #
    //##################################################################
    public int $maxrecs = 50; //Sets the maximum number of records stored per track Lower = Faster
    public int $minrank = 3; //Sets the minimum amount of records required for a player to be ranked Higher = Faster
    public int $maxavg = 10; //Sets the number of times used to calculate a player's average Lower = Faster
    //##################################################################
    //#-------------------- Jukebox Variables -------------------------#
    //#  These variables are used by the jukebox.                      #
    //##################################################################
    public int $buffersize = 20; //Specifies how large the track history buffer is.
    public float $tmxvoteratio = 0.66; //Specifies the required vote ratio for a TMX /add request to be successful.
    //The location of the tracks folders for saving TMX tracks, relative
    //to the dedicated server's GameData/Tracks/ directory:
    //$tmxdir for tracks downloaded via /admin add, and user tracks approved
    //  via /admin addthis.
    public string $tmxdir = 'Challenges/TMX';
    //$tmxtmpdir for tracks downloaded via /add user votes.
    //There must be full write permissions on these folders.
    //In linux the command will be:  chmod 777.
    //Regardless of OS, use the / character for pathing.
    public string $tmxtmpdir = 'Challenges/TMXtmp';
    public string $gamedir = '';
    public string $trackdir = '';
    //##################################################################
    //#------------------------ IRC Variables -------------------------#
    //#  These variables are used by the IRC plugin.                   #
    //##################################################################
    public array $_CONFIG = [
        'server' => 'localhost',
        'nick' => 'botname',
        'port' => 6667,
        'channel' => '#channel',
        'name' => 'botlogin'
    ];

    public bool $show_connect = false; //If set to true, the IRC connection messages will be displayed in the console.
    public array $linesbuffer = [];
    public array $ircmsgs = [];
    public array $outbuffer = [];
    public array $con = [];
    public array $jukebox = [];
    public array $jb_buffer = [];
    public array $tmxadd = [];
    public bool $tmxplaying = false;
    public bool $tmxplayed = false;
    public bool $prune = true;
    public array $challengeListCache = [];
    public int $challenge = 0;
    public ?XmlArrayObject $messages = null;
    private string $gamePath = '';

    public function __construct(
        private Fluent $fluent,
        private XmlParser $xmlParser
    ) {
        $this->gamePath = Aseco::path(3);
        $this->gamedir = "{$this->gamePath}GameData/";
        $this->trackdir = "{$this->gamePath}GameData/Tracks/";
    }

    public function start(): void
    {
        $this->messages = $this->xmlParser->parseXml('rasp.xml')->messages;
        Aseco::console('[RASP] Checking database structure...');
        $this->checkTables();
        Aseco::console('[RASP] ...Structure OK!');
        $this->cleanData();
    }

    public function getChallenges(): void
    {
        $newlist = $this->getChallengesCache();

        foreach ($newlist as $row) {
            $id = $this->fluent->query
                ->from('challenges')
                ->select('Id')
                ->where('Uid', $row['UId'])
                ->fetch('Id');

            if (!$id) {
                $values = [
                    'UId' => $row['UId'],
                    'Name' => $row['Name'],
                    'Author' => $row['Author'],
                    'Environment' => $row['Environment']
                ];
                $this->fluent->query->insertInto('challenges')->values($values)->execute();
            }
        }

        $this->challenge = $id ?? 0;
    }

    public function event_onsync(): void
    {
        $sepchar = substr($this->trackdir, -1, 1);
        $tmxdir = '';

        if ($sepchar === '/') {
            $tmxdir = str_replace('/', $sepchar, $this->tmxdir);
        }
        if (!file_exists($this->trackdir . $tmxdir)) {
            if (!mkdir($this->trackdir . $tmxdir)) {
                Aseco::console('{RASP_ERROR} TMX Directory (' . $this->trackdir . $tmxdir . ') cannot be created');
            }
        }

        if (!is_writeable($this->trackdir . $tmxdir)) {
            Aseco::console('{RASP_ERROR} TMX Directory (' . $this->trackdir . $tmxdir . ') cannot be written to');
        }

        if ($this->feature_tmxadd) {
            if (!file_exists($this->trackdir . $this->tmxtmpdir)) {
                if (!mkdir($this->trackdir . $this->tmxtmpdir)) {
                    Aseco::console('{RASP_ERROR} TMXtmp Directory (' . $this->trackdir . $this->tmxtmpdir . ') cannot be created');
                    $this->feature_tmxadd = false;
                }
            }

            if (!is_writeable($this->trackdir . $this->tmxtmpdir)) {
                Aseco::console('{RASP_ERROR} TMXtmp Directory (' . $this->trackdir . $this->tmxtmpdir . ') cannot be written to');
                $this->feature_tmxadd = false;
            }
        }
    }

    private function checkTables(): void
    {
        // List of tables you need to process
        $tables = ['rs_karma', 'rs_rank', 'rs_times'];

        foreach ($tables as $table) {
            try {
                // Create table if they dont exist
                if ($this->fluent->execSQLFile($table)) {
                    if (!$this->fluent->validStructure($table)) {
                        Aseco::console('ERROR 1 RaspType');
                    }
                } else {
                    if (!$this->fluent->validStructure($table)) {
                        Aseco::console('ERROR 2 RaspType');
                    }
                }
            } catch (Exception $e) {
                Log::error("Error processing table {$table}: {$e->getMessage()}");
            }
        }
    }

    private function cleanData(): void
    {
        if (!$this->prune) {
            Aseco::console('[RASP] Cleaning up unused data');

            $challenges = $this->fluent->query->deleteFrom('challenges')->where('Uid', '')->execute();
            $players = $this->fluent->query->deleteFrom('players')->where('Login', '')->execute();

            if (!$challenges && !$players) {

                $this->prune = true;
                return;
            }
            // false if not found ChallengeId otherwise
            $recordsToDelete = $this->fluent->query
                ->from('records r')
                ->leftJoin('challenges c ON r.ChallengeId = c.Uid')
                ->select('r.ChallengeId, c.Id')
                ->where('c.Id IS NULL')
                ->fetchAll();

            if (!empty($recordsToDelete)) {
                Aseco::console('[RASP] ...Deleting records... ');
                $placeholders = implode(',', array_fill(0, count($recordsToDelete), '?'));
                $this->fluent->query->deleteFrom('records')->where("ChallengeId IN ($placeholders)", $recordsToDelete)->execute();
            }
            // Deleting records for deleted players
            $playerRecordsToDelete = $this->fluent->query
                ->from('records r')
                ->leftJoin('players p ON r.PlayerId = p.PlayerId')
                ->select('r.PlayerId, p.PlayerId')
                ->where('p.PlayerId IS NULL')
                ->fetch('p.PlayerId');

            if (!empty($playerRecordsToDelete)) {
                Aseco::console('[RASP] ...Deleting records for deleted players');
                $placeholders = implode(',', array_fill(0, count($playerRecordsToDelete), '?'));
                $this->fluent->query->deleteFrom('records')->where("PlayerId IN ($placeholders)", $playerRecordsToDelete)->execute();
            }
            // Deleting rs_times for deleted players
            $rsTimesToDelete = $this->fluent->query
                ->from('rs_times r')
                ->leftJoin('challenges c ON r.challengeID = c.Uid')
                ->select('r.challengeID, c.Uid')
                ->where('c.Uid IS NULL')
                ->fetch('r.challengeID');
            if (!empty($rsTimesToDelete)) {
                Aseco::console('[RASP] ...Deleting rs_times for deleted challenges');
                $placeholders = implode(',', array_fill(0, count($rsTimesToDelete), '?'));
                $this->fluent->query->deleteFrom('rs_times')->where("challengeID IN ($placeholders)", $rsTimesToDelete)->execute();
            }
            // Deleting rs_times for deleted players
            $rsPlayerTimesToDelete  = $this->fluent->query
                ->from('rs_times r')
                ->leftJoin('players p ON r.playerID = p.playerID')
                ->select('r.playerID, p.playerID')
                ->where('p.playerID IS NULL')
                ->fetch('r.playerID');
            if ($rsPlayerTimesToDelete) {
                Aseco::console('[RASP] ...Deleting rs_times for deleted players');
                $placeholders = implode(',', array_fill(0, count($rsPlayerTimesToDelete), '?'));
                $this->fluent->query->deleteFrom('rs_times')->where("playerID IN ($placeholders)", $rsPlayerTimesToDelete)->execute();
            }
        }
    }

    private function getChallengesCache(): array
    {
        return $this->challengeListCache;
    }
}
