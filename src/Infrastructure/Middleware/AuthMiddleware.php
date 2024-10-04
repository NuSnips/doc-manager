<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Domain\User\ValueObject\Token;
use App\Infrastructure\Service\DoctrineAuthService;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as Psr7Response;

class AuthMiddleware implements MiddlewareInterface
{

    public function __construct(private DoctrineAuthService $doctrineAuthService) {}
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Get token from request
        $token = $request->getHeaderLine('Authorization');
        // if token is empty return immediately
        if (empty($token)) {
            $response = new Psr7Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        // remove Bearer from token
        // Validate token
        // if token is invalid return immediately
        // if token is valid add user to request and continue with the request
        $token = str_replace('Bearer ', '', $token);
        $user = $this->doctrineAuthService->validateToken(new Token($token));


        // if token is invalid return with error
        if (!$user) {
            $response = new Psr7Response();
            $response->getBody()->write(json_encode(['error' => 'Invalid token']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // add user to request
        $request = $request->withAttribute('user', $user);
        return $handler->handle($request);
    }
}
