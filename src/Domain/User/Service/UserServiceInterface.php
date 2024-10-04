<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

use App\Domain\User\Entity\User;

interface UserServiceInterface
{
    public function registerUser(User $user): User;
    public function findUserByEmail(string $email): ?User;
}
