<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * Routes HTML.
 * @param array    $c    contrôleurs : vitrine, auth, equipe, reservation
 * @param callable $role $role(['RESPONSABLE']) crée le middleware qui réserve des routes à ces rôles
 */
return static function (App $app, array $c, callable $role): void {
    // --- Public : vitrine et réservation d'un groupe (S3, S5) ------------------
    $app->get('/', [$c['vitrine'], 'accueil']);
    $app->get('/reserver/confirmation', [$c['reservation'], 'confirmation']);
    $app->get('/reserver/{id:[0-9]+}', [$c['reservation'], 'formulaire']);
    $app->post('/reserver/{id:[0-9]+}', [$c['reservation'], 'reserver']);

    // --- Connexion (S4) --------------------------------------------------------
    $app->get('/connexion', [$c['auth'], 'formulaire']);
    $app->post('/connexion', [$c['auth'], 'connecter']);
    $app->post('/deconnexion', [$c['auth'], 'deconnecter']);
    $app->get('/espace', [$c['auth'], 'espace'])
        ->add($role(['RESPONSABLE', 'GAME_MASTER', 'ADMIN']));

    // --- Responsable : planning et sessions (S5) --------------------------------
    $app->group('/responsable', function (RouteCollectorProxy $g) use ($c): void {
        $g->get('/sessions', [$c['equipe'], 'planning']);
        $g->get('/sessions/nouvelle', [$c['equipe'], 'nouvelleSession']);
        $g->post('/sessions', [$c['equipe'], 'creerSession']);
        $g->get('/sessions/{id:[0-9]+}', [$c['equipe'], 'session']);
        $g->post('/sessions/{id:[0-9]+}/etat', [$c['equipe'], 'changerEtat']);
    })->add($role(['RESPONSABLE']));

    // --- Game master -------------------------------------------------------------
    $app->get('/gm/sessions', [$c['equipe'], 'sessionsDuGameMaster'])
        ->add($role(['GAME_MASTER']));

    // --- Administrateur ----------------------------------------------------------
    $app->get('/admin/comptes', [$c['equipe'], 'comptes'])
        ->add($role(['ADMIN']));
};
