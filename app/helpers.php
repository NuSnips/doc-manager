<?php

use Psr\Http\Message\ResponseInterface as Response;
use Jenssegers\Blade\Blade;

if (!function_exists('view')) {
    function view(Response $response, $template, $with = [])
    {
        $cache = __DIR__ . "/../cache";
        $views = __DIR__ . "/../resources/views";
        $blade = (new Blade($views, $cache))->make($template, $with);
        $response->getBody()->write($blade->render());
        return $response;
    }
}

function base_path($path = "")
{
    return __DIR__ . "/../{$path}";
}

function public_path($path = "")
{
    return __DIR__ . "/../public/{$path}";
}

function site_path($path = "")
{
    return $_ENV['APP_URL'] . "/" . $path;
}
