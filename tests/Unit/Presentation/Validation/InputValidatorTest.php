<?php

use App\Presentation\Validation\InputValidator;

beforeEach(function () {
    $this->validator = new InputValidator();
});

it('can validate required fields', function () {
    $data = ['name' => ''];
    $rules = ['name' => ['required' => true]];

    $result = $this->validator->validate($data, $rules);

    expect($result)->toBeFalse();
    expect($this->validator->getErrors())->toHaveKey('name')
        ->and($this->validator->getErrors()['name'][0])->toBe('The name field is required.');
});

it('can validate the correct email format', function () {
    $data = ['email' => 'invalid-email'];
    $rules = ['email' => ['email' => true]];

    $result = $this->validator->validate($data, $rules);

    expect($result)->toBeFalse();
    expect($this->validator->getErrors())->toHaveKey('email')
        ->and($this->validator->getErrors()['email'][0])->toBe('The email must be a valid email address.');
});

it('can validate numeric fields', function () {
    $data = ['age' => 'not-a-number'];
    $rules = ['age' => ['numeric' => true]];

    $result = $this->validator->validate($data, $rules);

    expect($result)->toBeFalse();
    expect($this->validator->getErrors())->toHaveKey('age')
        ->and($this->validator->getErrors()['age'][0])->toBe('The age must be a number.');
});

it('can validate array fields', function () {
    $data = ['tags' => 'not-an-array'];
    $rules = ['tags' => ['array' => true]];

    $result = $this->validator->validate($data, $rules);

    expect($result)->toBeFalse();
    expect($this->validator->getErrors())->toHaveKey('tags')
        ->and($this->validator->getErrors()['tags'][0])->toBe('The tags must be an array.');
});

it('can validate minimum string length', function () {
    $data = ['password' => '123'];
    $rules = ['password' => ['min' => 6]];

    $result = $this->validator->validate($data, $rules);

    expect($result)->toBeFalse();
    expect($this->validator->getErrors())->toHaveKey('password')
        ->and($this->validator->getErrors()['password'][0])->toBe('The password must be at least 6 characters.');
});

it('can validate maximum string length', function () {
    $data = ['username' => 'a-very-long-username'];
    $rules = ['username' => ['max' => 10]];

    $result = $this->validator->validate($data, $rules);

    expect($result)->toBeFalse();
    expect($this->validator->getErrors())->toHaveKey('username')
        ->and($this->validator->getErrors()['username'][0])->toBe('The username may not be greater than 10 characters.');
});

it('will pass validation when all rules are satisfied', function () {
    $data = [
        'name' => 'John',
        'email' => 'john@example.com',
        'age' => 25,
        'tags' => ['tag1', 'tag2'],
        'password' => 'securepassword',
        'username' => 'john123'
    ];

    $rules = [
        'name' => ['required' => true],
        'email' => ['email' => true],
        'age' => ['numeric' => true],
        'tags' => ['array' => true],
        'password' => ['min' => 6],
        'username' => ['max' => 15]
    ];

    $result = $this->validator->validate($data, $rules);

    expect($result)->toBeTrue();
    expect($this->validator->getErrors())->toBeEmpty();
});
