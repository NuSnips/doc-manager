<?php

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\Document\Repository\DocumentRepository;
use App\Domain\DocumentShare\Entity\DocumentShare;
use App\Domain\DocumentShare\Repository\DocumentShareRepository;
use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\DoctrineDocumentRepository;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Infrastructure\Persistence\ElasticSearchDocumentRepository;
use App\Infrastructure\Service\DoctrineAuthService;
use Doctrine\ORM\EntityManager;

beforeEach(function () {
    $this->entityManager = $this->container->get(EntityManager::class);
    $this->expiresAt = (new DateTime())->modify("+30days");

    $this->documentShareRepository = container()->get(DocumentShareRepository::class);
    $this->documentRepository = container()->get(DocumentRepository::class);

    $this->metadata = new Metadata();
    $this->metadata->setSize("200")->setTags(['tag1', ['tag2']])
        ->setType("text/plain");

    $this->document = new Document(
        "another-file.txt",
        "path/to/another-file.txt",
        $this->metadata,
        authenticatedUser()
    );
    $this->metadata->setDocument($this->document);
    $this->documentRepository->save($this->document);

    $this->documentShare = new DocumentShare($this->document, $this->expiresAt);
    $this->documentShareRepository->save($this->documentShare);
});

it('saves a document share entity', function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);
    $result = $this->documentShareRepository->save($documentShare);

    expect($result)->toBe($documentShare);
});

it('finds a document share by token', function () {
    $documentShare = new DocumentShare($this->document, $this->expiresAt);
    $this->documentShareRepository->save($documentShare);

    $result = $this->documentShareRepository->findByToken($documentShare->getUrl());

    expect($result)->toBe($documentShare);
});

it('returns all document shares', function () {

    $documentShares = $this->documentShareRepository->findAll();

    // Assert
    expect($documentShares)->toHaveCount(1)
        ->and($documentShares[0])->toBe($this->documentShare);
});
