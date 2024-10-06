<?php

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\AuthenticationService;
use App\Infrastructure\Persistence\DoctrineUserRepository;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class)->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Extend the expect() function for custom expectations in your tests.
|
*/

expect()->extend('toBeOne', function () {
    return test()->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Define helper functions to reduce repetitive code across your test files.
|
*/

function createRequest(
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

function jsonDecode(string $response)
{
    return json_decode($response, true);
}

function setAuthorizationHeader(string $token): void
{
    $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";
}

function createJwtToken($userId, $expiration = 3600): string
{
    $payload = [
        'sub' => $userId,
        'iat' => time(),
        'exp' => time() + $expiration,
    ];

    $jwtSecret = $_ENV['JWT_SECRET'];

    return JWT::encode($payload, $jwtSecret, 'HS256');
}
function container()
{
    return test()->container;
}
function authenticatedUser()
{
    // Create and store a user for authentication
    $userRepository = container()->get(UserRepository::class);
    $authService = container()->get(AuthenticationService::class);

    // Store user for the test
    $stored = $userRepository->findByEmail("jane@email.com");
    if ($stored === null) {
        $stored = new User("Jane", "Smith", "jane@email.com", "password");
        $userRepository->store($stored);
    }


    // Generate JWT token for authentication
    $jwt = createJwtToken($stored->getId());
    setAuthorizationHeader($jwt);

    // Retrieve the authenticated user via the service
    return $authService->getUser($jwt);
}
