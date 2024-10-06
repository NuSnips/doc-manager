<?php

use App\Application\Service\DocumentService;
use App\Application\Service\UserService;
use App\Domain\Document\Repository\DocumentRepository;
use App\Domain\Document\Service\DocumentServiceInterface;
use App\Domain\Document\Storage\DocumentStorageInterface;
use App\Domain\DocumentShare\Repository\DocumentShareRepository;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\AuthenticationService;
use App\Domain\User\Service\UserServiceInterface;
use App\Infrastructure\Persistence\DoctrineDocumentRepository;
use App\Infrastructure\Persistence\DoctrineDocumentShareRepository;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Infrastructure\Persistence\ElasticSearchDocumentRepository;
use App\Infrastructure\Service\DoctrineAuthService;
use App\Infrastructure\Storage\DocumentStorage;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Elastic\Elasticsearch\Client;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
    $container->set('settings', function () {
        return [
            'displayErrorDetails' => true,
            'logErrorDetails' => true,
            'logErrors' => true,
            'doctrine' => [
                'dev_mode' => true,
                'cache_dir' => __DIR__ . '/../var/cache/doctrine',
                'metadata_dirs' => [__DIR__ . '/../src/'],
                'connection' => [
                    'driver' => $_ENV['APP_ENV'] === 'testing' ? $_ENV['DB_DRIVER_TEST'] : $_ENV['DB_DRIVER'],
                    'host' => $_ENV['APP_ENV'] === 'testing' ? '' : $_ENV['DB_HOST'],
                    'port' => $_ENV['APP_ENV'] === 'testing' ? '' : $_ENV['DB_PORT'],
                    'dbname' => $_ENV['DB_NAME'],
                    'user' => $_ENV['APP_ENV'] === 'testing' ? '' : $_ENV['DB_USER'],
                    'password' => $_ENV['APP_ENV'] === 'testing' ? '' : $_ENV['DB_PASSWORD'],
                    'charset' => 'utf8mb4'
                ]
            ],
            'storage' => [
                'base_path' => public_path($_ENV['UPLOAD_DIR'])
            ],
            'elasticsearch' => [
                'host' => $_ENV['ELASTICSEARCH_HOST'],
                'api_key' => $_ENV['ELASTICSEARCH_API_KEY']
            ]
        ];
    });
    $container->set(UserRepository::class, fn() => new DoctrineUserRespository($container->get(EntityManager::class)));
    $container->set(DocumentRepository::class, fn() => new DoctrineDocumentRepository(
        $container->get(EntityManager::class),
        $container->get(AuthenticationService::class),
        $container->get(ElasticSearchDocumentRepository::class)
    ));
    $container->set(UserServiceInterface::class, fn() => new UserService(new DoctrineUserRespository($container->get(EntityManager::class)), $container->get(DocumentStorageInterface::class)));
    $container->set(DocumentStorageInterface::class, fn() => new DocumentStorage($container->get('settings')['storage']['base_path']));
    $container->set(AuthenticationService::class, fn() => new DoctrineAuthService($container->get(EntityManager::class), $container->get(UserRepository::class)));
    $container->set(DocumentServiceInterface::class, fn() => new DocumentService($container->get(DocumentRepository::class), $container->get(ElasticSearchDocumentRepository::class)));
    $container->set(DocumentShareRepository::class, fn() => new DoctrineDocumentShareRepository($container->get(EntityManager::class)));
    $container->set(ElasticSearchDocumentRepository::class, fn() => new ElasticSearchDocumentRepository($container->get(Client::class)));
};
