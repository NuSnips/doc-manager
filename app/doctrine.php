<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
    $settings = $container->get('settings');

    $config = ORMSetup::createAttributeMetadataConfiguration(
        $settings['doctrine']['metadata_dirs'],
        $settings['doctrine']['dev_mode']
    );

    $connection = DriverManager::getConnection($settings['doctrine']['connection'], $config);

    $entityManager = new EntityManager($connection, $config);
    $container->set(EntityManager::class, $entityManager);
};
