<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Domain\User\Service\AuthenticationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginUserController
{

    public function __construct(private AuthenticationService $authenticationService) {}
    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        // Add validation to validate that $data is an array and contains all required fields
        if (!is_array($data) || !$this->validateRequestData($data)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Invalid or missing data in request.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $email = $data['email'];
        $password = $data['password'];

        $user = $this->authenticationService->authenticate($email, $password);

        if (!$user) {
            $response->getBody()->write(json_encode(['success' => 'false', 'message' => 'Invalid credentials']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        $token = $this->authenticationService->generateToken($user);
        $response->getBody()->write(json_encode(['success' => 'true', 'token' => $token->getToken()]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    private function validateRequestData(array $data): bool
    {
        $requiredFields = ['email', 'password'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}
