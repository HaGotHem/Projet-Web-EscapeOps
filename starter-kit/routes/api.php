<?php

declare(strict_types=1);

use App\Controller\ApiController;
use App\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * Routes de l'API JSON (contrat : docs/api.md).
 * @param callable $auth $auth(['RESPONSABLE']) crée le middleware Basic qui réserve une route à ces rôles
 */
return static function (App $app, ApiController $api, callable $auth): void {
    $app->group('/api', function (RouteCollectorProxy $g) use ($api, $auth): void {
        $g->get('/health', function (Request $request, Response $response): Response {
            return JsonResponder::respond($response, ['status' => 'ok', 'service' => 'Escape Ops']);
        });

        // Lecture publique : ce que montre déjà la vitrine
        $g->get('/salles', [$api, 'salles']);
        $g->get('/salles/{id:[0-9]+}', [$api, 'detailSalle']);
        $g->get('/sessions', [$api, 'creneaux']);
        $g->get('/sessions/{id:[0-9]+}', [$api, 'detailSession']);

        // Écriture et données internes : authentification Basic + rôle
        $g->post('/sessions/{id:[0-9]+}/game-master', [$api, 'affecterGameMaster'])
            ->add($auth(['RESPONSABLE']));
        $g->get('/incidents', [$api, 'incidents'])
            ->add($auth(['RESPONSABLE', 'GAME_MASTER']));
        $g->get('/incidents/{id:[0-9]+}', [$api, 'detailIncident'])
            ->add($auth(['RESPONSABLE', 'GAME_MASTER']));
        $g->post('/incidents', [$api, 'declarerIncident'])
            ->add($auth(['RESPONSABLE', 'GAME_MASTER']));
    });
};
