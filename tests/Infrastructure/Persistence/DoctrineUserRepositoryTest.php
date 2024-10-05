<?php

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\DoctrineDocumentRepository;
use App\Domain\User\Service\AuthenticationService;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Infrastructure\Persistence\ElasticSearchDocumentRepository;
use Doctrine\ORM\EntityManager;

beforeEach(function () {
    $this->entityManager = $this->container->get(EntityManager::class);
    $this->userRepository = new DoctrineUserRespository($this->entityManager);
});


it('stores a user', function () {
    $user = new User('John', 'Doe', 'john@email.com', 'password');

    $this->userRepository->store($user);

    $storedUser = $this->userRepository->findByEmail('john@email.com');
    expect($storedUser)->not()->toBeNull()
        ->and($storedUser->getEmail())->toBe('john@email.com');
});

it('can find a user by ID', function () {
    $user = new User('Jane', 'Doe', 'jane.doe@example.com', 'password');

    $this->userRepository->store($user);
    $storedUser = $this->userRepository->findByEmail('jane.doe@example.com');

    // Find the user by ID
    $foundUser = $this->userRepository->findById($storedUser->getId());
    expect($foundUser)->not()->toBeNull()
        ->and($foundUser->getId())->toBe($storedUser->getId());
});

it('can find a user by email', function () {
    $user = new User('Jane', 'Smith', 'jane@email.com', 'password');
    $this->userRepository->store($user);

    // Find user by email
    $foundUser = $this->userRepository->findByEmail('jane@email.com');
    expect($foundUser)->not()->toBeNull()->and($foundUser->getEmail())->toBe('jane@email.com');
});

it('returns null when finding a user by non-existing ID', function () {
    $foundUser = $this->userRepository->findById(999); // Assuming this ID does not exist
    expect($foundUser)->toBeNull();
});

it('returns null when finding a user by non-existing email', function () {
    $foundUser = $this->userRepository->findByEmail('non.existing@email.com');
    expect($foundUser)->toBeNull();
});
