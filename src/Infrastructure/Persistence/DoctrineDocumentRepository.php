<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Document\Entity\Document;
use App\Domain\Document\Repository\DocumentRepository;
use App\Domain\User\Service\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Exception;

class   DoctrineDocumentRepository implements DocumentRepository
{

    public function __construct(private EntityManager $entityManager, private AuthenticationService $authenticationService, private ElasticSearchDocumentRepository $elasticSearchDocumentRepository) {}

    public function findAll(): array
    {
        $user = $this->authenticationService->getUser(getallheaders()['Authorization']);

        return $this->entityManager->createQueryBuilder()
            ->select('doc')
            ->from(Document::class, 'doc')
            ->where('doc.user = :user_id')
            ->setParameter('user_id', $user->getId())
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Document
    {
        $user = $this->authenticationService->getUser(getallheaders()['Authorization']);
        return $this->entityManager->createQueryBuilder()
            ->select('doc')
            ->from(Document::class, 'doc')
            ->where('doc.id = :id')
            ->andWhere('doc.user = :user_id')
            ->setParameter('id', $id)
            ->setParameter('user_id', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Document $document): ?Document
    {
        $this->entityManager->persist($document);
        $this->entityManager->flush();
        // index document in elastic search
        $this->elasticSearchDocumentRepository->index($document);
        return $document;
    }

    public function delete(int $id): void
    {
        $document = $this->findById($id);
        $user = $this->authenticationService->getUser(getallheaders()['Authorization']);
        if ($document->getUser()->getId() !== $user->getId()) throw new Exception('Unauthorized.', 403);
        if ($document) {
            $this->entityManager->remove($document);
            $this->entityManager->flush();
        }
    }

    public function search(string $query): array
    {
        $user = $this->authenticationService->getUser(getallheaders()['Authorization']);
        return $this->entityManager->createQueryBuilder()
            ->select('doc')
            ->from(Document::class, 'doc')
            ->where('doc.name LIKE :query')
            ->andWhere('doc.user = :user_id')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('user_id', $user->getId())
            ->getQuery()
            ->getResult();
    }
}
