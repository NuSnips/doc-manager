<?php

declare(strict_types=1);

namespace App\Domain\Document\Service;

use App\Domain\Document\Entity\Document;

interface DocumentServiceInterface
{
    public function getDocuments(): array;
    public function getDocument(int $id): ?Document;
    public function createDocument(array $data): ?Document;
    public function createDocumentShare(Document $document): ?Document;

    public function deleteDocument(int $id): void;

    public function search(string $searchTerm): array;
}
