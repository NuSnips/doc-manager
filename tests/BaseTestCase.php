<?php

namespace Tests;

use DI\Container;
use DI\Bridge\Slim\Bridge as SlimAppFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

class BaseTestCase extends TestCase

{
    protected $app;
    protected $container;
    protected $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        $settings = require __DIR__ . "/../app/settings.php";
        $settings($this->container);

        $doctrine = require __DIR__ . "/../app/doctrine.php";
        $doctrine($this->container);

        $this->app = SlimAppFactory::create($this->container);

        $middleware = require __DIR__ . "/../app/middleware.php";
        $middleware($this->app);

        $routes = require __DIR__ . "/../app/routes.php";
        $routes($this->app);

        // Set up the database schema
        $this->entityManager = $this->container->get(EntityManager::class);
        $this->setUpSchema();
    }

    protected function tearDown(): void
    {
        // Drop the database schema
        $this->tearDownSchema();

        parent::tearDown();
    }

    protected function setUpSchema()
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool->createSchema($classes);
    }

    protected function tearDownSchema()
    {
        $tool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
    }

    protected function jsonDecode(string $response)
    {
        return json_decode($response, true);
    }
    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}
