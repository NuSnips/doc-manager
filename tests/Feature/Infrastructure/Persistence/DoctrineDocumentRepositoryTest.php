<?php

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\DoctrineDocumentRepository;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Infrastructure\Persistence\ElasticSearchDocumentRepository;
use App\Infrastructure\Service\DoctrineAuthService;
use Doctrine\ORM\EntityManager;

beforeEach(function () {
    $this->entityManager = $this->container->get(EntityManager::class);
    $this->elasticSearchDocumentRepository = $this->container->get(ElasticSearchDocumentRepository::class);
    $this->authenticationService = $this->container->get(DoctrineAuthService::class);
    $this->documentRepository = new DoctrineDocumentRepository(
        $this->entityManager,
        $this->authenticationService,
        $this->elasticSearchDocumentRepository
    );

    $this->metadata = new Metadata();
    $this->metadata->setSize("200")->setTags(['tag1', ['tag2']])
        ->setType("text/plain");

    // Create a document by another user
    $this->otherUserDocument = new Document(
        "file.txt",
        "path/to/file.txt",
        $this->metadata,
        new User("Ben", "Smith", "another-user@example.com", "password")
    );
    $this->metadata->setDocument($this->otherUserDocument);
    $this->documentRepository->save($this->otherUserDocument);

    $this->document = new Document(
        "another-file.txt",
        "path/to/another-file.txt",
        $this->metadata,
        authenticatedUser()
    );
    $this->metadata->setDocument($this->document);
    $this->documentRepository->save($this->document);
});

it('can store a document', function () {
    $newDocument = new Document(
        "another-file.txt",
        "path/to/another-file.txt",
        $this->metadata,
        authenticatedUser()
    );
    $newDocument->setMetadata($this->metadata);
    $document = $this->documentRepository->save($newDocument);

    $stored = $this->documentRepository->findById($document->getId());
    expect($stored)->not->toBeNull()->and($stored)->toBe($document);
});

it('finds all documents for the authenticated user', function () {
    $documents = $this->documentRepository->findAll();
    expect($documents)->toBeArray()->toHaveCount(1);
});

it('finds document by id for the authenticated user', function () {
    $newDocument = new Document(
        "another-file.txt",
        "path/to/another-file.txt",
        $this->metadata,
        authenticatedUser()
    );
    $newDocument->setMetadata($this->metadata);
    $this->documentRepository->save($newDocument);

    $document = $this->documentRepository->findById($newDocument->getId());
    expect($document)->not->toBeNull();
});

it('deletes document for the authenticated user', function () {
    $newDocument = new Document(
        "another-file.txt",
        "path/to/another-file.txt",
        $this->metadata,
        authenticatedUser()
    );
    $newDocument->setMetadata($this->metadata);
    $this->documentRepository->save($newDocument);
    $documents = $this->documentRepository->findAll();

    expect($documents)->toHaveCount(2);
    // Delete document
    $this->documentRepository->delete($documents[0]->getId());

    $documents = $this->documentRepository->findAll();
    expect($documents)->toHaveCount(1);
});

it('can search retrieve documents by part of its title', function () {
    $newDocument = new Document(
        "this-is-unique.txt",
        "path/to/this-is-unique.txt",
        $this->metadata,
        authenticatedUser()
    );
    $newDocument->setMetadata($this->metadata);
    $this->documentRepository->save($newDocument);
    $newDocument = new Document(
        "another-file-like-this.txt",
        "path/to/another-file-like-this.txt",
        $this->metadata,
        authenticatedUser()
    );
    $newDocument->setMetadata($this->metadata);
    $this->documentRepository->save($newDocument);

    $documents = $this->documentRepository->search("another-file");
    expect($documents)->toHaveCount(2);
});
