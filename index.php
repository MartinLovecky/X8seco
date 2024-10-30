<?php

declare(strict_types=1);

/**
 * This version of the Aseco is an upgraded and modified version of the Xaseco from the Xymph
 * all original Authors metioned 
 * Due heavy changes many parts of code function diffrently 
 * I gave you permition to change anything and everything you want just
 * Please visit github for more information.
 * Created by Yuhzel member of AMP team
 */

use Doctum\Doctum;
use Dotenv\Dotenv;
use Yuhzel\X8seco\Services\Log;

require __DIR__ . '/vendor/autoload.php';

// return new Doctum('E:\RPG Test\xaseco8\src\\');
// frist do parse index.php 
// next do  update index.php

// Class container with auto-wire
$container = new League\Container\Container();
$container->delegate(new League\Container\ReflectionContainer(true));

// Logger
Log::init();

// Load environment variables. safe is better for noobs
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$aseco = $container->get(\Yuhzel\X8seco\App\Aseco::class);
$fluent = $container->get(\Yuhzel\X8seco\Database\Fluent::class);

echo $aseco->run();

// vendor/bin/phpstan analyse src --memory-limit=256M
// php-cs-fixer fix src
// @phpstan-ignore-next-line
// ...$args always result in an array