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

it("can create a new document share and access it's properties", function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);
    expect($documentShare->getDocument())->toBe($this->document)
        ->and($documentShare->getStatus())->toBeTrue()
        ->and($documentShare->getUrl())->not()->toBeEmpty()->toBeString()
        ->and($documentShare->getExpiresAt())->toBe($this->expiresAt);
});
it('can generate a unique URL token', function () {

    $documentShare = new DocumentShare($this->document, $this->expiresAt);
    expect($documentShare->getUrl())->toMatch('/[a-f0-9]{32}/');
});

it('can generate a shareable URL', function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);

    // Set a custom prefix
    $customPrefix = "/custom/download/";
    $documentShare->setPrefix($customPrefix);
    $generatedUrl = $documentShare->generateUrl();

    expect($generatedUrl)->toStartWith($customPrefix);
    expect($generatedUrl)->toEndWith($documentShare->getUrl());
});

it('can set and get the status', function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);
    $documentShare->setStatus(false);
    expect($documentShare->getStatus())->toBeFalse();
});

it('can update the expires_at property', function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);

    $newExpiresAt = new DateTime('+14 days');
    $documentShare->setExpiresAt($newExpiresAt);

    expect($documentShare->getExpiresAt())->toBe($newExpiresAt);
});

it('can set and update the document property', function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);

    $newDocument = new Document('Another Document', '/path/to/another.pdf', new Metadata(), new User('Jill', 'Smith', 'jill@example.com', 'password'));
    $documentShare->setDocument($newDocument);

    expect($documentShare->getDocument())->toBe($newDocument);
});

it('can associate document share download', function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);

    $download = new DocumentShareDownload($documentShare);
    $documentShare->setDocumentShareDownload($download);

    expect($documentShare->getDocumentShareDownload())->toBe($download);
});
