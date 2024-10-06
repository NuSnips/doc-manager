<?php

declare(strict_types=1);

use DI\Container;
use DI\Bridge\Slim\Bridge as SlimAppFactory;
use Dotenv\Dotenv;

// Set the default time zone 
date_default_timezone_set('Asia/Colombo');

require __DIR__ . "/../vendor/autoload.php";

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
// Create a container
$container = new Container();

// Load the settings into the container
$settings =  require __DIR__ . "/../app/settings.php";
$settings($container);

// Set up Doctrine
$doctrine = require __DIR__ . "/../app/doctrine.php";
$doctrine($container);

$elasticSearch = require __DIR__ . "/../app/elasticsearch.php";
$elasticSearch($container);
$app = SlimAppFactory::create($container);


$middleware = require __DIR__ . "/../app/middleware.php";
$middleware($app);

$routes = require __DIR__ . "/../app/routes.php";
$routes($app);


$app->run();
