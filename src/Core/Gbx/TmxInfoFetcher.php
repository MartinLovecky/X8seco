<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Gbx;

class TmxInfoFetcher
{
    public string $section = '';
    public string $prefix = '';
    public string|int $uid = 0;
    public string|int $id = 0;
    public bool $records = false;
    public string $error = '';
    public string $name = '';
    public string|int $userid = 0;
    public string $author = '';
    public string $uploaded = '';
    public string $updated = '';
    public bool $visible = true;
    public string $type = '';
    public string $envir = '';
    public string $mood = '';
    public string $style = '';
    public string $routes = '';
    public string $length = '';
    public string $diffic = '';
    public string $lbrating = '';
    public int $awards = 0;
    public int $comments = 0;
    public bool $custimg = false;
    public string $game = '';
    public string $acomment = '';
    public string $pageurl = '';
    public string|int $replayid = 0;
    public string $replayurl  = '';
    public string $imageurl = '';
    public string $thumburl = '';
    public string $dloadurl = '';
    public array $recordlist = [];

    public function __construct(string $game, string|int $id, bool $records)
    {
        $this->section = $game;
        $this->records = $records;
        $this->prefix = match ($game) {
            'TMO' => 'original',
            'TMS' => 'sunrise',
            'TMN' => 'nations',
            'TMU' => 'united',
            'TMNF' => 'tmnforever',
            default => '',
        };

        if (preg_match('/^\w{24,27}$/', (string)$id)) {
            $this->uid = $id;
            $this->getData(true);
        } elseif (is_numeric($id) && (int)$id > 0) {
            $this->id = (int)$id;
            $this->getData(false);
        }
    }

    private function getData(bool $isUid): void
    {
        $url = 'http://' . $this->prefix . '.tm-exchange.com/apiget.aspx?action=apitrackinfo&' . ($isUid ? 'u' : '') . 'id=' . ($isUid ? $this->uid : $this->id);
        $file = $this->getFile($url);

        if ($file === false) {
            $this->error = 'Connection or response error on ' . $url;
            return;
        }

        if (strpos($file, chr(27)) !== false) {
            $this->error = 'Cannot decode main track info';
            return;
        }

        $fields = explode(chr(9), $file);

        $this->populateFields($fields, $isUid);
        $this->fetchMiscTrackInfo();

        if ($this->records) {
            $this->fetchTrackRecords();
        }
    }

    private function getFile(string $url): string|false
    {
        $urlComponents = parse_url($url);
        $port = $urlComponents['port'] ?? 80;
        $query = $urlComponents['query'] ?? '';

        $fp = @fsockopen($urlComponents['host'], $port, $errno, $errstr, 4);
        if (!$fp) {
            return false;
        }

        fwrite($fp, 'GET ' . $urlComponents['path'] . ($query ? "?$query" : '') . " HTTP/1.0\r\n" .
            'Host: ' . $urlComponents['host'] . "\r\n" .
            'User-Agent: TMXInfoFetcher (' . PHP_OS . ")\r\n\r\n");
        stream_set_timeout($fp, 2);
        $response = stream_get_contents($fp);
        fclose($fp);

        if ($response === false || substr($response, 9, 3) !== '200') {
            return false;
        }

        $page = explode("\r\n\r\n", $response, 2);

        return trim($page[1] ?? '');
    }

    private function populateFields(array $fields, bool $isUid): void
    {
        if ($isUid) {
            [
                $this->id,
                $this->name,
                $this->userid,
                $this->author,
                $this->uploaded,
                $this->updated,
                $visible,
                $this->type,
                $this->envir,
                $this->mood,
                $this->style,
                $this->routes,
                $this->length,
                $this->diffic,
                $this->lbrating,
                $this->game,
                $comment
            ] = $fields;

            $this->id = $this->numeric($this->id);
            $this->userid = $this->numeric($this->userid);
            $this->visible = filter_var($visible, FILTER_VALIDATE_BOOLEAN);
            $this->acomment = $this->formatComment($comment);
            $this->pageurl  = "http://{$this->prefix}.tm-exchange.com/main.aspx?action=trackshow&id={$this->id}";
            $this->imageurl = "http://{$this->prefix}.tm-exchange.com/get.aspx?action=trackscreen&id={$this->id}";
            $this->thumburl = "http://{$this->prefix}.tm-exchange.com/get.aspx?action=trackscreensmall&id={$this->id}";
            $this->dloadurl = "http://{$this->prefix}.tm-exchange.com/get.aspx?action=trackgbx&id={$this->id}";
        }
    }

    private function fetchMiscTrackInfo(): void
    {
        $url = "http://{$this->prefix}.tm-exchange.com/apiget.aspx?action=apisearch&trackid={$this->id}";
        $file = $this->getFile($url);

        if ($file === false || strpos($file, chr(27)) !== false) {
            return;
        }

        $fields = explode(chr(9), $file);
        $this->awards   = $this->numeric($fields[12]);
        $this->comments = $this->numeric($fields[13]);
        $this->replayid = $this->numeric($fields[16]);

        if ($this->replayid > 0) {
            $this->replayurl = "http://{$this->prefix}tm-exchange.com/get.aspx?action=recordgbx&id={$this->replayid}";
        }
    }

    private function fetchTrackRecords(): void
    {
        $url = "http://{$this->prefix}.tm-exchange.com/apiget.aspx?action=apitrackrecords&id={$this->id}";
        $file = $this->getFile($url);

        if ($file === false) {
            return;
        }

        $lines = explode("\r\n", $file);

        foreach (array_slice($lines, 0, 10) as $index => $line) {
            if ($line === '') {
                break;
            }

            $fields = explode(chr(9), $line);
            $this->recordlist[$index] = [
                'replayid'  => $this->numeric($fields[0]),
                'userid'    => $this->numeric($fields[1]),
                'name'      => $fields[2] ?? '',
                'time'      => $this->numeric($fields[3]),
                'replayat'  => $fields[4] ?? '',
                'trackat'   => $fields[5] ?? '',
                'approved'  => filter_var($fields[6], FILTER_VALIDATE_BOOLEAN),
                'score'     => $this->numeric($fields[7]),
                'expires'   => filter_var($fields[8], FILTER_VALIDATE_BOOLEAN),
                'lockspan'  => filter_var($fields[9], FILTER_VALIDATE_BOOLEAN),
                'replayurl' => "http://{$this->prefix}.tm-exchange.com/get.aspx?action=recordgbx&id={$fields[0]}",
            ];
        }
    }

    private function numeric(mixed $value): mixed
    {
        return is_numeric($value) ? (int)$value : $value;
    }

    private function formatComment(string $comment): string
    {
        $search = [chr(31), '[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[url]', '[/url]'];
        $replace = ['<br/>', '<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<i>', '</i>'];
        $formatted = str_ireplace($search, $replace, $comment);
        return preg_replace('/\[url=".*"\]/', '<i>', $formatted) ?? $formatted;
    }
}
