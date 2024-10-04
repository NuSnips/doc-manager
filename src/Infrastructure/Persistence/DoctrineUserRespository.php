<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Doctrine\ORM\EntityManager;

class DoctrineUserRespository implements UserRepository
{

    public function __construct(private EntityManager $entityManager) {}

    public function store(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
    public function findAll(): array
    {
        return [];
    }

    public function findById(int $id): ?User
    {
        $user = $this->entityManager->find(User::class, $id);
        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        return $user;
    }
}
