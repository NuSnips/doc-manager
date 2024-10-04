<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserServiceInterface;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Infrastructure\Storage\DocumentStorage;
use Exception;

class UserService implements UserServiceInterface
{
    public function __construct(private DoctrineUserRespository $doctrineUserRespository, private DocumentStorage $documentStorage) {}

    public function registerUser(User $user): User
    {
        try {
            $this->doctrineUserRespository->store($user);
            // When user is registered create folder for that user
            $this->documentStorage->createFolder($user->getEmail());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $user;
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->doctrineUserRespository->findByEmail($email);
    }
}
