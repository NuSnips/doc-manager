<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Action\CreateDocument;
use App\Application\Action\DeleteDocument;
use App\Domain\Document\Service\DocumentServiceInterface;
use App\Domain\DocumentShare\Entity\DocumentShare;
use App\Domain\DocumentShare\Repository\DocumentShareRepository;
use App\Domain\User\ValueObject\Token;
use App\Infrastructure\Service\DoctrineAuthService;
use App\Infrastructure\Storage\DocumentStorage;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocumentController
{

    public function __construct(
        private DocumentServiceInterface $documentService,
        private DoctrineAuthService $doctrineAuthService,
        private CreateDocument $createDocument,
        private DeleteDocument $deleteDocument,
        private DocumentShareRepository $documentShareRepository
    ) {}

    /**
     * List documents
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return Response
     */
    public function index(Request $request, Response $response)
    {
        // Search if query available
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['q']) && $queryParams['q'] != null) {
            $documents = $this->documentService->search($queryParams['q']);
        } else {
            $documents = $this->documentService->getDocuments();
        }
        $documents = array_map(function ($document) {
            return [
                'id' => $document->getId(),
                'name' => $document->getName(),
                'path' => $document->getPath(),
                'type' => $document->getMetadata()->getType(),
                'size' => $document->getMetadata()->getSize(),
                'user' => $document->getUser()->getEmail(),
                'tags' => $document->getMetadata()->getTags()
            ];
        }, $documents);
        $response->getBody()->write(json_encode(['documents' => $documents]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get a single document
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $id
     * @return Response
     */
    public function show(Request $request, Response $response, int $id)
    {
        $document = $this->documentService->getDocument($id);
        if (!$document) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Document not found.']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        $document = [
            'id' => $document->getId(),
            'name' => $document->getName(),
            'path' => $document->getPath(),
            'type' => $document->getMetadata()->getType(),
            'size' => $document->getMetadata()->getSize(),
            'user' => $document->getUser()->getEmail(),
            'tags' => $document->getMetadata()->getTags()
        ];
        $response->getBody()->write(json_encode(['document' => $document]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    /**
     * Store document
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return Response
     */
    public function store(Request $request, Response $response)
    {
        // Get the authenticated user
        $tokenString = $request->getHeaderLine('Authorization');
        $user = $this->doctrineAuthService->getUser($tokenString);

        // Get the form data
        $data = $request->getParsedBody();
        $tags = $data['tags'] ?? [];
        // TODO: Valdidate and sanitize data
        try {
            // Upload document
            $uploadedFile = $this->createDocument->execute($request->getUploadedFiles(), $user, $tags);
            $response->getBody()->write(json_encode(['success' => true, 'message' =>  'Document created successfully.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
            return  $response->withStatus($e->getCode())->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Delete document
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request, Response $response, int $id)
    {
        // TODO: Valdidate and sanitize data

        // Get the document
        $document = $this->documentService->getDocument($id);
        // If document not found return 404
        if (!$document) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Document not found.']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        // If document exists delete
        try {
            $this->deleteDocument->execute($document);
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Document deleted successfully.']));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
            return $response->withStatus($e->getCode())->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Generate a shareable url
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $id
     * @return Response
     */
    public function genrateShareable(Request $request, Response $response, int $id)
    {
        // TODO: Valdidate and sanitize data

        // FInd the document
        $document = $this->documentService->getDocument($id);
        // if document not found return with errors
        if (!$document) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Document not found.']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        // Create a document share
        $documentShare = new DocumentShare($document, (new \DateTime())->modify("+30days"));
        $document->addDocumentShare($documentShare);
        // Save the document share
        $this->documentService->createDocumentShare($document);
        $path = "documents/download/" . $documentShare->getUrl();
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Document shareable link generated successfully.', 'link' => site_path($path)]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Download document using the shareable url 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string $token
     * @return Response
     */
    public function download(Request $request, Response $response, string $token)
    {
        // TODO: Valdidate and sanitize data

        $documentShare = $this->documentShareRepository->findByToken($token);
        if (!$documentShare) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Url not found.']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        $document = $documentShare->getDocument();
        if (!$document) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Document not found.']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        $documentStorage = new DocumentStorage();
        $documentStorage->download($document);
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}
