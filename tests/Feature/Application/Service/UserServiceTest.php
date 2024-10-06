<?php

use App\Application\Service\UserService;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Infrastructure\Storage\DocumentStorage;

it('can register a new user and creates a storage folder', function () {
    $user = Mockery::mock(\App\Domain\User\Entity\User::class);
    $user->shouldReceive('getEmail')->andReturn('test@example.com');

    $doctrineUserRespository = Mockery::mock(DoctrineUserRespository::class);
    $doctrineUserRespository->shouldReceive('save')->with($user)->once();

    $documentStorage = Mockery::mock(DocumentStorage::class);
    $documentStorage->shouldReceive('createFolder')->with('test@example.com')->once();

    $userService = new UserService($doctrineUserRespository, $documentStorage);

    $result = $userService->registerUser($user);
    expect($result)->toBe($user);
});


it('throws an exception if user registration fails', function () {
    // Arrange
    $user = Mockery::mock(\App\Domain\User\Entity\User::class);
    $user->shouldReceive('getEmail')->andReturn('test@example.com');

    $doctrineUserRespository = Mockery::mock(DoctrineUserRespository::class);
    $doctrineUserRespository->shouldReceive('save')->with($user)->andThrow(new Exception('Registration failed'));

    $documentStorage = Mockery::mock(DocumentStorage::class);

    $userService = new UserService($doctrineUserRespository, $documentStorage);

    expect(fn() => $userService->registerUser($user))->toThrow(Exception::class, 'Registration failed');
});

it('finds a user by email', function () {
    // Arrange
    $email = 'test@example.com';
    $user = Mockery::mock(\App\Domain\User\Entity\User::class);

    $doctrineUserRespository = Mockery::mock(DoctrineUserRespository::class);
    $doctrineUserRespository->shouldReceive('findByEmail')->with($email)->andReturn($user);

    $documentStorage = Mockery::mock(DocumentStorage::class);

    $userService = new UserService($doctrineUserRespository, $documentStorage);

    $result = $userService->findUserByEmail($email);
    expect($result)->toBe($user);
});

it('returns null if user is not found by email', function () {
    $email = 'notfound@example.com';

    $doctrineUserRespository = Mockery::mock(DoctrineUserRespository::class);
    $doctrineUserRespository->shouldReceive('findByEmail')->with($email)->andReturn(null);

    $documentStorage = Mockery::mock(DocumentStorage::class);

    $userService = new UserService($doctrineUserRespository, $documentStorage);

    $result = $userService->findUserByEmail($email);

    expect($result)->toBeNull();
});
