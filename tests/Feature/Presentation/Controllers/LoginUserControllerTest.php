<?php

use App\Domain\User\Entity\User;
use App\Domain\User\Service\AuthenticationService;
use App\Domain\User\ValueObject\Token;
use App\Presentation\Controllers\LoginUserController;
use App\Presentation\Validation\InputValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

beforeEach(function () {
    $this->authenticationService = Mockery::mock(AuthenticationService::class);
    $this->inputValidator = Mockery::mock(InputValidator::class);
    $this->controller = new LoginUserController($this->authenticationService);

    $this->request = Mockery::mock(ServerRequestInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
});

it('returns 400 if no data is provided', function () {
    $this->request->shouldReceive('getParsedBody')->andReturn(null);

    $this->response->shouldReceive('getBody->write')
        ->once()
        ->with(json_encode(['success' => false, 'message' => 'Invalid or missing data in request.']));
    $this->response->shouldReceive('withStatus')->andReturnSelf();
    $this->response->shouldReceive('withHeader')->andReturnSelf();

    $response = $this->controller->login($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});

it('validates request data and returns 400 if validation fails', function () {
    $data = ['email' => '', 'password' => ''];
    $this->request->shouldReceive('getParsedBody')->andReturn($data);

    $this->inputValidator->shouldReceive('validate')
        ->with(['email' => '', 'password' => ''], [
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true]
        ])
        ->andReturn(false);

    $this->inputValidator->shouldReceive('getErrors')
        ->andReturn([
            'email' => ['The email field is required.'],
            'password' => ['The password field is required.']
        ]);

    $this->response->shouldReceive('getBody->write')
        ->once()
        ->with(json_encode([
            'success' => false,
            'message' => 'Invalid or missing data in request.',
            'errors' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ]
        ]));
    $this->response->shouldReceive('withStatus')->andReturnSelf();
    $this->response->shouldReceive('withHeader')->andReturnSelf();

    $response = $this->controller->login($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});

it('returns 401 if authentication fails', function () {
    $data = ['email' => 'jane@email.com', 'password' => 'inpassword'];
    $this->request->shouldReceive('getParsedBody')->andReturn($data);

    $this->inputValidator->shouldReceive('validate')
        ->andReturn(true);

    $this->authenticationService->shouldReceive('authenticate')
        ->with('jane@email.com', 'inpassword')
        ->andReturn(null);

    $this->response->shouldReceive('getBody->write')
        ->once()
        ->with(json_encode(['success' => 'false', 'message' => 'Invalid credentials']));
    $this->response->shouldReceive('withStatus')->andReturnSelf();
    $this->response->shouldReceive('withHeader')->andReturnSelf();

    $response = $this->controller->login($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});

it('returns 200 and token if authentication is successful', function () {
    $data = ['email' => 'jane@email.com', 'password' => 'password'];
    $user = Mockery::mock(User::class);
    $token = Mockery::mock(Token::class);

    $this->request->shouldReceive('getParsedBody')->andReturn($data);

    $this->inputValidator->shouldReceive('validate')
        ->andReturn(true);

    $this->authenticationService->shouldReceive('authenticate')
        ->with('jane@email.com', 'password')
        ->andReturn($user);

    $this->authenticationService->shouldReceive('generateToken')
        ->with($user)
        ->andReturn($token);
    $token->shouldReceive('getToken')
        ->andReturn('fake-jwt-token');

    $this->response->shouldReceive('getBody->write')
        ->once()
        ->with(json_encode(['success' => 'true', 'token' => 'fake-jwt-token']));
    $this->response->shouldReceive('withStatus')->andReturnSelf();
    $this->response->shouldReceive('withHeader')->andReturnSelf();

    $response = $this->controller->login($this->request, $this->response, $this->inputValidator);

    expect($response)->toBe($this->response);
});
