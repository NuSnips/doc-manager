<?php

use App\Application\Action\DeleteDocument;
use App\Application\Service\DocumentService;
use App\Domain\Document\Entity\Document;
use App\Infrastructure\Storage\DocumentStorage;

beforeEach(function () {
    $this->documentService = Mockery::mock(DocumentService::class);
    $this->documentStorage = Mockery::mock(DocumentStorage::class);


    // Optionally, if your DI container or test suite can use real instances of DocumentStorage
    $this->deleteDocumentAction = new DeleteDocument($this->documentService, $this->documentStorage);
});

it('successfully deletes a document', function () {
    // Arrange
    $documentId = 1;
    $documentName = 'test_document.pdf';
    $documentPath = 'user@example.com' . DIRECTORY_SEPARATOR . $documentName;

    $document = Mockery::mock(Document::class);
    $document->shouldReceive('getId')->andReturn($documentId);
    $document->shouldReceive('getName')->andReturn($documentName);
    $document->shouldReceive('getPath')->andReturn($documentPath);

    // Mock the methods to delete document and file
    $this->documentService->shouldReceive('getDocument')
        ->once()
        ->with($documentId)
        ->andReturn($document);

    $this->documentService->shouldReceive('deleteDocument')
        ->once()
        ->with($documentId);

    $this->documentStorage->shouldReceive('deleteFile')
        ->once()
        ->with($documentName, 'user@example.com');

    $this->deleteDocumentAction->execute($document);
});

it('throws an exception when unable to delete the document', function () {
    // Arrange
    $documentId = 1;
    $documentName = 'test_document.pdf';
    $documentPath = 'user@example.com' . DIRECTORY_SEPARATOR . $documentName;

    $document = Mockery::mock(Document::class);
    $document->shouldReceive('getId')->andReturn($documentId);
    $document->shouldReceive('getName')->andReturn($documentName);
    $document->shouldReceive('getPath')->andReturn($documentPath);

    // Mock the getDocument method to return a document
    $this->documentService->shouldReceive('getDocument')
        ->once()
        ->with($documentId)
        ->andReturn($document);

    $this->documentService->shouldReceive('deleteDocument')
        ->once()
        ->with($documentId)
        ->andThrow(new Exception());

    expect(fn() => $this->deleteDocumentAction->execute($document))
        ->toThrow(Exception::class, 'Error deleting document: Error deleting document from DB');
});
