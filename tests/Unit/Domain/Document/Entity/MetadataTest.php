<?php

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\User\Entity\User;

beforeEach(function () {
    $this->user = new User("Jane", "Smith", "jane@email.com", "password");

    $this->document = new Document("another-file.txt", "path/to/another-file.txt", new Metadata(), $this->user);
});

it("can create Metadata and access it's properties", function () {
    $metadata = (new Metadata())->setDocument($this->document)
        ->setSize("300")->setTags(['tag1', 'tag2'])
        ->setType("text/plain");
    expect($metadata->getDocument())->toBe($this->document)
        ->and($metadata->getSize())->toBe("300")
        ->and($metadata->getTags())->toBe(['tag1', 'tag2'])
        ->and($metadata->getType())->toBe("text/plain");
});

it('can set and get the document', function () {
    $metadata = new Metadata();
    $metadata->setDocument($this->document);
    expect($metadata->getDocument())->toBe($this->document);
});

it('can set and get the size and type', function () {
    $metadata = new Metadata();
    $metadata->setSize("300")->setType("text/plain");
    expect($metadata->getSize())->toBe("300")
        ->and($metadata->getType())->toBe("text/plain");
});

it('can get and set tags', function () {
    $metadata = new Metadata();
    $metadata->setTags(['tag1', 'tag2']);
    expect($metadata->getTags())->toBe(['tag1', 'tag2']);
});
