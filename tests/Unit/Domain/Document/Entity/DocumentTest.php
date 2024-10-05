<?php

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\DocumentShare\Entity\DocumentShare;
use App\Domain\User\Entity\User;

beforeEach(function () {
    $this->user = new User("Jane", "Smith", "jane@email.com", "password");
    $this->expiresAt = (new DateTime())->modify("+30days");
    $this->metadata = new Metadata();
    $this->metadata->setSize("200")->setTags(['tag1', ['tag2']])
        ->setType("text/plain");

    $this->document = new Document("another-file.txt", "path/to/another-file.txt", $this->metadata, $this->user);
});

it(
    "can create a Document entity and access it's properties",
    function () {
        $document = new Document("file.txt", "path/to/file.txt", $this->metadata, $this->user);
        expect($document->getUser())->toBe($this->user)
            ->and($document->getPath())->toBe("path/to/file.txt")
            ->and($document->getMetadata())->toBe($this->metadata);
    }
);

it('can set and get the document name and path', function () {
    $this->document->setName("new-file.txt")->setPath("new/path/to/new-file.txt");
    expect($this->document->getName())->toBe("new-file.txt")
        ->and($this->document->getPath())->toBe("new/path/to/new-file.txt");
});

it('can set and get metadata', function () {
    $newMetadata = (new Metadata())->setSize("100")->setTags(['tag3', ['tag4']])
        ->setType("text/plain")->setDocument($this->document);
    $this->document->setMetadata($newMetadata);

    expect($this->document->getMetadata())->toBe($newMetadata);
});

it('can add and retrieve document shares', function () {
    $documentShare = new DocumentShare($this->document,  $this->expiresAt);
    $this->document->addDocumentShare($documentShare);

    expect($this->document->getDocumentShares())->toHaveCount(1)
        ->and($this->document->getDocumentShares()[0])->toBe($documentShare);
});

it('can add multiple document shares', function () {
    $newDocument = new Document("another-new-file.txt", "path/to/another-new-file.txt", $this->metadata, $this->user);
    $documentShare1 = new DocumentShare($this->document,  $this->expiresAt);
    $documentShare2 = new DocumentShare($newDocument,  $this->expiresAt);
    $this->document->addDocumentShare($documentShare1);
    $this->document->addDocumentShare($documentShare2);

    expect($this->document->getDocumentShares())->toHaveCount(2)
        ->and($this->document->getDocumentShares()[0])->toBe($documentShare1)
        ->and($this->document->getDocumentShares()[1])->toBe($documentShare2);
});

it('verifies duplicate document share are not inserted', function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);
    
    $this->document->addDocumentShare($documentShare);
    $this->document->addDocumentShare($documentShare);

    expect($this->document->getDocumentShares())->toHaveCount(1);
});
