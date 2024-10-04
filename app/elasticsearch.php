<?php

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
    $settings = $container->get('settings');
    $client = ClientBuilder::create()
        ->setHosts([$settings['elasticsearch']['host']])
        ->setApiKey($settings['elasticsearch']['api_key'])
        ->build();
    $container->set(Client::class, $client);
};
