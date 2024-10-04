<?php

declare(strict_types=1);

namespace App\Domain\Document\Repository;

use App\Domain\Document\Entity\Document;

interface DocumentRepository
{

    public function findAll(): array;

    public function findById(int $id): ?Document;
    public function save(Document $document): ?Document;
    public function delete(int $id): void;
    public function search(string $query): array;
}
