<?php

use App\Application\Action\CreateDocument;
use App\Application\Service\DocumentService;
use App\Domain\Document\Entity\Document;
use App\Domain\Document\Service\DocumentServiceInterface;
use App\Domain\Document\Storage\DocumentStorageInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\Storage\DocumentStorage;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

beforeEach(function () {
    $this->documentService = Mockery::mock(DocumentService::class);
    $this->documentStorage = Mockery::mock(DocumentStorageInterface::class);
    $this->createDocumentAction = new CreateDocument($this->documentService, $this->documentStorage);
    $this->user = Mockery::mock(User::class);
});

it('can create a document', function () {
    $uploadedFile = Mockery::mock(UploadedFileInterface::class);
    $this->documentService = Mockery::mock(DocumentService::class);
    $this->documentStorage = Mockery::mock(DocumentStorageInterface::class);

    $this->user->shouldReceive('getEmail')
        ->andReturn('jane@email.com');

    $uploadedFile->shouldReceive('getClientFilename')
        ->andReturn('another-document.pdf');
    $uploadedFile->shouldReceive('getClientMediaType')
        ->andReturn('application/pdf');
    $uploadedFile->shouldReceive('getSize')
        ->andReturn("1024");

    $this->documentStorage->shouldReceive('saveUploadedFile')
        ->once()
        ->with(Mockery::type('string'), $uploadedFile, 'jane@email.com');

    $document = Mockery::mock(Document::class);

    $this->documentService->shouldReceive('createDocument')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['name'] === time() . "_another-document.pdf" &&
                $arg['path'] === 'jane@email.com' &&
                $arg['type'] === 'application/pdf' &&
                $arg['size'] === '1024' &&
                $arg['user'] instanceof User;
        }))
        ->andReturn($document);

    $createDocumentAction = new CreateDocument($this->documentService, $this->documentStorage);
    $uploadedFiles = ['document' => $uploadedFile];
    $tags = [];

    $result = $createDocumentAction->execute($uploadedFiles, $this->user, $tags);
    expect($result)->toBe($document);
});


it('throws an exception when document creation fails', function () {
    $uploadedFile = Mockery::mock(UploadedFileInterface::class);
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
