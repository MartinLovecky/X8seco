<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Services;

use InvalidArgumentException;
use Yuhzel\Xaseco\Services\Log;

class Basic
{
    public static array $colors = [
        'Welcome ' => '$f00',
        'Server ' => '$ff0',
        'Highlite ' => '$fff',
        'Timelite ' => '$bbb',
        'Record ' => '$0f3',
        'Emotic ' => '$fa0',
        'Music ' => '$d80',
        'Message ' => '$39f',
        'Rank ' => '$ff3',
        'Vote ' => '$f8f',
        'karma ' => '$ff0',
        'Donate ' => '$f0f',
        'Admin ' => '$ff0',
        'Black ' => '$000',
        'Grey ' => '$888',
        'Login ' => '$00f',
        'Logina ' => '$0c0',
        'Nick ' => '$f00',
        'interact ' => '$ff0$i',
        'dedimsg ' => '$28b',
        'dedirec ' => '$0b3',
        'Error ' => '$f00$i'
    ];

    public static array $message = [
        "startup" => "{#server}*** XASECO {#highlite}v{1}{#server} running on {#highlite}{2}{#server}:{#highlite}{3}{#server} ***",
        "welcome" => '{#welcome}Welcome {#highlite}{1}{#welcome} to {#highlite}{2}$z$s{br}{#welcome}This server uses {#highlite}XASECO v{3}{#welcome} to manage your records.',
        "warning" => '$s{#welcome}This is an administrative warning.{br}{br}$gWhatever you wrote is against our server\'s{br}policy. Not respecting other players, or{br}using offensive language might result in a{br}{#welcome}kick, or ban {#message}the next time.{br}{br}$gThe server administrators.',
        "record_current" => "{#server}>> {#message}Current record on {#highlite}{1}{#message} is {#highlite}{2}{#message} by {#highlite}{3}",
        "record_none" => "{#server}>> {#message}Currently no record on {#highlite}{1}{#message} ...",
        "record_error" => "{#server}>> {#error}Could not get records from database... No records this round!",
        "ranking" => "{#server}>> {#message}Local Record rankings on {#highlite}{1}{#message} {2} this round:",
        "ranking_range" => "{#server}>> {#message}Local Record rankings on {#highlite}{1}{#message} {2} this round (range {#highlite}{3}{#message}):",
        "ranking_new" => "{#server}>> {#message}Local Record rankings on {#highlite}{1}{#message} {2} this round ({#highlite}{3}{#message} new):",
        "ranking_nonew" => "{#server}>> {#message}Local Record rankings on {#highlite}{1}{#message} {2} this round: none new so far",
        "ranking_none" => "{#server}>> {#message}Local Record rankings on {#highlite}{1}{#message} {2} this round: no records!",
        "ranking_record_new_on" => '{#rank}{1}{#message}.$i{#highlite}{2}{#message}[{#highlite}{3}{#message}]$i,',
        "ranking_record_new" => "{#rank}{1}{#message}.{#highlite}{2}{#message}[{#highlite}{3}{#message}],",
        "ranking_record_on" => '{#rank}{1}{#message}.$i{#timelite}{2}{#message}[{#timelite}{3}{#message}]$i,',
        "ranking_record" => "{#rank}{1}{#message}.{#timelite}{2}{#message}[{#timelite}{3}{#message}],",
        "ranking_record2" => "{#rank}{1}{#message}.{#timelite}{2}{#message},",
        "first_record" => "{#server}> {#record}The first Local record is:",
        "last_record" => "{#server}> {#record}The last Local record is:",
        "diff_record" => "{#server}> {#record}Difference between {1}{#record} and {2}{#record} is: {#highlite}{3}",
        "summary" => '{#server}> {#highlite}{1} $z$s{#record}has {#highlite}{2}{#record} Local record{3}, the top {4} being:',
        "sum_entry" => "{#highlite}{1} {#record}rec{2} #{#rank}{3}{#record},",
        "wins" => "{#server}> {#record}You have already won {#highlite}{1}{#record} race{2}",
        "win_new" => "{#server}> {#record}Congratulations, you won your {#highlite}{1}{#record}. race!",
        "win_multi" => "{#server}>> {#record}Congratulations, {#highlite}{1}{#record} won his/her {#highlite}{2}{#record}. race!",
        "mute" => '{#server}> Player {#highlite}{1}$z$s{#server} is muted!',
        "unmute" => '{#server}> Player {#highlite}{1}$z$s{#server} is unmuted!',
        "muted" => "{#server}> {#highlite}{1}{#error} disabled because you are on the global mute list!",
        "donation" => '{#donate} Donate {#highlite}{1}{#donate} coppers to {#highlite}{2}$z',
        "thanks_all" => '{#server}>> {#highlite}{1}$z$s{#donate} received a donation of {#highlite}{2}{#donate} coppers from {#highlite}{3}$z$s{#donate}.  Thank You!',
        "thanks_you" => "{#server}> {#donate}You made a donation of {#highlite}{1}{#donate} coppers.  Thank You!",
        "donate_minimum" => '{#server}> {#error}Minimum donation amount is {#highlite}$i {1}{#error} coppers!',
        "donate_help" => '{#server}> {#error}Use {#highlite}$i /donate <number>{#error} to donate coppers to the server',
        "payment" => '{#donate} Send {#highlite}{1}{#donate} coppers to {#highlite}{2}$z',
        "pay_insuff" => '{#server}> {#error}Insufficient server coppers: {#highlite}$i {1}{#error}!',
        "pay_server" => "{#server}> {#error}Cannot pay this server itself!",
        "pay_confirm" => "{#server}> {#donate}Payment of {#highlite}{1}{#donate} coppers to {#highlite}{2}{#donate} confirmed!  Remaining coppers: {#highlite}{3}",
        "pay_cancel" => "{#server}> {#donate}Payment to {#highlite}{1}{#donate} cancelled!",
        "pay_help" => '{#server}> {#error}Use {#highlite}$i /admin pay <login> $m<number>{#error} to send server coppers to a login',
        "playtime" => "{#server}> Current track {#highlite}{1}{#server} has been played for {#highlite}{2}",
        "playtime_finish" => "{#server}>> Current track {#highlite}{1}{#server} finished after {#highlite}{2}",
        "playtime_replay" => "{#server}({#highlite}{1}{#server} replay{2}, total {#highlite}{3}{#server})",
        "track" => "{#server}> Current track {#highlite}{1} {#server}by {#highlite}{2}  {#server}Author: {#highlite}{3} {#server}Gold: {#highlite}{4} {#server}Silver: {#highlite}{5} {#server}Bronze: {#highlite}{6} {#server}Cost: {#highlite}{7}",
        "current_track" => "{#server}>> Current track {#highlite}{1} {#server}by {#highlite}{2}  {#server}Author: {#highlite}{3}",
        "rpoints_named" => "{#server}> {1}Custom points system set to {#highlite}{2}{3}: {#highlite}{4},...",
        "rpoints_nameless" => "{#server}> {1}Custom points system set to: {#highlite}{2},...",
        "no_rpoints" => "{#server}> {1}No custom Rounds points system defined!",
        "no_relays" => "{#server}> {#error}No relay servers connected",
        "relaymaster" => "{#server}> This server relays master server: {#highlite}{1}{#server} ({#highlite}{2}{#server})",
        "notonrelay" => "{#server}> {#error}Command unavailable on relay server",
        "uptodate_ok" => "{#server}>> {#message}This XASECO version {#highlite}{1}{#message} is up to date",
        "uptodate_new" => "{#server}>> {#message}New XASECO version {#highlite}{1}{#message} available from {#highlite}{2}",
        "banip_dialog" => '{#welcome}Your IP was banned from this server.$z',
        "banip_error" => "{#welcome}Could not connect:{br}{br}Your IP was banned from this server!",
        "client_dialog" => '{#welcome}Obsolete client version, please $l[http://www.tm-forum.com/viewtopic.php?p=139752#p139752]upgrade$l.$z',
        "client_error" => '{#welcome}Obsolete client version!{br}Please upgrade to the $l[http://www.tm-forum.com/viewtopic.php?p=139752#p139752]latest version$l.',
        "connect_dialog" => '{#welcome}Connection problem, please retry.$z',
        "connect_error" => '{#welcome}$sThis is an administrative notice.$z{br}{br}XASECO encountered a very rare player connection{br}problem. Please re-join the server to correct it.{br}Apologies for the inconvenience.{br}{br}$sThe server administrators.',
        "idlekick_play" => '{#server}>> IdleKick player {#highlite}{1}$z$s{#server} after {#highlite}{2}{#server} challenge{3}!',
        "idlespec_play" => '{#server}>> IdleSpec player {#highlite}{1}$z$s{#server} after {#highlite}{2}{#server} challenge{3}',
        "idlekick_spec" => '{#server}>> IdleKick spectator {#highlite}{1}$z$s{#server} after {#highlite}{2}{#server} challenge{3}!',
        "song" => "{#server}> Track {#highlite}{1} {#server}plays song: {#highlite}{2}",
        "mod" => "{#server}> Track {#highlite}{1} {#server}uses mod: {#highlite}{2} {#server}({#highlite}{3}{#server})",
        "coppers" => '{#server}> Server {#highlite}{1}$z$s {#server}owns {#highlite}{2} {#server}coppers!',
        "time" => '{#server}> {#interact}Current Server Time: {#highlite}$i {1}{#interact} on {#highlite}$i {2}',
        "tmxrec" => "{#server}>> {#record}TMX World Record: {#highlite}{1}{#record} by {#highlite}{2}",
        "round" => '$n{#message}R{#highlite}{1}{#message}>',
        "no_cpsspec" => "{#server}> {#highlite}/cpsspec{#server} is not currently enabled on this server.",
        "no_admin" => "{#server}> {#error}You have to be in admin list to do that!",
        "help_explanation" => "{#server}> Press the {#highlite}C{#server} key to see the whole list, and use {#highlite}/helpall{#server} for details",
        "united_only" => "{#server}> {#error}This requires a TM United Forever {1}!",
        "forever_only" => "{#server}> {#error}Command only available on TM Forever!"
    ];

    public static string $path = '';


    public static function getChatMessage(string $message): ?string
    {
        if (array_key_exists($message, self::$message)) {
            return htmlspecialchars_decode(self::$message[$message]);
        }

        self::console('[XAseco] Invalid message in getChatMessage [{1}]', $message);
        return null;
    }

    public static function stripColors(string $input, bool $for_tm = true): string
    {
        // Replace all occurrences of double dollar signs with a null character
        $input = str_replace('$$', "\0", $input);

        // Strip TMF H, L, & P links, keeping the first and second capture groups if present
        $input = preg_replace(
            '/
            # Match and strip H, L, and P links with square brackets
            \$[hlp]         # Match a $ followed by h, l, or p (link markers)
            (.*?)           # Non-greedy capture of any content after the link marker
            (?:             # Start non-capturing group for possible brackets content
                \[.*?\]     # Match any content inside square brackets
                (.*?)       # Non-greedy capture of any content after the square brackets
            )*              # Zero or more occurrences of the bracketed content
            (?:\$[hlp]|$)   # Match another $ with h, l, p or end of string
            /ixu',
            '$1$2',  // Replace with the content of the first and second capture groups
            $input
        );

        // Strip various patterns beginning with an unescaped dollar sign
        $input = preg_replace(
            '/
            # Match a single unescaped dollar sign and one of the following:
            \$
            (?:
                [0-9a-f][^$][^$]  # Match color codes: hexadecimal + 2 more chars
                | [0-9a-f][^$]    # Match incomplete color codes
                | [^][hlp]        # Match any style code that isn’t H, L, or P
                | (?=[][])        # Match $ followed by [ or ], but keep the brackets
                | $               # Match $ at the end of the string
            )
            /ixu',
            '',  // Remove the dollar sign and matched sequence
            $input
        );

        // Restore null characters to dollar signs if needed for displaying in TM or logs
        return str_replace("\0", $for_tm ? '$$' : '$', $input);
    }

    public static function customSprintf(string $format, ...$args): string
    {
        // Replace null values with the string "null"
        $args = array_map(function ($arg) {
            return $arg === null ? 'null' : $arg;
        }, $args);

        // Call sprintf with the modified arguments
        return sprintf($format, ...$args);
    }

    public static function formatColors(string $text): string
    {
        foreach (self::$colors as $color => $value) {
            $text = str_replace('{#' . strtolower($color) . '}', $value, $text);
        }

        return $text;
    }

    public static function formatText(mixed ...$args): string
    {
        // first parameter is the text to format
        $text = array_shift($args);

        if (!is_string($text)) {
            throw new InvalidArgumentException('The first argument must be a string.');
        }

        foreach ($args as $i => $param) {
            if (!is_string($param) && !is_int($param)) {
                throw new InvalidArgumentException('Argument #' . ($i + 2) . ' must be of type string or int.');
            }

            $text = str_replace('{' . ($i + 1) . '}', (string)$param, $text);
        }
        return $text;
    }

    public static function console(mixed ...$args): void
    {
        $formattedText  = self::formatText(...$args);
        $timestamp = date('m/d,H:i:s');
        $message = "[{$timestamp}] {$formattedText}\r\n";

        echo $message . PHP_EOL;
        Log::info($message);
        flush();
    }

    public static function validateUTF8(string $string): string
    {
        return mb_convert_encoding($string, 'UTF-8', mb_list_encodings());
    }

    /**
     * Formats a string from the format sssshh0 (milliseconds)
     * into the format mmm:ss.hh (or mmm:ss if $hsec is false)
     */
    public static function formatTime(int $MwTime, bool $hsec = true): string
    {
        if ($MwTime === -1) {
            return '???';
        }

        // Calculate minutes, seconds, and hundredths of seconds
        $minutes = floor($MwTime / (1000 * 60));
        $seconds = floor(($MwTime % (1000 * 60)) / 1000);
        $hundredths = floor(($MwTime % 1000) / 10);

        // Format the time string based on whether hundredths of seconds are needed
        if ($hsec) {
            $formattedTime = sprintf('%02d:%02d.%02d', $minutes, $seconds, $hundredths);
        } else {
            $formattedTime = sprintf('%02d:%02d', $minutes, $seconds);
        }

        // Remove leading zero if present
        if ($formattedTime[0] == '0') {
            $formattedTime = ltrim($formattedTime, '0');
        }

        return $formattedTime;
    }

    /**
     * Formats a string from the format sssshh0
     * into the format hh:mm:ss.hh (or hh:mm:ss if $hsec is false)
     */
    public static function formatTimeH(int|string $MwTime, bool $hsec = true): string
    {
        if ($MwTime == -1) {
            return '???';
        }

        $MwTime = (string) $MwTime;
        $hseconds = substr($MwTime, -3, 2);
        $MwTime = (int) substr($MwTime, 0, -3);

        $hours = intdiv($MwTime, 3600);
        $MwTime %= 3600;
        $minutes = intdiv($MwTime, 60);
        $seconds = $MwTime % 60;

        $formattedTime = $hsec
            ? sprintf('%02d:%02d:%02d.%02d', $hours, $minutes, $seconds, $hseconds)
            : sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        // Remove leading zeros in hours if it's less than 1 hour
        if ($hours == 0) {
            $formattedTime = ltrim($formattedTime, '0');
        }

        return $formattedTime;
    }

    /**
     * Convert boolean value to text string
     */
    public static function bool2text(bool $boolval): string
    {
        return $boolval ? 'True' : 'False';
    }

    public static function path(?int $level = null): string
    {
        return dirname(__DIR__, $level ?? 2) . DIRECTORY_SEPARATOR;
    }

    public static function isLANLogin(string $login): bool
    {
        $n = "(25[0-5]|2[0-4]\d|[01]?\d\d|\d)";
        return (preg_match("/(\/{$n}\\.{$n}\\.{$n}\\.{$n}:\d+)$/", $login) ||
            preg_match("/(_{$n}\\.{$n}\\.{$n}\\.{$n}_\d+)$/", $login));
    }
}
