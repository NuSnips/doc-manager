<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Entity\Metadata;
use App\Domain\Document\Repository\DocumentRepository;
use App\Domain\Document\Service\DocumentServiceInterface;
use App\Infrastructure\Persistence\ElasticSearchDocumentRepository;

class DocumentService implements DocumentServiceInterface
{
    public function __construct(
        private DocumentRepository $documentRepository,
        private ElasticSearchDocumentRepository $elasticSearchDocumentRepository
    ) {}

    public function getDocuments(): array
    {
        return $this->documentRepository->findAll();
    }

    public function getDocument(int $id): ?Document
    {
        return $this->documentRepository->findById($id);
    }

    public function createDocument(array $data): ?Document
    {
        $metaData = new Metadata();
        $document = new Document($data['name'], $data['path'], $metaData, $data['user']);
        $metaData->setSize($data['size']);
        $metaData->setType($data['type']);
        $metaData->setTags($data['tags']);
        $metaData->setDocument($document);

        return $this->documentRepository->save($document);
    }

    public function createDocumentShare(Document $document): ?Document
    {
        if ($document) {
            $this->documentRepository->save($document);
        }
        return $document;
    }


    public function deleteDocument(int $id): void
    {
        try {
            $this->documentRepository->delete($id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function search(string $searchTerm): array
    {
        // Get the IDs from elastic
        $ids = $this->elasticSearchDocumentRepository->search($searchTerm);
        // Use the IDs to query the db
        return $this->documentRepository->findByIds($ids);
    }
}
