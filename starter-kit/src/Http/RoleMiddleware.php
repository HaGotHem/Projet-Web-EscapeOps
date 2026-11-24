<?php

declare(strict_types=1);

namespace App\Http;

use App\Service\AuthService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Contrôle d'accès côté serveur (S4), posé sur un groupe de routes.
 * - personne de connecté  → redirection vers /connexion ;
 * - rôle non autorisé     → page 403 ;
 * - sinon la requête continue, avec l'utilisateur en attribut « utilisateur ».
 */
final class RoleMiddleware implements MiddlewareInterface
{
    /** @param string[] $roles rôles autorisés sur ces routes */
    public function __construct(
        private AuthService $auth,
        private ViewRenderer $renderer,
        private ResponseFactoryInterface $reponses,
        private array $roles
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $utilisateur = $this->auth->utilisateurConnecte();

        if ($utilisateur === null) {
            return $this->reponses->createResponse(303)->withHeader('Location', '/connexion');
        }

        if (!in_array($utilisateur['role'], $this->roles, true)) {
            return $this->renderer->render($this->reponses->createResponse(), 'message', [
                'titre' => 'Accès refusé',
                'texte' => 'Votre rôle ne donne pas accès à cette page.',
                'code' => 403,
                'lien' => '/espace',
                'lienTexte' => 'Retour à mon espace',
                'utilisateur' => $utilisateur,
            ], 403);
        }

        $reponse = $handler->handle($request->withAttribute('utilisateur', $utilisateur));

        // Pages privées : pas de cache, pour que « Précédent » après déconnexion ne les réaffiche pas
        return $reponse->withHeader('Cache-Control', 'no-store');
    }
}
