<?php

use Slim\App;
use Slim\Exception\HttpNotFoundException;

return function (App $app) {
    $settings = $app->getContainer()->get('settings');
    $errorMiddleware = $app->addErrorMiddleware(
        $settings['displayErrorDetails'],
        $settings['logErrorDetails'],
        $settings['logErrors']
    );

    // Get the default error handler and override the default response
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->forceContentType('application/json');

    // Set the renderer for the error handler
    $errorHandler->setDefaultErrorRenderer('application/json', function ($exception, $displayErrorDetails) {
        $statusCode = 500;
        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
        }

        $error = [
            'message' => $exception->getMessage(),
            'status' => $statusCode,
        ];

        if ($displayErrorDetails) {
            $error['trace'] = $exception->getTrace();
        }
        return $error;
    });
};
