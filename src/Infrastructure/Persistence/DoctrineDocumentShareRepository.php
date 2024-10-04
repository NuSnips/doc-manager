<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\DocumentShare\Entity\DocumentShare;
use App\Domain\DocumentShare\Repository\DocumentShareRepository;
use Doctrine\ORM\EntityManager;
use Exception;

class DoctrineDocumentShareRepository implements DocumentShareRepository
{

    public function __construct(private EntityManager $entityManager) {}

    public function findAll(): array
    {
        return $this->entityManager->getRepository(DocumentShare::class)->findAll();
    }

    public function findByToken(string $token): ?DocumentShare
    {
        return $this->entityManager->createQueryBuilder()
            ->select('docshare')
            ->from(DocumentShare::class, 'docshare')
            ->where('docshare.url = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(DocumentShare $documentShare): ?DocumentShare
    {
        $this->entityManager->persist($documentShare);
        $this->entityManager->flush();
        return $documentShare;
    }

    public function delete(int $id): void
    {
        //
    }
}
