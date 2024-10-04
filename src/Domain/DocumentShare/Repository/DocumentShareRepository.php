<?php

declare(strict_types=1);

namespace App\Domain\DocumentShare\Repository;

use App\Domain\DocumentShare\Entity\DocumentShare;

interface DocumentShareRepository
{

    public function findAll(): array;

    public function findByToken(string $token): ?DocumentShare;
    public function save(DocumentShare $document): ?DocumentShare;
    public function delete(int $id): void;
}
