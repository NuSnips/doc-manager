<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Token;

interface AuthenticationService
{
    public function authenticate(string $email, string $password): ?User;
    public function generateToken(User $user): Token;
    public function validateToken(Token $token): ?User;

    public function getUser(string $tokenString): ?User;
}
