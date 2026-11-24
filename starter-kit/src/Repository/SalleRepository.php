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
     * Créneaux publiés : sessions PREVUE ou PRETE qui n'ont pas encore commencé.
     * Pour chacune, on compte ses réservations confirmées (0 = libre, 1 = réservé).
     */
    public function creneauxPublies(): array
    {
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
             ORDER BY s.id_salle, s.debut_session
        ";

        return $this->database->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
