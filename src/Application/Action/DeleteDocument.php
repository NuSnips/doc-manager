<?php

declare(strict_types=1);

namespace App\Application\Action;

use App\Application\Service\DocumentService;
use App\Domain\Document\Entity\Document;
use App\Infrastructure\Storage\DocumentStorage;
use Exception;

class DeleteDocument
{

    public function __construct(private DocumentService $documentService) {}
    public function execute(Document $document)
    {
        // Get the document
        $document = $this->documentService->getDocument($document->getId());
        try {
            // Delete document from db.
            $this->documentService->deleteDocument($document->getId());
            // Delete document file.
            $documentStorage = new DocumentStorage();
            $folderPath = explode(DIRECTORY_SEPARATOR, $document->getPath())[0];
            $documentStorage->deleteFile($document->getName(), $folderPath);
        } catch (Exception $e) {
            throw new Exception("Error deleting document: " . $e->getMessage());
        }
    }
}
