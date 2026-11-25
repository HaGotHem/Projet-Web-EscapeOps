<?php

declare(strict_types=1);

namespace App\Repository;

use Medoo\Medoo;
use PDO;

/**
 * Accès aux incidents déclarés sur les salles. Lecture et écriture, sans règle métier.
 */
final class IncidentRepository
{
    private const SELECT_INCIDENT = "
        SELECT i.id_incident, i.titre_incident, i.description_incident, i.gravite_incident,
               i.statut_incident, i.declare_le, i.resolu_le,
               i.id_salle, sa.nom_salle,
               i.id_equipement, e.nom_equipement,
               i.id_declarant, u.prenom_utilisateur, u.nom_utilisateur
          FROM incident i
          JOIN salle sa ON sa.id_salle = i.id_salle
          LEFT JOIN equipement e ON e.id_equipement = i.id_equipement
          JOIN utilisateur u ON u.id_utilisateur = i.id_declarant
    ";

    public function __construct(private Medoo $database)
    {
    }

    /** Les incidents, les ouverts d'abord, puis du plus récent au plus ancien. */
    public function liste(): array
    {
        return $this->database
            ->query(self::SELECT_INCIDENT . " ORDER BY i.statut_incident = 'RESOLU', i.declare_le DESC, i.id_incident DESC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trouver(int $idIncident): ?array
    {
        $ligne = $this->database
            ->query(self::SELECT_INCIDENT . ' WHERE i.id_incident = :id', [':id' => $idIncident])
            ->fetch(PDO::FETCH_ASSOC);

        return is_array($ligne) ? $ligne : null;
    }

    /** L'équipement est-il affecté à la salle aujourd'hui (affectation sans date de fin) ? */
    public function equipementDansSalle(int $idEquipement, int $idSalle): bool
    {
        return $this->database->has('affecter', [
            'id_equipement' => $idEquipement,
            'id_salle' => $idSalle,
            'date_fin_affectation' => null,
        ]);
    }

    /** Enregistre un incident OUVERT, déclaré à l'heure de la base. Renvoie son identifiant. */
    public function creer(array $incident, int $idDeclarant): int
    {
        $this->database->query(
            "INSERT INTO incident
                (titre_incident, description_incident, gravite_incident, statut_incident,
                 declare_le, id_salle, id_equipement, id_declarant)
             VALUES (:titre, :description, :gravite, 'OUVERT', NOW(), :salle, :equipement, :declarant)",
            [
                ':titre' => $incident['titre'],
                ':description' => $incident['description'],
                ':gravite' => $incident['gravite'],
                ':salle' => $incident['id_salle'],
                ':equipement' => $incident['id_equipement'],
                ':declarant' => $idDeclarant,
            ]
        );

        return (int) $this->database->id();
    }
}
