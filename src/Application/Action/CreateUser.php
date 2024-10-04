<?php

declare(strict_types=1);

namespace App\Application\Action;

use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserServiceInterface;
use Exception;

class CreateUser
{
    public function __construct(private UserServiceInterface $userService) {}
    public function execute(string $first_name, string $last_name,  string $email,  string $password): ?User
    {
        try {
            $user = $this->userService->registerUser(new User($first_name, $last_name, $email, $password));
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }
}
