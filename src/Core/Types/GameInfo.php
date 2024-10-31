<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use Yuhzel\X8seco\Core\Gbx\GbxClient;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

class GameInfo
{
    public const int TA = 1;
    public int $mode = self::TA;

    public function __construct(
        private GbxClient $client,
        private ?XmlArrayObject $data = null
    ) {
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function setGameInfo(): void
    {
        $this->data = $this->client->query('GetCurrentGameInfo', 1);
    }
}
