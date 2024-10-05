<?php

use App\Application\Action\CreateDocument;
use App\Application\Service\DocumentService;
use App\Domain\Document\Entity\Document;
use App\Domain\User\Entity\User;
use App\Infrastructure\Storage\DocumentStorage;


use Slim\Psr7\UploadedFile;

beforeEach(function () {
    $this->documentService = Mockery::mock(DocumentService::class);
    $this->documentStorage = Mockery::mock(DocumentStorage::class);
    $this->createDocumentAction = new CreateDocument($this->documentService, $this->documentStorage);
    $this->user = Mockery::mock(User::class);
});

it('can create a document', function () {
    $uploadedFile = Mockery::mock(UploadedFile::class);
    $uploadedFile->shouldReceive('getClientFilename')->andReturn('test document.pdf');
    $uploadedFile->shouldReceive('getClientMediaType')->andReturn('application/pdf');
    $uploadedFile->shouldReceive('getSize')->andReturn(12345);

    $uploadedFiles = ['document' => $uploadedFile];

    $this->user->shouldReceive('getEmail')->andReturn('test@example.com');

    $this->documentStorage->shouldReceive('saveUploadedFile')->once()->with(
        'test_document.pdf',
        $uploadedFile,
        'test@example.com'
    )->andReturn(true);

    $this->documentService->shouldReceive('createDocument')->once()->andReturn(Mockery::mock(Document::class));

    $result = $this->createDocumentAction->execute($uploadedFiles, $this->user);
    expect($result)->toBeTrue();
});



it('throws an exception when document creation fails', function () {
    $uploadedFile = Mockery::mock(UploadedFile::class);
    $uploadedFile->shouldReceive('getClientFilename')->andReturn('test document.pdf');
    $uploadedFile->shouldReceive('getClientMediaType')->andReturn('application/pdf');
    $uploadedFile->shouldReceive('getSize')->andReturn(12345);

    $uploadedFiles = ['document' => $uploadedFile];

    $this->user->shouldReceive('getEmail')->andReturn('test@example.com');

    $this->documentStorage->shouldReceive('saveUploadedFile')->once()->andReturn(true);

    $this->documentService->shouldReceive('createDocument')->once()->andThrow(new Exception('Document creation failed'));

    expect(fn() => $this->createDocumentAction->execute($uploadedFiles, $this->user))
        ->toThrow(Exception::class, 'Document creation failed');
});
