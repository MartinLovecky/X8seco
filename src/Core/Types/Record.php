<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use DateTimeImmutable;
use Yuhzel\X8seco\Core\Types\Player;
use Yuhzel\X8seco\Core\Types\Challenge;

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
