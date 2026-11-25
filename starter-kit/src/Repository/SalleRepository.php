<?php

declare(strict_types=1);

namespace App\Repository;

use Medoo\Medoo;
use PDO;

/**
 * Accès aux données des salles et de leurs créneaux.
 * Le repository lit la base ; il ne décide rien (pas de règle métier ici).
 */
final class SalleRepository
{
    public function __construct(private Medoo $database)
    {
    }

    /**
     * Toutes les salles, de la première à la dernière.
     */
    public function all(): array
    {
        return $this->database->select(
            'salle',
            [
                'id_salle',
                'nom_salle',
                'theme_salle',
                'description_salle',
                'duree_jeu_min',
                'capacite_min',
                'capacite_max',
                'remise_etat_min',
            ],
            ['ORDER' => ['id_salle' => 'ASC']]
        );
    }

    /**
     * Une salle par son identifiant, ou null si elle n'existe pas.
     */
    public function trouver(int $idSalle): ?array
    {
        $salle = $this->database->get(
            'salle',
            [
                'id_salle',
                'nom_salle',
                'theme_salle',
                'description_salle',
                'duree_jeu_min',
                'capacite_min',
                'capacite_max',
            ],
            ['id_salle' => $idSalle]
        );

        return is_array($salle) ? $salle : null;
    }

    /** E9 : les contrôles de maintenance d'une salle, le plus récent d'abord, avec leur auteur. */
    public function controles(int $idSalle): array
    {
        $sql = "
            SELECT c.id_controle, c.controle_le, c.commentaire_controle,
                   u.prenom_utilisateur, u.nom_utilisateur
              FROM controle_maintenance c
              JOIN utilisateur u ON u.id_utilisateur = c.id_responsable
             WHERE c.id_salle = :salle
             ORDER BY c.controle_le DESC, c.id_controle DESC";

        return $this->database->query($sql, [':salle' => $idSalle])->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * E9 : sessions terminées ou annulées de la salle, finies après $depuis (toutes si null).
     * Le repository ne compte pas : c'est Maintenance::compter() qui applique la règle.
     */
    public function sessionsFinies(int $idSalle, ?string $depuis): array
    {
        $filtre = $depuis === null ? '' : 'AND s.fin_session > :depuis';
        $parametres = [':salle' => $idSalle] + ($depuis === null ? [] : [':depuis' => $depuis]);

        $sql = "
            SELECT s.id_session, s.debut_session, s.fin_session, s.etat_session, r.nom_groupe
              FROM session s
              LEFT JOIN reservation r ON r.id_session = s.id_session
                                     AND r.statut_reservation = 'CONFIRMEE'
             WHERE s.id_salle = :salle
               AND s.etat_session IN ('TERMINEE', 'ANNULEE')
               {$filtre}
             ORDER BY s.debut_session";

        return $this->database->query($sql, $parametres)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** E9 : enregistre un contrôle à l'heure de la base ; il remet le compteur de la salle à zéro. */
    public function enregistrerControle(int $idSalle, int $idResponsable, ?string $commentaire): void
    {
        $this->database->query(
            'INSERT INTO controle_maintenance (controle_le, commentaire_controle, id_salle, id_responsable)
             VALUES (NOW(), :commentaire, :salle, :responsable)',
            [':commentaire' => $commentaire, ':salle' => $idSalle, ':responsable' => $idResponsable]
        );
    }

    /**
     * Créneaux publiés : sessions PREVUE ou PRETE qui n'ont pas encore commencé.
     * Pour chacune, on compte ses réservations confirmées (0 = libre, 1 = réservé).
     * Avec $idSalle, on ne garde que les créneaux de cette salle (fiche salle).
     */
    public function creneauxPublies(?int $idSalle = null): array
    {
        $filtreSalle = $idSalle === null ? '' : 'AND s.id_salle = :salle';
        $parametres = $idSalle === null ? [] : [':salle' => $idSalle];

        $sql = "
            SELECT s.id_session,
                   s.id_salle,
                   s.debut_session,
                   s.fin_session,
                   (SELECT COUNT(*)
                      FROM reservation r
                     WHERE r.id_session = s.id_session
                       AND r.statut_reservation = 'CONFIRMEE') AS nb_confirmees
              FROM session s
             WHERE s.etat_session IN ('PREVUE', 'PRETE')
               AND s.debut_session > NOW()
               {$filtreSalle}
             ORDER BY s.id_salle, s.debut_session
        ";

        return $this->database->query($sql, $parametres)->fetchAll(PDO::FETCH_ASSOC);
    }
}
