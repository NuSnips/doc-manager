<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\AuthenticationService;
use App\Domain\User\ValueObject\Token;
use Doctrine\ORM\EntityManager;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DoctrineAuthService implements AuthenticationService
{
    public function __construct(
        private EntityManager $entityManager,
        private UserRepository $userRepository
    ) {}
    public function authenticate(string $email, string $password): ?User
    {
        // Find user
        $user = $this->userRepository->findByEmail($email);
        // Verify password
        if ($user && password_verify($password, $user->getPasswordHash())) {
            return $user;
        }
        return null;
    }

    public function generateToken(User $user): Token
    {
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => time(),
            'exp' => time() + 3600
        ];
        $jwtSecret = $_ENV['JWT_SECRET'];

        $jwt = JWT::encode($payload, $jwtSecret, 'HS256');
        return new Token($jwt);
    }
    public function validateToken(Token $token): ?User
    {
        try {
            $decoded = JWT::decode(
                $token->getToken(),
                new Key($_ENV['JWT_SECRET'], 'HS256')
            );
            $user = $this->userRepository->findById($decoded->sub);
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUser(string $tokenString): ?User
    {
        $token = new Token(str_replace('Bearer ', '', $tokenString));
        // Get the email of the authenticated user
        return $this->validateToken($token);
    }
}
