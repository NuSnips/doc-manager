<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Domain\User\Service\AuthenticationService;
use App\Presentation\Validation\InputValidator;
use Illuminate\Support\Facades\Validator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginUserController
{

    public function __construct(private AuthenticationService $authenticationService) {}
    public function login(Request $request, Response $response, InputValidator $inputValidator)
    {
        $data = $request->getParsedBody();

        if ($data == null || !is_array($data) || count($data) == 0) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Invalid or missing data in request.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Validate input data
        $dataIsValid = $inputValidator->validate(
            [
                'email' => $data['email'] ?? '',
                'password' => $data['password'] ?? ''
            ],
            [
                'email' => ['required' => true, 'email' => true],
                'password' => ['required' => true]
            ]
        );
        if (!$dataIsValid) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Invalid or missing data in request.', 'errors' => $inputValidator->getErrors()]));
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
