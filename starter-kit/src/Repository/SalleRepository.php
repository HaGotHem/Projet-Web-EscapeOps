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
