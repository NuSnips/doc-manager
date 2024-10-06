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
    $this->documentShare = new DocumentShare($this->document, $this->expiresAt);
});


it('can create a document share download and access its properties', function () {
    $documentShareDownload = new DocumentShareDownload($this->documentShare);
    expect($documentShareDownload->getDocumentShare())->toBe($this->documentShare)
        ->and($documentShareDownload->getCount())->toBe(0);
});

it('can increment the download count', function () {
    $documentShareDownload = new DocumentShareDownload($this->documentShare);
    // Increment by 1
    $documentShareDownload->incrementCount();
    expect($documentShareDownload->getCount())->toEqual(1);

    // Increment by 1
    $documentShareDownload->incrementCount();
    expect($documentShareDownload->getCount())->toEqual(2);
});

it('can set and get the document share', function () {
    $documentShareDownload = new DocumentShareDownload($this->documentShare);

    $newDocumentShare = new DocumentShare(new Document(
        'Another Document',
        '/path/to/another.pdf',
        $this->metadata,
        $this->user
    ), $this->expiresAt);
    $documentShareDownload->setDocumentShare($newDocumentShare);

    expect($documentShareDownload->getDocumentShare())->toBe($newDocumentShare);
});
