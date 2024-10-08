<?php


use App\Application\Action\CreateUser;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserServiceInterface;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Presentation\Controllers\RegisterUserController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;

beforeEach(function () {
    $this->createUser = Mockery::mock(CreateUser::class);
    $this->userService = Mockery::mock(UserServiceInterface::class);
    $this->doctrineUserRepository = Mockery::mock(DoctrineUserRespository::class);

    $this->controller = new RegisterUserController(
        $this->createUser,
        $this->userService,
        $this->doctrineUserRepository
    );

    $this->responseFactory = new ResponseFactory();
    $this->response = $this->responseFactory->createResponse();
});

afterEach(function () {
    Mockery::close();
});

it('returns 400 if request data is invalid', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn(['first_name' => '']);

    $response = $this->controller->register($request, $this->response);

    expect((string)$response->getBody())->toContain('Invalid or missing data in request.');
    expect($response->getStatusCode())->toBe(400);
});

it('returns 400 if email already exists', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@email.com',
        'password' => 'password',
    ]);

    $mockUser = Mockery::mock(User::class);


    $this->doctrineUserRepository->shouldReceive('findByEmail')
        ->with('john@email.com')
        ->andReturn($mockUser);

    $response = $this->controller->register($request, $this->response);

    expect((string)$response->getBody())->toContain('User already exists.');
    expect($response->getStatusCode())->toBe(400);
});

it('returns 400 if there is an error creating the user', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@email.com',
        'password' => 'password',
    ]);

    $this->doctrineUserRepository->shouldReceive('findByEmail')->with('john@email.com')->andReturn(null);

    $this->createUser->shouldReceive('execute')->andReturn(null);

    $response = $this->controller->register($request, $this->response);

    expect((string)$response->getBody())->toContain('Error registering user.');
    expect($response->getStatusCode())->toBe(400);
});

it('successfully registers a user', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getParsedBody')->andReturn([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@email.com',
        'password' => 'password',
    ]);

    $this->doctrineUserRepository->shouldReceive('findByEmail')->with('john@email.com')->andReturn(null);

    $mockUser = Mockery::mock(User::class);
    $this->createUser->shouldReceive('execute')->andReturn($mockUser);

    $response = $this->controller->register($request, $this->response);

    expect((string)$response->getBody())->toContain('User registered.');
    expect($response->getStatusCode())->toBe(201);
});
