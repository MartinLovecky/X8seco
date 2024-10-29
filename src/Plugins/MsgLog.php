<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

use Yuhzel\Xaseco\Services\Basic;

class MsgLog
{
    public function __construct(
        public array $msgbuf = [],
        public int $msglen =  21,
        public int $linelen = 800,
        public int $winlen = 5
    ) {
    }

    public function send_window_message(string $message, int $scoreboard): void
    {
        $message = explode("\n", $message);
        foreach ($message as $item) {
            $multi = explode("\n", wordwrap('$z$s' . $item, $this->linelen, "\n" . '$z$s$n'));
            foreach ($multi as $line) {
                if (count($this->msgbuf) >= $this->msglen) {
                    array_shift($this->msgbuf);
                }
                $this->msgbuf[] = Basic::formatColors($line);
            }
        }

        $timeout = 0;
        if ($scoreboard) {
            //$aseco->client->query('GetChatTime');
            //$timeout = $aseco->client->getResponse();
            //$timeout = $timeout['CurrentValue'] + 5000;
        } else {
            //$timeout = $aseco->settings['window_timeout'] * 1000;
        }
        $lines = array_slice($this->msgbuf, -$this->winlen);

        // $this->maniaLinks->display_msgwindow($lines, $timeout);
    }
}
