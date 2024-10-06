<?php

use App\Domain\User\ValueObject\Token;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\DoctrineAuthService;
use App\Infrastructure\Middleware\AuthMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\Psr7\Response as Psr7Response;

// Before each test, we'll mock the dependencies and set up the middleware.
beforeEach(function () {
    $this->doctrineAuthService = Mockery::mock(DoctrineAuthService::class);
    $this->handler = Mockery::mock(RequestHandler::class);
    $this->middleware = new AuthMiddleware($this->doctrineAuthService);
});

it('can process the request with a valid token', function () {
    // Arrange
    $tokenString = 'validToken123';
    $user = Mockery::mock(User::class);

    $request = Mockery::mock(Request::class);
    $request->shouldReceive('getHeaderLine')
        ->with('Authorization')
        ->andReturn('Bearer ' . $tokenString);

    $this->doctrineAuthService->shouldReceive('validateToken')
        ->once()
        ->with(Mockery::on(fn($arg) => $arg->getToken() === $tokenString))
        ->andReturn($user);

    $request->shouldReceive('withAttribute')
        ->with('user', $user)
        ->andReturnSelf();

    $this->handler->shouldReceive('handle')
        ->once()
        ->with($request)
        ->andReturn(new Psr7Response());

    $response = $this->middleware->process($request, $this->handler);

    expect($response->getStatusCode())->toBe(200);
});


it('returns 401 Unauthorized when the token is missing', function () {
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('getHeaderLine')->with('Authorization')->andReturn('');

    $response = $this->middleware->process($request, $this->handler);

    expect($response->getStatusCode())->toBe(401);
    expect((string)$response->getBody())->toContain('Unauthorized');
});

it('returns 401 Unauthorized for an invalid token', function () {
    $invalidToken = 'invalidToken123';

    $request = Mockery::mock(Request::class);
    $request->shouldReceive('getHeaderLine')
        ->with('Authorization')
        ->andReturn('Bearer ' . $invalidToken);

    $this->doctrineAuthService->shouldReceive('validateToken')
        ->once()
        ->with(Mockery::on(fn($arg) => $arg->getToken() === $invalidToken))
        ->andReturn(null);

    $response = $this->middleware->process($request, $this->handler);

    expect($response->getStatusCode())->toBe(401);
    expect((string)$response->getBody())->toContain('Invalid token');
});
