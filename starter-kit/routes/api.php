<?php

declare(strict_types=1);

use App\Controller\VitrineController;
use App\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app, VitrineController $controller): void {
      $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->get('/health', function (
            Request $request,
            Response $response
        ): Response {
            return JsonResponder::respond($response, [
                'status' => 'ok',
                'service' => 'Escape Ops',
            ]);
        });
    });
};
