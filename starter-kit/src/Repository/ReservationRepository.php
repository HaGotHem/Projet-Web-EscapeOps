<?php

declare(strict_types=1);

namespace App\Repository;

use Medoo\Medoo;
use PDO;

/**
 * Accès aux réservations de groupe (S5).
 */
final class ReservationRepository
{
    public function __construct(private Medoo $database)
    {
    }

    /**
     * Ce qu'il faut savoir d'une session pour la réserver.
     * Avec $verrouiller = true (dans une transaction), la ligne de la session est
     * verrouillée (FOR UPDATE) : deux groupes qui réservent en même temps passent
     * l'un après l'autre, et le second voit la réservation du premier.
     */
    public function sessionAReserver(int $idSession, bool $verrouiller = false): ?array
    {
        $sql = "
            SELECT s.id_session, s.debut_session, s.fin_session, s.etat_session,
                   s.debut_session > NOW() AS a_venir,
                   sa.id_salle, sa.nom_salle, sa.duree_jeu_min, sa.capacite_min, sa.capacite_max,
                   (SELECT COUNT(*) FROM reservation r
                     WHERE r.id_session = s.id_session
                       AND r.statut_reservation = 'CONFIRMEE') AS nb_confirmees
              FROM session s
              JOIN salle sa ON sa.id_salle = s.id_salle
             WHERE s.id_session = :id" . ($verrouiller ? ' FOR UPDATE' : '');

        $ligne = $this->database->query($sql, [':id' => $idSession])->fetch(PDO::FETCH_ASSOC);

        return is_array($ligne) ? $ligne : null;
    }

    public function codeExiste(string $code): bool
    {
        return $this->database->has('reservation', ['code_reservation' => $code]);
    }

    /** Enregistre une réservation CONFIRMEE ; la date de réservation est celle de la base. */
    public function creer(int $idSession, string $code, array $groupe): void
    {
        $this->database->query(
            "INSERT INTO reservation
                (code_reservation, nom_groupe, nb_joueurs, contact_nom, contact_email,
                 contact_tel, statut_reservation, reservee_le, id_session)
             VALUES (:code, :groupe, :nb, :contact, :email, :tel, 'CONFIRMEE', NOW(), :session)",
            [
                ':code' => $code,
                ':groupe' => $groupe['nom_groupe'],
                ':nb' => $groupe['nb_joueurs'],
                ':contact' => $groupe['contact_nom'],
                ':email' => $groupe['contact_email'],
                ':tel' => $groupe['contact_tel'],
                ':session' => $idSession,
            ]
        );
    }

    public function demarrerTransaction(): void
    {
        $this->database->pdo->beginTransaction();
    }

    public function valider(): void
    {
        $this->database->pdo->commit();
    }

    public function annulerTransaction(): void
    {
        if ($this->database->pdo->inTransaction()) {
            $this->database->pdo->rollBack();
        }
    }
}
