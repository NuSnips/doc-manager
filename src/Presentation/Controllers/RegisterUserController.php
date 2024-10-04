<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Action\CreateUser;
use App\Domain\User\Service\UserServiceInterface;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RegisterUserController
{

    public function __construct(
        private CreateUser $createUser,
        private UserServiceInterface $userService,
        private DoctrineUserRespository $doctrineUserRespository
    ) {}
    public function register(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        // TODO: Valdidate and sanitize data

        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $email = $data['email'];
        $password = $data['password'];
        // Check if email exists
        if ($this->doctrineUserRespository->findByEmail($email)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'User already exists.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        // Create user
        $user = $this->createUser->execute($first_name, $last_name, $email, $password);
        if (!$user) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => "Error registering user."]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'User registered.']));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }
}
