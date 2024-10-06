<?php

use App\Application\Service\DocumentService;
use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\Document\Repository\DocumentRepository;
use App\Domain\User\Entity\User;


it('can create a document and return it', function () {
    $documentRepository = Mockery::mock(DocumentRepository::class);
    $documentService = new DocumentService($documentRepository);
    $data = [
        'name' => 'doc1',
        'path' => '/path/to/doc1',
        'size' => "1234",
        'type' => 'application/pdf',
        'tags' => ['tag1', 'tag2'],
        'user' => new User("Jane", "Smith", "jane@email.com", "password"),

    ];

    $document = new Document($data['name'], $data['path'], new Metadata(), $data['user']);

    $documentRepository
        ->shouldReceive('save')
        ->once()
        ->with(Mockery::on(function ($arg) use ($document) {
            return $arg instanceof Document && $arg->getName() === $document->getName();
        }))
        ->andReturn($document);

    $result = $documentService->createDocument($data);
    expect($result)->toEqual($document);
});
it('gets all documents', function () {
    $documents = [
        new Document(
            'another-file.txt',
            '/path/to/another-file.txt',
            new Metadata(),
            new User("Jane", "Smith", "jane@email.com", "password")
        )
    ];

    $documentRepository = Mockery::mock(DocumentRepository::class);
    $documentRepository->shouldReceive('findAll')->once()->andReturn($documents);

    $documentService = new DocumentService($documentRepository);

    $result = $documentService->getDocuments();
    expect($result)->toEqual($documents);
});

it('gets a document by id', function () {
    $documentId = 1;
    $document = new Document(
        'another-file.txt',
        '/path/to/another-file.txt',
        new Metadata(),
        new User("Jane", "Smith", "jane@email.com", "password")
    );

    $documentRepository = Mockery::mock(DocumentRepository::class);
    $documentRepository->shouldReceive('findById')->with($documentId)->once()->andReturn($document);

    $documentService = new DocumentService($documentRepository);

    $result = $documentService->getDocument($documentId);
    expect($result)->toEqual($document);
});

it('returns null when document is not found', function () {
    $documentId = 1;
    $documentRepository = Mockery::mock(DocumentRepository::class);
    $documentRepository->shouldReceive('findById')->with($documentId)->once()->andReturn(null);

    $documentService = new DocumentService($documentRepository);

    $result = $documentService->getDocument($documentId);
    expect($result)->toBeNull();
});

it('deletes a document by id', function () {
    $documentId = 1;
    $documentRepository = Mockery::mock(DocumentRepository::class);
    $documentRepository->shouldReceive('delete')->with($documentId)->once();

    $documentService = new DocumentService($documentRepository);

    expect(function () use ($documentService, $documentId) {
        $documentService->deleteDocument($documentId);
    })->not()->toThrow(Exception::class);
});

it('will throw an exception if document deletion fails', function () {
    $documentId = 1;

    $documentRepository = Mockery::mock(DocumentRepository::class);
    $documentRepository->shouldReceive('delete')->with($documentId)->andThrow(new \Exception('Deletion failed'));

    $documentService = new DocumentService($documentRepository);

    expect(fn() => $documentService->deleteDocument($documentId))->toThrow(\Exception::class, 'Deletion failed');
});

it('can search for documents', function () {
    // Arrange
    $searchTerm = 'another';
    $documents = [
        new Document('file.txt', '/path/to/file.txt', new Metadata(), new User("Jane", "Smith", "jane@email.com", "password"),),
        new Document('another-file.txt', '/path/to/another-file.txt', new Metadata(), new User("Jack", "Smith", "jack@email.com", "password"),),
    ];

    $documentRepository = Mockery::mock(DocumentRepository::class);
    $documentRepository->shouldReceive('search')->with($searchTerm)->once()->andReturn($documents);

    $documentService = new DocumentService($documentRepository);

    // Act
    $result = $documentService->search($searchTerm);

    // Assert
    expect($result)->toEqual($documents);
});
