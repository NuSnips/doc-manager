<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;

interface UserRepository
{
    public function save(User $user): ?User;
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
}
