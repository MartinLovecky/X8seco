<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Yuhzel\Xaseco\Core\Types\ChatCommand;

class ChatAdmin
{
    public const PLUGIN_NAME = 'ChatAdmin';
    public array $commands = [];
    public array $pmbuf = [];
    public int $pmlen = 30;
    public int $lnlen = 20;
    public bool $auto_scorepanel = true;
    public bool $rounds_finishpanel = true;

    public function __construct()
    {
        $this->commands = [
            ['help', [$this, 'help'], 'Shows all available /admin commands'],
            ['helpall', [$this, 'helpall'], 'Displays help for available /admin commands'],
            ['setservername', [$this, 'setservername'], 'Changes the name of the server'],
            ['setcomment', [$this, 'setcomment'], 'Changes the server comment'],
            ['setpwd', [$this, 'setpwd'], 'Changes the player password'],
            ['setspecpwd', [$this, 'setspecpwd'], 'Changes the spectator password'],
            ['setrefpwd', [$this, 'setrefpwd'], 'Changes the referee password'],
            ['setmaxplayers', [$this, 'setmaxplayers'], 'Sets a new maximum of players'],
            ['setmaxspecs', [$this, 'setmaxspecs'], 'Sets a new maximum of spectators'],
            ['setgamemode', [$this, 'setgamemode'], 'Sets next mode {ta,rounds,team,laps,stunts,cup}'],
            ['setrefmode', [$this, 'setrefmode'], 'Sets referee mode {0=top3,1=all}'],
            ['next', [$this, 'next'], 'Forces server to load next track'],
            ['skip', [$this, 'skip'], 'Forces server to load next track'],
            ['prev', [$this, 'prev'], 'Forces server to load previous track'],
            ['nextenv', [$this, 'nextenv'], 'Loads next track in same environment'],
            ['res', [$this, 'res'], 'Restarts currently running track'],
            ['replay', [$this, 'replay'], 'Replays current track (via jukebox)'],
            ['djb', [$this, 'djb'], 'Drops a track from the jukebox'],
            ['cjb', [$this, 'cjb'], 'Clears the entire jukebox'],
            ['clearhist', [$this, 'clearhist'], 'Clears (part of) track history'],
            ['add', [$this, 'add'], 'Adds tracks directly from TMX (<ID>... {sec})'],
            ['addthis', [$this, 'addthis'], 'Adds current /add-ed track permanently'],
            ['addlocal', [$this, 'addlocal'], 'Adds a local track (<filename>)'],
            ['warn', [$this, 'warn'], 'Sends a kick/ban warning to a player'],
            ['kick', [$this, 'kick'], 'Kicks a player from server'],
            ['kickghost', [$this, 'kickghost'], 'Kicks a ghost player from server'],
            ['ban', [$this, 'ban'], 'Bans a player from server'],
            ['unban', [$this, 'unban'], 'UnBans a player from server'],
            ['banip', [$this, 'banip'], 'Bans an IP address from server'],
            ['unbanip', [$this, 'unbanip'], 'UnBans an IP address from server'],
            ['black', [$this, 'black'], 'Blacklists a player from server'],
            ['unblack', [$this, 'unblack'], 'UnBlacklists a player from server'],
            ['addguest', [$this, 'addguest'], 'Adds a guest player to server'],
            ['removeguest', [$this, 'removeguest'], 'Removes a guest player from server'],
            ['pass', [$this, 'pass'], 'Passes a chat-based or TMX /add vote'],
            ['can', [$this, 'can'], 'Cancels any running vote'],
            ['er', [$this, 'er'], 'Forces end of current round'],
            ['players', [$this, 'players'], 'Displays list of known players {string}'],
            ['listbans', [$this, 'listbans'], 'Displays current ban list'],
            ['listips', [$this, 'listips'], 'Displays current banned IPs list'],
            ['listblacks', [$this, 'listblacks'], 'Displays current black list'],
            ['listguests', [$this, 'listguests'], 'Displays current guest list'],
            ['writeiplist', [$this, 'writeiplist'], 'Saves current banned IPs list (def: bannedips.xml)'],
            ['readiplist', [$this, 'readiplist'], 'Loads current banned IPs list (def: bannedips.xml)'],
            ['writeblacklist', [$this, 'writeblacklist'], 'Saves current black list (def: blacklist.txt)'],
            ['readblacklist', [$this, 'readblacklist'], 'Loads current black list (def: blacklist.txt)'],
            ['writeguestlist', [$this, 'writeguestlist'], 'Saves current guest list (def: guestlist.txt)'],
            ['readguestlist', [$this, 'readguestlist'], 'Loads current guest list (def: guestlist.txt)'],
            ['cleanbanlist', [$this, 'cleanbanlist'], 'Cleans current ban list'],
            ['cleaniplist', [$this, 'cleaniplist'], 'Cleans current banned IPs list'],
            ['cleanblacklist', [$this, 'cleanblacklist'], 'Cleans current black list'],
            ['cleanguestlist', [$this, 'cleanguestlist'], 'Cleans current guest list'],
            ['mergegbl', [$this, 'mergegbl'], 'Merges a global black list {URL}'],
            ['access', [$this, 'access'], 'Handles player access control (see: /admin access help)'],
            ['writetracklist', [$this, 'writetracklist'], 'Saves current track list (def: tracklist.txt)'],
            ['readtracklist', [$this, 'readtracklist'], 'Loads current track list (def: tracklist.txt)'],
            ['shuffle', [$this, 'shuffle'], 'Randomizes current track list'],
            ['listdupes', [$this, 'listdupes'], 'Displays list of duplicate tracks'],
            ['remove', [$this, 'remove'], 'Removes a track from rotation'],
            ['erase', [$this, 'erase'], 'Removes a track from rotation & deletes track file'],
            ['removethis', [$this, 'removethis'], 'Removes this track from rotation'],
            ['erasethis', [$this, 'erasethis'], 'Removes this track from rotation & deletes track file'],
            ['mute', [$this, 'mute'], 'Adds a player to global mute/ignore list'],
            ['unmute', [$this, 'unmute'], 'Removes a player from global mute/ignore list'],
            ['mutelist', [$this, 'mutelist'], 'Displays global mute/ignore list'],
            ['ignorelist', [$this, 'ignorelist'], 'Displays global mute/ignore list'],
            ['cleanmutes', [$this, 'cleanmutes'], 'Cleans global mute/ignore list'],
            ['addadmin', [$this, 'addadmin'], 'Adds a new admin'],
            ['removeadmin', [$this, 'removeadmin'], 'Removes an admin'],
            ['addop', [$this, 'addop'], 'Adds a new operator'],
            ['removeop', [$this, 'removeop'], 'Removes an operator'],
            ['listmasters', [$this, 'listmasters'], 'Displays current masteradmin list'],
            ['listadmins', [$this, 'listadmins'], 'Displays current admin list'],
            ['listops', [$this, 'listops'], 'Displays current operator list'],
            ['adminability', [$this, 'adminability'], 'Shows/changes admin ability {ON/OFF}'],
            ['opability', [$this, 'opability'], 'Shows/changes operator ability {ON/OFF}'],
            ['listabilities', [$this, 'listabilities'], 'Displays current abilities list'],
            ['writeabilities', [$this, 'writeabilities'], 'Saves current abilities list (def: adminops.xml)'],
            ['readabilities', [$this, 'readabilities'], 'Loads current abilities list (def: adminops.xml)'],
            ['wall', [$this, 'wall'], 'Displays popup message to all players'],
            ['delrec', [$this, 'delrec'], 'Deletes specific record on current track'],
            ['prunerecs', [$this, 'prunerecs'], 'Deletes records for specified track'],
            ['rpoints', [$this, 'rpoints'], 'Sets custom Rounds points (see: /admin rpoints help)'],
            ['matchs', [$this, 'matchs'], '{begin/end} to start/stop match tracking'],
            ['acdl', [$this, 'acdl'], 'Sets AllowChallengeDownload {ON/OFF}'],
            ['autotime', [$this, 'autotime'], 'Sets Auto TimeLimit {ON/OFF}'],
            ['disablerespawn', [$this, 'disablerespawn'], 'Disables respawn at CPs {ON/OFF}'],
            ['forceshowopp', [$this, 'forceshowopp'], 'Forces to show opponents {##/ALL/OFF}'],
            ['scorepanel', [$this, 'scorepanel'], 'Shows automatic scorepanel {ON/OFF}'],
            ['roundsfinish', [$this, 'roundsfinish'], 'Shows rounds panel upon first finish {ON/OFF}'],
            ['forceteam', [$this, 'forceteam'], 'Forces player into {Blue} or {Red} team'],
            ['forcespec', [$this, 'forcespec'], 'Forces player into free spectator'],
            ['specfree', [$this, 'specfree'], 'Forces spectator into free mode'],
            ['panel', [$this, 'panel'], 'Selects admin panel (see: /admin panel help)'],
            ['style', [$this, 'style'], 'Selects default window style'],
            ['admpanel', [$this, 'admpanel'], 'Selects default admin panel'],
            ['donpanel', [$this, 'donpanel'], 'Selects default donate panel'],
            ['recpanel', [$this, 'recpanel'], 'Selects default records panel'],
            ['votepanel', [$this, 'votepanel'], 'Selects default vote panel'],
            ['coppers', [$this, 'coppers'], 'Shows server\'s coppers amount'],
            ['pay', [$this, 'pay'], 'Pays server coppers to login'],
            ['relays', [$this, 'relays'], 'Displays relays list or shows relay master'],
            ['server', [$this, 'server'], 'Displays server\'s detailed settings'],
            ['pm', [$this, 'pm'], 'Sends personal message to player'],
            ['pmlog', [$this, 'pmlog'], 'Displays log of recent private admin messages'],
            ['call', [$this, 'call'], 'Executes direct server call (see: /admin call help)'],
            ['unlock', [$this, 'unlock'], 'Unlocks admin commands & features'],
            ['debug', [$this, 'debug'], 'Toggles debugging output'],
            ['shutdown', [$this, 'shutdown'], 'Shuts down XASECO'],
            ['shutdownall', [$this, 'shutdownall'], 'Shuts down Server & XASECO']
        ];
    }

    public function onStartup(): void
    {
        ChatCommand::registerCommands($this->commands, self::PLUGIN_NAME, true);
    }

    public function admin($command)
    {
        //$jukebox;   //from RaspJukebox
        $admin = $command['author'];
        $login = $admin->login;
        // Split params into arrays & ensure optional parameters exist
        $arglist = explode(' ', $command['params'], 2);
        if (!isset($arglist[1])) {
            $arglist[1] = '';
        }
        $command['params'] = explode(' ', preg_replace('/ +/', ' ', $command['params']));
        if (!isset($command['params'][1])) {
            $command['params'][1] = '';
        }
    }

    public function help()
    {
    }

    public function helpall()
    {
    }
    public function setservername()
    {
    }
    public function setcomment()
    {
    }
    public function setpwd()
    {
    }
    public function setspecpwd()
    {
    }
    public function setrefpwd()
    {
    }
    public function setmaxplayers()
    {
    }
    public function setmaxspecs()
    {
    }
    public function setgamemode()
    {
    }
    public function setrefmode()
    {
    }
    public function next()
    {
    }
    public function skip()
    {
    }
    public function prev()
    {
    }
    public function nextenv()
    {
    }
    public function res()
    {
    }
    public function replay()
    {
    }
    public function djb()
    {
    }
    public function cjb()
    {
    }
    public function clearhist()
    {
    }
    public function add()
    {
    }
    public function addthis()
    {
    }
    public function addlocal()
    {
    }
    public function warn()
    {
    }
    public function kick()
    {
    }
    public function kickghost()
    {
    }
    public function ban()
    {
    }
    public function unban()
    {
    }
    public function banip()
    {
    }
    public function unbanip()
    {
    }
    public function black()
    {
    }
    public function unblack()
    {
    }
    public function addguest()
    {
    }
    public function removeguest()
    {
    }
    public function pass()
    {
    }
    public function can()
    {
    }
    public function er()
    {
    }
    public function players()
    {
    }
    public function listbans()
    {
    }
    public function listips()
    {
    }
    public function listblacks()
    {
    }
    public function listguests()
    {
    }
    public function writeiplist()
    {
    }
    public function readiplist()
    {
    }
    public function writeblacklist()
    {
    }
    public function readblacklist()
    {
    }
    public function writeguestlist()
    {
    }
    public function readguestlist()
    {
    }
    public function cleanbanlist()
    {
    }
    public function cleaniplist()
    {
    }
    public function cleanblacklist()
    {
    }
    public function cleanguestlist()
    {
    }
    public function mergegbl()
    {
    }
    public function access()
    {
    }
    public function writetracklist()
    {
    }
    public function readtracklist()
    {
    }
    public function shuffle()
    {
    }
    public function listdupes()
    {
    }
    public function remove()
    {
    }
    public function erase()
    {
    }
    public function removethis()
    {
    }
    public function erasethis()
    {
    }
    public function mute()
    {
    }
    public function unmute()
    {
    }
    public function mutelist()
    {
    }
    public function ignorelist()
    {
    }
    public function cleanmutes()
    {
    }
    public function addadmin()
    {
    }
    public function removeadmin()
    {
    }
    public function addop()
    {
    }
    public function removeop()
    {
    }
    public function listmasters()
    {
    }
    public function listadmins()
    {
    }
    public function listops()
    {
    }
    public function adminability()
    {
    }
    public function opability()
    {
    }
    public function listabilities()
    {
    }
    public function writeabilities()
    {
    }
    public function readabilities()
    {
    }
    public function wall()
    {
    }
    public function delrec()
    {
    }
    public function prunerecs()
    {
    }
    public function rpoints()
    {
    }
    public function matchs()
    {
    }
    public function acdl()
    {
    }
    public function autotime()
    {
    }
    public function disablerespawn()
    {
    }
    public function forceshowopp()
    {
    }
    public function scorepanel()
    {
    }
    public function roundsfinish()
    {
    }
    public function forceteam()
    {
    }
    public function forcespec()
    {
    }
    public function specfree()
    {
    }
    public function panel()
    {
    }
    public function style()
    {
    }
    public function admpanel()
    {
    }
    public function donpanel()
    {
    }
    public function recpanel()
    {
    }
    public function votepanel()
    {
    }
    public function coppers()
    {
    }
    public function pay()
    {
    }
    public function relays()
    {
    }
    public function server()
    {
    }
    public function pm()
    {
    }
    public function pmlog()
    {
    }
    public function call()
    {
    }
    public function unlock()
    {
    }
    public function debug()
    {
    }
    public function shutdown()
    {
    }
    public function shutdownall()
    {
    }
}
