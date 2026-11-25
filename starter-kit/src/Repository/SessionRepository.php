<?php

declare(strict_types=1);

namespace App\Repository;

use Medoo\Medoo;
use PDO;

/**
 * Accès aux sessions de jeu (S5). Lecture et écriture, sans règle métier.
 */
final class SessionRepository
{
    /** Colonnes communes : la session, sa salle, son game master et sa réservation confirmée. */
    private const SELECT_SESSION = "
        SELECT s.id_session, s.debut_session, s.fin_session, s.etat_session,
               s.id_salle, sa.nom_salle, sa.duree_jeu_min, sa.remise_etat_min,
               sa.capacite_min, sa.capacite_max,
               s.id_game_master, gm.prenom_utilisateur AS gm_prenom, gm.nom_utilisateur AS gm_nom,
               r.code_reservation, r.nom_groupe, r.nb_joueurs,
               r.contact_nom, r.contact_email, r.contact_tel, r.reservee_le
          FROM session s
          JOIN salle sa ON sa.id_salle = s.id_salle
          LEFT JOIN utilisateur gm ON gm.id_utilisateur = s.id_game_master
          LEFT JOIN reservation r ON r.id_session = s.id_session
                                 AND r.statut_reservation = 'CONFIRMEE'
    ";

    public function __construct(private Medoo $database)
    {
    }

    /** L'heure de référence est celle de la base, comme pour le seed et la vitrine. */
    public function maintenant(): string
    {
        return (string) $this->database->query('SELECT NOW()')->fetchColumn();
    }

    /** Sessions d'aujourd'hui et des $jours jours suivants, toutes salles, par heure. */
    public function planning(int $jours): array
    {
        $sql = self::SELECT_SESSION . "
            WHERE s.debut_session >= CURDATE()
              AND s.debut_session < CURDATE() + INTERVAL :jours DAY
            ORDER BY s.debut_session, sa.id_salle";

        return $this->database->query($sql, [':jours' => $jours])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouver(int $idSession): ?array
    {
        $ligne = $this->database
            ->query(self::SELECT_SESSION . ' WHERE s.id_session = :id', [':id' => $idSession])
            ->fetch(PDO::FETCH_ASSOC);

        return is_array($ligne) ? $ligne : null;
    }

    /** Sessions d'un game master, d'aujourd'hui et des $jours jours suivants. */
    public function parGameMaster(int $idGameMaster, int $jours): array
    {
        $sql = self::SELECT_SESSION . "
            WHERE s.id_game_master = :gm
              AND s.debut_session >= CURDATE()
              AND s.debut_session < CURDATE() + INTERVAL :jours DAY
            ORDER BY s.debut_session";

        return $this->database->query($sql, [':gm' => $idGameMaster, ':jours' => $jours])
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Une autre session (non annulée) de la salle occupe-t-elle déjà ce créneau ?
     * Chaque session bloque la salle de son début jusqu'à sa fin + la remise en état.
     */
    public function salleOccupee(int $idSalle, string $debut, string $fin, int $remiseMin): bool
    {
        $sql = "
            SELECT COUNT(*)
              FROM session s
             WHERE s.id_salle = :salle
               AND s.etat_session <> 'ANNULEE'
               AND s.debut_session < :fin + INTERVAL :remise1 MINUTE
               AND s.fin_session + INTERVAL :remise2 MINUTE > :debut";

        return (int) $this->database->query($sql, [
            ':salle' => $idSalle,
            ':debut' => $debut,
            ':fin' => $fin,
            ':remise1' => $remiseMin,
            ':remise2' => $remiseMin,
        ])->fetchColumn() > 0;
    }

    /** Les game masters actifs, pour les proposer à l'affectation (E5). */
    public function gameMastersActifs(): array
    {
        return $this->database->select(
            'utilisateur',
            ['id_utilisateur', 'prenom_utilisateur', 'nom_utilisateur'],
            [
                'role_utilisateur' => 'GAME_MASTER',
                'actif_utilisateur' => 1,
                'ORDER' => ['prenom_utilisateur' => 'ASC'],
            ]
        );
    }

    /**
     * Les autres sessions non annulées d'un game master autour d'un créneau (± 1 jour).
     * C'est un pré-filtre large : la vraie règle de chevauchement est dans Creneau (E5).
     */
    public function autresSessionsDuGameMaster(int $idGameMaster, int $idSessionExclue, string $debut, string $fin): array
    {
        $sql = "
            SELECT s.id_session, s.debut_session, s.fin_session, sa.nom_salle
              FROM session s
              JOIN salle sa ON sa.id_salle = s.id_salle
             WHERE s.id_game_master = :gm
               AND s.id_session <> :id
               AND s.etat_session <> 'ANNULEE'
               AND s.debut_session < :fin + INTERVAL 1 DAY
               AND s.fin_session > :debut - INTERVAL 1 DAY
             ORDER BY s.debut_session";

        return $this->database->query($sql, [
            ':gm' => $idGameMaster,
            ':id' => $idSessionExclue,
            ':debut' => $debut,
            ':fin' => $fin,
        ])->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Affecte le game master seulement si la session est encore dans l'état lu
     * (même protection que changerEtat). Renvoie false sinon.
     */
    public function affecterGameMaster(int $idSession, int $idGameMaster, string $etatAttendu): bool
    {
        $resultat = $this->database->update(
            'session',
            ['id_game_master' => $idGameMaster],
            ['id_session' => $idSession, 'etat_session' => $etatAttendu]
        );

        return $resultat !== null && $resultat->rowCount() === 1;
    }

    public function creer(int $idSalle, string $debut, string $fin): int
    {
        $this->database->insert('session', [
            'id_salle' => $idSalle,
            'debut_session' => $debut,
            'fin_session' => $fin,
            'etat_session' => 'PREVUE',
        ]);

        return (int) $this->database->id();
    }

    /**
     * Change l'état seulement s'il vaut encore $ancien (deux onglets ouverts ne
     * peuvent pas faire deux transitions contradictoires). Renvoie false sinon.
     *
     * Une partie terminée en avance prend sa vraie heure de fin : le compteur E9
     * compare la fin des sessions à la date du dernier contrôle de la salle.
     */
    public function changerEtat(int $idSession, string $ancien, string $nouveau): bool
    {
        $finReelle = $nouveau === 'TERMINEE'
            ? ', fin_session = GREATEST(debut_session + INTERVAL 1 MINUTE, LEAST(fin_session, NOW()))'
            : '';

        $resultat = $this->database->query(
            "UPDATE session
                SET etat_session = :nouveau{$finReelle}
              WHERE id_session = :id
                AND etat_session = :ancien",
            [':nouveau' => $nouveau, ':id' => $idSession, ':ancien' => $ancien]
        );

        return $resultat !== null && $resultat->rowCount() === 1;
    }
}
