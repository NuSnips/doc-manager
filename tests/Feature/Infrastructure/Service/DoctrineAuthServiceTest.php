<?php

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\AuthenticationService;
use App\Domain\User\ValueObject\Token;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

beforeEach(function () {
    $this->entityManager = container()->get(EntityManager::class);
    $this->userRepository = container()->get(UserRepository::class);
    $this->authenticationService = container()->get(AuthenticationService::class);

    $this->user = new User("Jane", "Smith", "jane@email.com", "password");
    $this->userRepository->save($this->user);
});
it('authenticates a user with valid credentials', function () {
    $user = $this->authenticationService->authenticate("jane@email.com", "password");
    expect($user)->toBe($this->user);
});

it('returns null if credentials are invalid', function () {
    $email = 'invalid@email.com';
    $password = 'password';
    $result = $this->authenticationService->authenticate($email, $password);

    expect($result)->toBeNull();
});

it('generates a valid JWT token for a user', function () {
    $_ENV['JWT_SECRET'] = 'my_jwt_secret';
    $token = $this->authenticationService->generateToken($this->user);
    expect($token)->toBeInstanceOf(\App\Domain\User\ValueObject\Token::class);

    // Decode the token to check its payload
    $decodedToken = JWT::decode($token->getToken(), new Key($_ENV['JWT_SECRET'], 'HS256'));
    expect($decodedToken->email)->toBe($this->user->getEmail());
});


it('validates a valid JWT token and returns a user', function () {
    // Arrange
    $_ENV['JWT_SECRET'] = 'my_jwt_secret';

    $user = Mockery::mock(\App\Domain\User\Entity\User::class);
    $user->shouldReceive('getId')->andReturn(1);

    $userRepository = Mockery::mock(\App\Domain\User\Repository\UserRepository::class);
    $userRepository->shouldReceive('findById')->with(1)->andReturn($user);

    $entityManager = Mockery::mock(\Doctrine\ORM\EntityManager::class);

    $authService = new \App\Infrastructure\Service\DoctrineAuthService($entityManager, $userRepository);

    $token = new \App\Domain\User\ValueObject\Token(JWT::encode([
        'sub' => 1,
        'email' => 'test@example.com',
        'iat' => time(),
        'exp' => time() + 3600,
    ], $_ENV['JWT_SECRET'], 'HS256'));

    // Act
    $result = $authService->validateToken($token);

    // Assert
    expect($result)->toBe($user);
});

it('returns null for an invalid JWT token', function () {
    $_ENV['JWT_SECRET'] = 'my_jwt_secret';

    // Create an invalid token
    $token = new Token('invalid_token');
    $result = $this->authenticationService->validateToken($token);

    expect($result)->toBeNull();
});

it('gets the user from a valid JWT token', function () {
    // Arrange
    $_ENV['JWT_SECRET'] = 'my_jwt_secret';

    $user = Mockery::mock(\App\Domain\User\Entity\User::class);
    $user->shouldReceive('getId')->andReturn(1);

    $userRepository = Mockery::mock(\App\Domain\User\Repository\UserRepository::class);
    $userRepository->shouldReceive('findById')->with(1)->andReturn($user);

    $entityManager = Mockery::mock(\Doctrine\ORM\EntityManager::class);

    $authService = new \App\Infrastructure\Service\DoctrineAuthService($entityManager, $userRepository);

    $jwt = JWT::encode([
        'sub' => 1,
        'email' => 'test@example.com',
        'iat' => time(),
        'exp' => time() + 3600,
    ], $_ENV['JWT_SECRET'], 'HS256');

    $tokenString = 'Bearer ' . $jwt;

    // Act
    $result = $authService->getUser($tokenString);

    // Assert
    expect($result)->toBe($user);
});

it('returns null when the JWT token is invalid', function () {
    $_ENV['JWT_SECRET'] = 'my_jwt_secret';

    // Create an invalid token
    $tokenString = 'Bearer invalid_token';

    $result = $this->authenticationService->getUser($tokenString);

    expect($result)->toBeNull();
});
