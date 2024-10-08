<?php

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\Document\Service\DocumentServiceInterface;
use App\Infrastructure\Service\DoctrineAuthService;
use App\Application\Action\CreateDocument;
use App\Application\Action\DeleteDocument;
use App\Domain\DocumentShare\Repository\DocumentShareRepository;
use App\Presentation\Controllers\DocumentController;
use App\Domain\User\Entity\User;
use App\Presentation\Validation\InputValidator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

beforeEach(function () {
    $this->documentService = Mockery::mock(DocumentServiceInterface::class);
    $this->doctrineAuthService = Mockery::mock(DoctrineAuthService::class);
    $this->createDocument = Mockery::mock(CreateDocument::class);
    $this->deleteDocument = Mockery::mock(DeleteDocument::class);
    $this->documentShareRepository = Mockery::mock(DocumentShareRepository::class);
    $this->inputValidator = Mockery::mock(InputValidator::class);

    $this->request = Mockery::mock(Request::class);
    $this->response = Mockery::mock(Response::class);

    $this->controller = new DocumentController(
        $this->documentService,
        $this->doctrineAuthService,
        $this->createDocument,
        $this->deleteDocument,
        $this->documentShareRepository
    );
});

it('can list all documents', function () {
    $document = Mockery::mock(Document::class);
    $metadata = Mockery::mock(Metadata::class);
    $user = Mockery::mock(User::class);

    $document->shouldReceive('getId')->andReturn(1);
    $document->shouldReceive('getName')->andReturn('Test Document');
    $document->shouldReceive('getPath')->andReturn('/path/to/document');
    $document->shouldReceive('getMetadata')->andReturn($metadata);
    $document->shouldReceive('getUser')->andReturn($user);

    $metadata->shouldReceive('getType')->andReturn('pdf');
    $metadata->shouldReceive('getSize')->andReturn('200KB');
    $metadata->shouldReceive('getTags')->andReturn(['tag1', 'tag2']);

    $user->shouldReceive('getEmail')->andReturn('jack@email.com');

    $this->documentService->shouldReceive('getDocuments')->andReturn([$document]);

    $request = Mockery::mock(Request::class);
    $request->shouldReceive('getQueryParams')->andReturn([]);

    $response = Mockery::mock(Response::class);
    $response->shouldReceive('getBody->write')->with(Mockery::on(function ($json) {
        $data = json_decode($json, true);
        return isset($data['documents']) && count($data['documents']) === 1;
    }))->once();
    $response->shouldReceive('withHeader')->andReturnSelf();

    $this->controller->index($request, $response);
});

it('can get a single document', function () {
    $document = Mockery::mock(Document::class);
    $metadata = Mockery::mock(Metadata::class);
    $user = Mockery::mock(User::class);

    $document->shouldReceive('getId')->andReturn(1);
    $document->shouldReceive('getName')->andReturn('Test Document');
    $document->shouldReceive('getPath')->andReturn('/path/to/document');
    $document->shouldReceive('getMetadata')->andReturn($metadata);
    $document->shouldReceive('getUser')->andReturn($user);

    $metadata->shouldReceive('getType')->andReturn('pdf');
    $metadata->shouldReceive('getSize')->andReturn('200KB');
    $metadata->shouldReceive('getTags')->andReturn(['tag1', 'tag2']);

    $user->shouldReceive('getEmail')->andReturn('jack@email.com');

    $this->documentService->shouldReceive('getDocument')->with(1)->andReturn($document);

    $request = Mockery::mock(Request::class);
    $response = Mockery::mock(Response::class);

    $response->shouldReceive('withStatus')->with(200)->andReturnSelf();
    $response->shouldReceive('getBody->write')->with(Mockery::on(function ($json) {
        $data = json_decode($json, true);
        return isset($data['document']) && $data['document']['id'] === 1;
    }))->once();
    $response->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();

    $this->controller->show($request, $response, 1);
});


it('returns 400 when data is missing', function () {
    $token = 'Bearer someValidToken';
    $user = Mockery::mock(User::class);
    $document = Mockery::mock(Document::class);

    $this->doctrineAuthService->shouldReceive('getUser')->with($token)->andReturn($user);

    $data = null;

    $this->request->shouldReceive('getHeaderLine')->with('Authorization')->andReturn($token);
    $this->request->shouldReceive('getParsedBody')->andReturn($data);
    $this->request->shouldReceive('getUploadedFiles')->andReturn([]);

    $this->response->shouldReceive('getBody->write')
        ->once()
        ->with(json_encode(['success' => false, 'message' => 'Invalid or missing data in request.']));
    $this->response->shouldReceive('withStatus')->andReturnSelf();
    $this->response->shouldReceive('withHeader')->andReturnSelf();

    $this->controller->store($this->request, $this->response, $this->inputValidator);
});

it('can store a document', function () {
    $token = 'Bearer someValidToken';
    $user = Mockery::mock(User::class);
    $document = Mockery::mock(Document::class);

    $this->doctrineAuthService->shouldReceive('getUser')->with($token)->andReturn($user);

    $data = [
        'tags' => ['tag1', 'tag2']
    ];

    $this->request->shouldReceive('getHeaderLine')->with('Authorization')->andReturn($token);
    $this->request->shouldReceive('getParsedBody')->andReturn($data);
    $this->request->shouldReceive('getUploadedFiles')->andReturn([]);

    $this->response->shouldReceive('getBody->write')->with(Mockery::on(function ($json) {
        $data = json_decode($json, true);
        return $data['success'] === true;
    }))->once();
    $this->response->shouldReceive('withHeader')->andReturnSelf();
    $this->response->shouldReceive('withStatus')->andReturnSelf();

    $this->createDocument->shouldReceive('execute')->with([], $user, ['tag1', 'tag2'])->andReturn($document);
    $this->inputValidator->shouldReceive('validate')
        ->andReturn(true);

    $this->controller->store($this->request, $this->response, $this->inputValidator);
});

it('can delete a document', function () {
    $document = Mockery::mock(Document::class);

    $this->documentService->shouldReceive('getDocument')->with(1)->andReturn($document);

    $this->deleteDocument->shouldReceive('execute')->with($document)->once();

    $request = Mockery::mock(Request::class);
    $response = Mockery::mock(Response::class);

    $response->shouldReceive('getBody->write')->with(Mockery::on(function ($json) {
        $data = json_decode($json, true);
        return $data['success'] === true;
    }))->once();
    $response->shouldReceive('withHeader')->andReturnSelf();

    $this->controller->destroy($request, $response, 1);
});
