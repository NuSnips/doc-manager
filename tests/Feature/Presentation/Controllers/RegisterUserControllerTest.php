<?php

use App\Application\Action\CreateUser;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserServiceInterface;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Presentation\Validation\InputValidator;
use App\Presentation\Controllers\RegisterUserController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

beforeEach(function () {
    $this->createUser = Mockery::mock(CreateUser::class);
    $this->userService = Mockery::mock(UserServiceInterface::class);
    $this->doctrineUserRespository = Mockery::mock(DoctrineUserRespository::class);
    $this->inputValidator = Mockery::mock(InputValidator::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->body = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
    $this->request = Mockery::mock(ServerRequestInterface::class);

    $this->response->shouldReceive('getBody')->andReturn($this->body);

    $this->controller = new RegisterUserController(
        $this->createUser,
        $this->userService,
        $this->doctrineUserRespository
    );
});


it('returns 400 if request data is null or not an array', function () {
    $data = [];

    $this->request->shouldReceive('getParsedBody')->andReturn($data);
    $this->body->shouldReceive('write')->once()
        ->with(json_encode(['success' => false, 'message' => 'Invalid or missing data in request.']));

    $this->response->shouldReceive('withStatus')->with(400)->andReturnSelf();
    $this->response->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();

    $response = $this->controller->register($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});

it('returns 400 if validation fails', function () {
    $data = [
        'first_name' => '',
        'last_name' => 'Smith',
        'email' => 'invalid-email',
        'password' => 'short'
    ];

    $this->request->shouldReceive('getParsedBody')->andReturn($data);

    $this->inputValidator->shouldReceive('validate')->andReturn(false);
    $this->inputValidator->shouldReceive('getErrors')->andReturn(['first_name' => ['The first_name field is required.']]);

    $this->body->shouldReceive('write')->once()
        ->with(json_encode([
            'success' => false,
            'message' => 'Invalid or missing data in request.',
            'errors' => ['first_name' => ['The first_name field is required.']]
        ]));

    $this->response->shouldReceive('withStatus')->with(400)->andReturnSelf();
    $this->response->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();

    $response = $this->controller->register($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});

it('returns 400 if user already exists', function () {
    $data = [
        'first_name' => 'jane',
        'last_name' => 'Smith',
        'email' => 'jane@email.com',
        'password' => 'password'
    ];
    $existingUser = Mockery::mock(User::class);

    $this->request->shouldReceive('getParsedBody')->andReturn($data);
    $this->inputValidator->shouldReceive('validate')->andReturn(true);

    $this->doctrineUserRespository->shouldReceive('findByEmail')->with($data['email'])->andReturn($existingUser);

    $this->body->shouldReceive('write')->once()
        ->with(json_encode(['success' => false, 'message' => 'User already exists.']));

    $this->response->shouldReceive('withStatus')->with(400)->andReturnSelf();
    $this->response->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();

    $response = $this->controller->register($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});

it('returns 400 if there is an error creating the user', function () {
    $data = [
        'first_name' => 'jane',
        'last_name' => 'Smith',
        'email' => 'jane@email.com',
        'password' => 'password'
    ];

    $this->request->shouldReceive('getParsedBody')->andReturn($data);
    $this->inputValidator->shouldReceive('validate')->andReturn(true);
    $this->doctrineUserRespository->shouldReceive('findByEmail')->with($data['email'])->andReturn(null);
    $this->createUser->shouldReceive('execute')->with($data['first_name'], $data['last_name'], $data['email'], $data['password'])->andReturn(null);

    $this->body->shouldReceive('write')->once()
        ->with(json_encode(['success' => false, 'message' => "Error registering user."]));

    $this->response->shouldReceive('withStatus')->with(400)->andReturnSelf();
    $this->response->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();

    $response = $this->controller->register($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});

it('returns 201 if user is successfully registered', function () {
    $data = [
        'first_name' => 'jane',
        'last_name' => 'Smith',
        'email' => 'jane@email.com',
        'password' => 'password'
    ];

    $user = Mockery::mock(User::class);

    $this->request->shouldReceive('getParsedBody')->andReturn($data);
    $this->inputValidator->shouldReceive('validate')->andReturn(true);
    $this->doctrineUserRespository->shouldReceive('findByEmail')->with($data['email'])->andReturn(null);
    $this->createUser->shouldReceive('execute')->with($data['first_name'], $data['last_name'], $data['email'], $data['password'])->andReturn($user);

    $this->body->shouldReceive('write')->once()
        ->with(json_encode(['success' => true, 'message' => 'User registered.']));

    $this->response->shouldReceive('withStatus')->with(201)->andReturnSelf();
    $this->response->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();

    $response = $this->controller->register($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});
