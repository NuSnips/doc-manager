<?php

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\DocumentShare\Entity\DocumentShare;
use App\Domain\DocumentShare\Entity\DocumentShareDownload;
use App\Domain\User\Entity\User;

beforeEach(function () {
    $this->user = new User("Jane", "Smith", "jane@email.com", "password");
    $this->expiresAt = (new DateTime())->modify("+30days");
    $this->metadata = new Metadata();
    $this->metadata->setSize("200")->setTags(['tag1', ['tag2']])
        ->setType("text/plain");

    $this->document = new Document("another-file.txt", "path/to/another-file.txt", $this->metadata, $this->user);
});

it('can create a user and access its properties', function () {
    $user = new User('John', 'Doe', 'john@email.com', 'password');

    expect($user->getFirstName())->toBe('John')
        ->and($user->getLastName())->toBe("Doe")
        ->and($user->getEmail())->toBe("john@email.com")
        ->and(password_verify("password", $user->getPasswordHash()))->toBeTrue();
});

it('can set and get user first name, last name, and email', function () {
    $user = new User('John', 'Doe', 'john@email.com', 'password');

    $user->setFirstName('Jane');
    $user->setLastName('Smith');
    $user->setEmail('jane@email.com');
    $user->setPasswordHash('password123');

    expect($user->getFirstName())->toBe('Jane')
        ->and($user->getLastName())->toBe("Smith")
        ->and($user->getEmail())->toBe("jane@email.com")
        ->and(password_verify("password123", $user->getPasswordHash()))->toBeTrue();
});

it('can add and get documents associated with the user', function () {
    $user = new User('John', 'Doe', 'john@email.com', 'password');

    // Add document to user
    $user->addDocument($this->document);

    expect($user->getDocuments()->count())->toBe(1)
        ->and($user->getDocuments()[0])->toBe($this->document);
});


it('hashes the password correctly', function () {
    $user = new User('John', 'Doe', 'john@email.com', 'password');
    $hashedPassword = $user->getPasswordHash();

    expect(password_verify('password', $hashedPassword))->toBeTrue();
});
