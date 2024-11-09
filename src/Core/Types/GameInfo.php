<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Types;

use Yuhzel\X8seco\Core\Gbx\GbxClient;

class GameInfo
{
    public const int TA = 1;
    public int $mode = self::TA;
    private array $data = [];

    public function __construct(
        private GbxClient $client,
<<<<<<< HEAD
    ) {}
=======
        private ?XmlArrayObject $data = null
    ) {
    }
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8

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
        foreach ($this->client->query('GetCurrentGameInfo', 1) as $key => $value) {
            $this->__set(lcfirst($key), $value);
        }
    }
}
