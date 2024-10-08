<?php

use App\Presentation\Controllers\LoginUserController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Service\AuthenticationService;
use Slim\Psr7\Response as SlimResponse;

it('returns 400 if email or password is missing', function () {
    // Arrange
    $authenticationService = Mockery::mock(AuthenticationService::class);
    $controller = new LoginUserController($authenticationService);
    $request = Mockery::mock(Request::class);
    $response = new SlimResponse();

    $request->shouldReceive('getParsedBody')
        ->andReturn(['email' => 'jane@email.com']); // Password is missing

    $result = $controller->login($request, $response);

    expect($result->getStatusCode())->toBe(400);
    expect($result->getBody()->__toString())->toContain('Invalid or missing data in request.');
});

it('returns 401 if authentication fails', function () {
    $authenticationService = Mockery::mock(AuthenticationService::class);
    $controller = new LoginUserController($authenticationService);
    $request = Mockery::mock(Request::class);
    $response = new SlimResponse();

    $request->shouldReceive('getParsedBody')
        ->andReturn(['email' => 'jane@email.com', 'password' => 'inpassword']);

    $authenticationService->shouldReceive('authenticate')
        ->with('jane@email.com', 'inpassword')
        ->andReturn(null);

    $result = $controller->login($request, $response);

    expect($result->getStatusCode())->toBe(401);
    expect($result->getBody()->__toString())->toContain('Invalid credentials');
});

it('returns 200 and a token on successful login', function () {
    $authenticationService = Mockery::mock(AuthenticationService::class);
    $controller = new LoginUserController($authenticationService);
    $request = Mockery::mock(Request::class);
    $response = new SlimResponse();
    $user = Mockery::mock(\App\Domain\User\Entity\User::class);
    $token = Mockery::mock(\App\Domain\User\ValueObject\Token::class);

    $request->shouldReceive('getParsedBody')
        ->andReturn(['email' => 'jane@email.com', 'password' => 'password']);

    $authenticationService->shouldReceive('authenticate')
        ->with('jane@email.com', 'password')
        ->andReturn($user);

    $authenticationService->shouldReceive('generateToken')
        ->with($user)
        ->andReturn($token);

    $token->shouldReceive('getToken')
        ->andReturn('valid-token');

    // Act
    $result = $controller->login($request, $response);

    // Assert
    expect($result->getStatusCode())->toBe(200);
    expect($result->getBody()->__toString())->toContain('valid-token');
});
