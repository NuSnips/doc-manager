<?php

declare(strict_types=1);

namespace App\Domain\Document\Repository;

use App\Domain\Document\Entity\Document;
use Doctrine\Common\Collections\Collection;

interface DocumentRepository
{

    public function findAll(): array;

    public function findById(int $id): ?Document;
    public function save(Document $document): ?Document;
    public function delete(int $id): void;
    public function search(string $query): array;

    public function findByIds(array $ids): array;
}
