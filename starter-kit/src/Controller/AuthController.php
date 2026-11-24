<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Csrf;
use App\Http\ViewRenderer;
use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Connexion / déconnexion de l'équipe (S4). Le rôle est lié au compte :
 * après connexion, chacun arrive sur son espace.
 */
final class AuthController
{
    /** Page d'arrivée de chaque rôle (maquette Connexion). */
    public const ACCUEIL_DU_ROLE = [
        'RESPONSABLE' => '/responsable/sessions',
        'GAME_MASTER' => '/gm/sessions',
        'ADMIN' => '/admin/comptes',
    ];

    public function __construct(
        private AuthService $auth,
        private ViewRenderer $renderer
    ) {
    }

    /** GET /connexion */
    public function formulaire(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        if ($this->auth->utilisateurConnecte() !== null) {
            return $this->rediriger($response, '/espace');
        }

        return $this->afficher($response, null, '');
    }

    /** POST /connexion */
    public function connecter(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $donnees = (array) $request->getParsedBody();
        $email = is_string($donnees['email'] ?? null) ? $donnees['email'] : '';
        $motDePasse = is_string($donnees['mot_de_passe'] ?? null) ? $donnees['mot_de_passe'] : '';

        if (!Csrf::estValide($donnees['csrf'] ?? null)) {
            return $this->afficher($response, 'Votre session a expiré. Rechargez la page et réessayez.', $email, 403);
        }

        $utilisateur = $this->auth->connecter($email, $motDePasse);

        if ($utilisateur === null) {
            // Un seul message pour tous les échecs : on ne dit pas si l'email existe
            return $this->afficher($response, 'E-mail ou mot de passe non reconnu.', $email, 401);
        }

        // 303 See Other : le navigateur recharge la page d'arrivée en GET (pas de double envoi)
        return $this->rediriger($response, self::ACCUEIL_DU_ROLE[$utilisateur['role']] ?? '/');
    }

    /** POST /deconnexion (POST et jeton CSRF : un lien piégé ne peut pas déconnecter quelqu'un) */
    public function deconnecter(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $donnees = (array) $request->getParsedBody();

        if (Csrf::estValide($donnees['csrf'] ?? null)) {
            $this->auth->deconnecter();
        }

        return $this->rediriger($response, '/');
    }

    /** GET /espace : renvoie chaque rôle vers sa page d'arrivée. */
    public function espace(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $utilisateur = $request->getAttribute('utilisateur');

        return $this->rediriger($response, self::ACCUEIL_DU_ROLE[$utilisateur['role']] ?? '/');
    }

    private function afficher(
        ResponseInterface $response,
        ?string $erreur,
        string $email,
        int $statut = 200
    ): ResponseInterface {
        return $this->renderer->render($response, 'connexion', [
            'erreur' => $erreur,
            'email' => $email,
            'csrf' => Csrf::jeton(),
        ], $statut);
    }

    private function rediriger(ResponseInterface $response, string $url): ResponseInterface
    {
        return $response->withHeader('Location', $url)->withStatus(303);
    }
}
