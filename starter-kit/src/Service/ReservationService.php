<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\CodeReservation;
use App\Domain\RegleMetierException;
use App\Domain\SaisieInvalide;
use App\Domain\SaisieReservation;
use App\Repository\ReservationRepository;
use Throwable;

/**
 * Réservation d'un créneau par un groupe, depuis la vitrine (S5).
 * Un créneau = une salle privatisée pour un seul groupe.
 */
final class ReservationService
{
    public function __construct(private ReservationRepository $reservations)
    {
    }

    /**
     * Le créneau à afficher dans le formulaire.
     * @throws RegleMetierException s'il n'existe pas ou n'est plus réservable
     */
    public function creneau(int $idSession): array
    {
        $session = $this->reservations->sessionAReserver($idSession);
        $this->verifierReservable($session);

        return $session;
    }

    /**
     * Réserve le créneau et renvoie le récapitulatif (avec le code de réservation).
     * @throws SaisieInvalide        formulaire incorrect (422)
     * @throws RegleMetierException  créneau pris entre-temps, passé ou annulé (409)
     */
    public function reserver(int $idSession, array $saisie): array
    {
        $this->reservations->demarrerTransaction();

        try {
            // Verrou sur la session : deux groupes ne peuvent pas prendre le même créneau
            $session = $this->reservations->sessionAReserver($idSession, true);
            $this->verifierReservable($session);

            $resultat = SaisieReservation::valider(
                $saisie,
                (int) $session['capacite_min'],
                (int) $session['capacite_max']
            );
            if ($resultat['erreurs'] !== []) {
                throw new SaisieInvalide($resultat['erreurs']);
            }

            $code = $this->nouveauCode();
            $this->reservations->creer($idSession, $code, $resultat['donnees']);
            $this->reservations->valider();
        } catch (Throwable $erreur) {
            $this->reservations->annulerTransaction();
            throw $erreur;
        }

        return $resultat['donnees'] + [
            'code' => $code,
            'nom_salle' => $session['nom_salle'],
            'debut_session' => $session['debut_session'],
            'fin_session' => $session['fin_session'],
        ];
    }

    /** Invariants du socle : on ne réserve ni un créneau passé, ni annulé, ni déjà pris. */
    private function verifierReservable(?array $session): void
    {
        if ($session === null) {
            throw new RegleMetierException('Ce créneau n\'existe pas.');
        }
        if (!in_array($session['etat_session'], ['PREVUE', 'PRETE'], true) || (int) $session['a_venir'] !== 1) {
            throw new RegleMetierException('Ce créneau n\'est plus proposé à la réservation.');
        }
        if ((int) $session['nb_confirmees'] > 0) {
            throw new RegleMetierException('Ce créneau vient d\'être réservé par un autre groupe.');
        }
    }

    private function nouveauCode(): string
    {
        do {
            $code = CodeReservation::generer();
        } while ($this->reservations->codeExiste($code));

        return $code;
    }
}
