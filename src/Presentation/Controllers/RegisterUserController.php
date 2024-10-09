<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Action\CreateUser;
use App\Domain\User\Service\UserServiceInterface;
use App\Infrastructure\Persistence\DoctrineUserRespository;
use App\Presentation\Validation\InputValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RegisterUserController
{

    public function __construct(
        private CreateUser $createUser,
        private UserServiceInterface $userService,
        private DoctrineUserRespository $doctrineUserRespository
    ) {}
    public function register(Request $request, Response $response, InputValidator $inputValidator)
    {

        $data = $request->getParsedBody();
        if ($data == null || !is_array($data) || count($data) == 0) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Invalid or missing data in request.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        // Validate input data
        $dataIsValid = $inputValidator->validate(
            [
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? '',
                'password' => $data['password'] ?? ''
            ],
            [
                'first_name' => ['required' => true],
                'last_name' => ['required' => true],
                'email' => ['required' => true, 'email' => true],
                'password' => ['required' => true]
            ]
        );
        if (!$dataIsValid) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Invalid or missing data in request.', 'errors' => $inputValidator->getErrors()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

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
