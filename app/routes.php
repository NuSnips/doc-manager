<?php

use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserServiceInterface;
use App\Infrastructure\Middleware\AuthMiddleware;
use App\Presentation\Controllers\DocumentController;
use App\Presentation\Controllers\LoginUserController;
use App\Presentation\Controllers\RegisterUserController;
use Elastic\Elasticsearch\Client;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {

    $app->post(
        "/register",
        [RegisterUserController::class, 'register']
    );
    $app->post("/login", [LoginUserController::class, 'login']);


    $app->get("/documents", [DocumentController::class, 'index'])->add(AuthMiddleware::class);
    $app->get("/documents/{id}", [DocumentController::class, 'show'])->add(AuthMiddleware::class);
    $app->post("/documents", [DocumentController::class, 'store'])->add(AuthMiddleware::class);
    $app->delete("/documents/{id}", [DocumentController::class, 'destroy'])->add(AuthMiddleware::class);
    $app->get("/documents/generate-url/{id}", [DocumentController::class, 'genrateShareable'])->add(AuthMiddleware::class);
    $app->get("/documents/download/{token}", [DocumentController::class, 'download']);
};
