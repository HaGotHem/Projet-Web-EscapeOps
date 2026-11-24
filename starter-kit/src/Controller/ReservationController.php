<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\RegleMetierException;
use App\Domain\SaisieInvalide;
use App\Http\Csrf;
use App\Http\ViewRenderer;
use App\Service\ReservationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Réservation publique d'un créneau par un groupe (S5, maquette Reservation).
 */
final class ReservationController
{
    public function __construct(
        private ReservationService $service,
        private ViewRenderer $renderer
    ) {
    }

    /** GET /reserver/{id} */
    public function formulaire(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $creneau = $this->service->creneau((int) $args['id']);
        } catch (RegleMetierException $e) {
            return $this->indisponible($response, $e->getMessage());
        }

        return $this->afficher($response, $creneau, [], []);
    }

    /** POST /reserver/{id} */
    public function reserver(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $saisie = (array) $request->getParsedBody();

        try {
            $creneau = $this->service->creneau($id);
        } catch (RegleMetierException $e) {
            return $this->indisponible($response, $e->getMessage());
        }

        if (!Csrf::estValide($saisie['csrf'] ?? null)) {
            return $this->afficher($response, $creneau, $saisie, ['csrf' => 'Votre session a expiré. Réessayez.'], 403);
        }

        try {
            $recap = $this->service->reserver($id, $saisie);
        } catch (SaisieInvalide $e) {
            return $this->afficher($response, $creneau, $saisie, $e->erreurs, 422);
        } catch (RegleMetierException $e) {
            return $this->indisponible($response, $e->getMessage());
        }

        // Post/Redirect/Get : un rafraîchissement ne crée pas une deuxième réservation
        $_SESSION['reservation_confirmee'] = $recap;

        return $response->withHeader('Location', '/reserver/confirmation')->withStatus(303);
    }

    /** GET /reserver/confirmation : affiché une seule fois, juste après la réservation. */
    public function confirmation(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $recap = $_SESSION['reservation_confirmee'] ?? null;
        unset($_SESSION['reservation_confirmee']);

        if (!is_array($recap)) {
            return $response->withHeader('Location', '/')->withStatus(303);
        }

        return $this->renderer->render($response, 'reservation/confirmation', ['recap' => $recap]);
    }

    private function afficher(ResponseInterface $response, array $creneau, array $valeurs, array $erreurs, int $statut = 200): ResponseInterface
    {
        return $this->renderer->render($response, 'reservation/formulaire', [
            'creneau' => $creneau,
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
            'csrf' => Csrf::jeton(),
        ], $statut);
    }

    /** 409 Conflict : le créneau existe peut-être, mais il ne peut plus être réservé. */
    private function indisponible(ResponseInterface $response, string $message): ResponseInterface
    {
        return $this->renderer->render($response, 'message', [
            'titre' => 'Créneau indisponible',
            'texte' => $message,
            'code' => 409,
            'lien' => '/#salles',
            'lienTexte' => 'Voir les autres créneaux',
        ], 409);
    }
}
