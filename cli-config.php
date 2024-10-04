<?php

declare(strict_types=1);

use DI\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$container = new Container();

$settings = require __DIR__ . '/app/settings.php';
$settings($container);

$doctrine = require __DIR__ . '/app/doctrine.php';
$doctrine($container);

$entityManager = $container->get(EntityManager::class);
return ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
    $commands
);
