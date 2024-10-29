<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Core\Types;

use DateTimeImmutable;
use Yuhzel\Xaseco\Core\Types\Player;
use Yuhzel\Xaseco\Core\Types\Challenge;

class Record
{
    public function __construct(
        public Player $player,
        public Challenge $challenge,
        public int $score = 0,
        public string $date = '',
        public array $checks = [],
        public bool $new = false,
        public $pos = null
    ) {
        $date = new DateTimeImmutable();
        $this->date = $date->format('Y-m-d H:i:s');
    }
}
