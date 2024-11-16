<?php

declare(strict_types=1);

use Doctum\Doctum;
use Dotenv\Dotenv;
use Yuhzel\X8seco\Services\Log;

require __DIR__ . '/vendor/autoload.php';

// return new Doctum('E:\RPG Test\x8seco\src\\');
// frist do parse index.php 
// next do  update index.php //FIXME - TMX need be fixed

// Class container with auto-wire
$container = new League\Container\Container();
$container->delegate(new League\Container\ReflectionContainer(true));
//NOTE: I decided to use constructor only for Class injection and not for https://php.watch/versions/8.0/constructor-property-promotion

// Logger
Log::init();

// Load environment variables. safe is better for noobs
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$x8seco = $container->get(\Yuhzel\X8seco\App\X8seco::class);
$fluent = $container->get(\Yuhzel\X8seco\Database\Fluent::class);

echo $x8seco->run();