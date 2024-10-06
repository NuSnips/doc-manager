<?php

use App\Application\Service\DocumentService;
use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\Document\Repository\DocumentRepository;
use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\ElasticSearchDocumentRepository;

beforeEach(function () {
    $this->documentRepository = Mockery::mock(DocumentRepository::class);
    $this->elasticSearchDocumentRepository = Mockery::mock(ElasticSearchDocumentRepository::class);
    $this->documentService = new DocumentService($this->documentRepository, $this->elasticSearchDocumentRepository);
});
it('can create a document and return it', function () {

    $data = [
        'name' => 'doc1',
        'path' => '/path/to/doc1',
        'size' => "1234",
        'type' => 'application/pdf',
        'tags' => ['tag1', 'tag2'],
        'user' => new User("Jane", "Smith", "jane@email.com", "password"),

    ];

    $document = new Document($data['name'], $data['path'], new Metadata(), $data['user']);

    $this->documentRepository
        ->shouldReceive('save')
        ->once()
        ->with(Mockery::on(function ($arg) use ($document) {
            return $arg instanceof Document && $arg->getName() === $document->getName();
        }))
        ->andReturn($document);

    $result = $this->documentService->createDocument($data);
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

    $this->documentRepository->shouldReceive('findAll')->once()->andReturn($documents);


    $result = $this->documentService->getDocuments();
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

    $this->documentRepository->shouldReceive('findById')->with($documentId)->once()->andReturn($document);


    $result = $this->documentService->getDocument($documentId);
    expect($result)->toEqual($document);
});

it('returns null when document is not found', function () {
    $documentId = 1;
    $this->documentRepository->shouldReceive('findById')->with($documentId)->once()->andReturn(null);


    $result = $this->documentService->getDocument($documentId);
    expect($result)->toBeNull();
});

it('deletes a document by id', function () {
    $documentId = 1;
    $this->documentRepository->shouldReceive('delete')->with($documentId)->once();


    expect(function () use ($documentId) {
        $this->documentService->deleteDocument($documentId);
    })->not()->toThrow(Exception::class);
});

it('will throw an exception if document deletion fails', function () {
    $documentId = 1;

    $this->documentRepository->shouldReceive('delete')->with($documentId)->andThrow(new \Exception('Deletion failed'));

    expect(fn() => $this->documentService->deleteDocument($documentId))->toThrow(\Exception::class, 'Deletion failed');
});

it('returns documents matching the search term', function () {
    // Arrange
    $searchTerm = 'another';
    $documents = [
        new Document('file.txt', '/path/to/file.txt', new Metadata(), new User("Jane", "Smith", "jane@email.com", "password"),),
        new Document('another-file.txt', '/path/to/another-file.txt', new Metadata(), new User("Jack", "Smith", "jack@email.com", "password"),),
    ];

    $this->elasticSearchDocumentRepository->shouldReceive('search')->with($searchTerm)->once()->andReturn([1, 2]);
    $this->documentRepository->shouldReceive('findByIds')->with([1, 2])->once()->andReturn([$documents[0], $documents[1]]);
    // $this->documentRepository->shouldReceive('search')->with($searchTerm)->once()->andReturn($documents);
    $result = $this->documentService->search($searchTerm);

    expect($result)->toEqual($documents);
});
