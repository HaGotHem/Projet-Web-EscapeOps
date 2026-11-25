<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Csrf;
use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Outils de développement. Ce contrôleur et sa route ne sont créés
 * qu'en APP_ENV=dev avec APP_DEBUG=true : en production, /debug/... n'existe pas (404).
 */
final class DebugController
{
    public function __construct(private AuthService $auth)
    {
    }

    /** POST /debug/connexion : se connecter en un clic à un compte de démonstration. */
    public function connecter(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $donnees = (array) $request->getParsedBody();
        $retour = '/';

        if (Csrf::estValide($donnees['csrf'] ?? null)) {
            $utilisateur = $this->auth->connecterSansMotDePasse((int) ($donnees['id_utilisateur'] ?? 0));
            if ($utilisateur !== null) {
                $retour = AuthController::ACCUEIL_DU_ROLE[$utilisateur['role']] ?? '/';
            }
        }

        return $response->withHeader('Location', $retour)->withStatus(303);
    }
}
