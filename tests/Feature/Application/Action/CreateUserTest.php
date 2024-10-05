<?php

use App\Application\Action\CreateUser;
use App\Application\Service\UserService;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserServiceInterface;

beforeEach(function () {
    $this->userService = Mockery::mock(UserService::class);
    $this->createUserAction = new CreateUser($this->userService);
});

it('successfully creates a user', function () {
    $firstName = 'John';
    $lastName = 'Doe';
    $email = 'john@email.com';
    $password = 'password';

    $user = new User($firstName, $lastName, $email, $password);

    $this->userService->shouldReceive('registerUser')
        ->once()
        ->with(Mockery::on(function ($arg) use ($firstName, $lastName, $email) {
            return $arg instanceof User &&
                $arg->getFirstName() === $firstName &&
                $arg->getLastName() === $lastName &&
                $arg->getEmail() === $email;
        }))
        ->andReturn($user);

    $result = $this->createUserAction->execute($firstName, $lastName, $email, $password);

    expect($result)->toBeInstanceOf(User::class);
});


it('returns null when user registration fails', function () {
    // Arrange
    $firstName = 'Jane';
    $lastName = 'Doe';
    $email = 'jane@email.com';
    $password = 'password';

    $user = new User($firstName, $lastName, $email, $password);

    $this->userService->shouldReceive('registerUser')
        ->once()
        ->with(Mockery::on(function ($arg) use ($firstName, $lastName, $email) {
            return $arg instanceof User &&
                $arg->getFirstName() === $firstName &&
                $arg->getLastName() === $lastName &&
                $arg->getEmail() === $email;
        }))
        ->andThrow(new Exception());

    $result = $this->createUserAction->execute($firstName, $lastName, $email, $password);

    expect($result)->toBeNull();
});
