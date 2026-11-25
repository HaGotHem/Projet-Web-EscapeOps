<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\RegleMetierException;
use App\Domain\SaisieInvalide;
use App\Http\Csrf;
use App\Http\ViewRenderer;
use App\Repository\UtilisateurRepository;
use App\Service\SessionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Pages de l'équipe, derrière RoleMiddleware (S4/S5) :
 * responsable (planning, sessions), game master (mes sessions), admin (comptes).
 */
final class EquipeController
{
    public function __construct(
        private SessionService $sessions,
        private UtilisateurRepository $utilisateurs,
        private ViewRenderer $renderer
    ) {
    }

    // --- Responsable ---------------------------------------------------------

    /** GET /responsable/sessions */
    public function planning(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->vue($request, $response, 'responsable/planning', [
            'sessions' => $this->sessions->planning(),
            'jours' => SessionService::JOURS_PLANNING,
            'message' => $this->lireMessage(),
        ]);
    }

    /** GET /responsable/sessions/nouvelle */
    public function nouvelleSession(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->formulaireSession($request, $response, [], []);
    }

    /** POST /responsable/sessions */
    public function creerSession(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $saisie = (array) $request->getParsedBody();

        if (!Csrf::estValide($saisie['csrf'] ?? null)) {
            return $this->formulaireSession($request, $response, $saisie, ['csrf' => 'Votre session a expiré. Réessayez.'], 403);
        }

        try {
            $id = $this->sessions->creer($saisie);
        } catch (SaisieInvalide $e) {
            return $this->formulaireSession($request, $response, $saisie, $e->erreurs, 422);
        }

        $this->ecrireMessage('Session créée : elle est « Prévue ».');

        return $this->rediriger($response, '/responsable/sessions/' . $id);
    }

    /** GET /responsable/sessions/{id} */
    public function session(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $session = $this->sessions->detail((int) $args['id']);

        if ($session === null) {
            return $this->vue($request, $response, 'message', [
                'titre' => 'Session introuvable',
                'texte' => 'Cette session n\'existe pas.',
                'code' => 404,
                'lien' => '/responsable/sessions',
                'lienTexte' => 'Retour au planning',
            ], 404);
        }

        return $this->vue($request, $response, 'responsable/session', [
            'session' => $session,
            'message' => $this->lireMessage(),
        ]);
    }

    /** POST /responsable/sessions/{id}/etat */
    public function changerEtat(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $saisie = (array) $request->getParsedBody();

        if (!Csrf::estValide($saisie['csrf'] ?? null)) {
            $this->ecrireMessage('Votre session a expiré. Réessayez.', true);
        } else {
            try {
                $this->sessions->changerEtat($id, is_string($saisie['etat'] ?? null) ? $saisie['etat'] : '');
                $this->ecrireMessage('État de la session mis à jour.');
            } catch (RegleMetierException $e) {
                $this->ecrireMessage($e->getMessage(), true);
            }
        }

        // Post/Redirect/Get : le message est affiché sur la page rechargée en GET
        return $this->rediriger($response, '/responsable/sessions/' . $id);
    }

    /** POST /responsable/sessions/{id}/game-master (règle E5) */
    public function affecterGameMaster(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $saisie = (array) $request->getParsedBody();

        if (!Csrf::estValide($saisie['csrf'] ?? null)) {
            $this->ecrireMessage('Votre session a expiré. Réessayez.', true);
        } else {
            try {
                $nom = $this->sessions->affecterGameMaster($id, (int) ($saisie['id_game_master'] ?? 0));
                $this->ecrireMessage('Game master affecté : ' . $nom . '.');
            } catch (RegleMetierException $e) {
                $this->ecrireMessage($e->getMessage(), true);
            }
        }

        return $this->rediriger($response, '/responsable/sessions/' . $id);
    }

    // --- Game master -----------------------------------------------------------

    /** GET /gm/sessions */
    public function sessionsDuGameMaster(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $utilisateur = $request->getAttribute('utilisateur');

        return $this->vue($request, $response, 'gm/sessions', [
            'sessions' => $this->sessions->sessionsDuGameMaster($utilisateur['id']),
            'jours' => SessionService::JOURS_PLANNING,
        ]);
    }

    // --- Administrateur --------------------------------------------------------

    /** GET /admin/comptes */
    public function comptes(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->vue($request, $response, 'admin/comptes', [
            'comptes' => $this->utilisateurs->all(),
        ]);
    }

    // --- Outils ------------------------------------------------------------------

    private function formulaireSession(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $valeurs,
        array $erreurs,
        int $statut = 200
    ): ResponseInterface {
        return $this->vue($request, $response, 'responsable/nouvelle-session', [
            'salles' => $this->sessions->salles(),
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
        ], $statut);
    }

    /** Toutes les vues de l'équipe reçoivent l'utilisateur connecté et le jeton CSRF. */
    private function vue(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $template,
        array $donnees,
        int $statut = 200
    ): ResponseInterface {
        return $this->renderer->render($response, $template, $donnees + [
            'utilisateur' => $request->getAttribute('utilisateur'),
            'csrf' => Csrf::jeton(),
        ], $statut);
    }

    private function rediriger(ResponseInterface $response, string $url): ResponseInterface
    {
        return $response->withHeader('Location', $url)->withStatus(303);
    }

    /** Message « flash » : rangé en session, affiché une seule fois après la redirection. */
    private function ecrireMessage(string $texte, bool $erreur = false): void
    {
        $_SESSION['message'] = ['texte' => $texte, 'erreur' => $erreur];
    }

    private function lireMessage(): ?array
    {
        $message = $_SESSION['message'] ?? null;
        unset($_SESSION['message']);

        return is_array($message) ? $message : null;
    }
}
