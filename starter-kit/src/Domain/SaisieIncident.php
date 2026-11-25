<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Contrôle de la saisie d'un incident (API JSON, puis formulaire du game master).
 * Ne touche pas à la base : la présence de l'équipement dans la salle est
 * vérifiée par IncidentService.
 */
final class SaisieIncident
{
    public const GRAVITES = ['MINEUR', 'BLOQUANT'];
    private const TITRE_MIN = 3;
    private const TITRE_MAX = 150;
    private const DESCRIPTION_MAX = 2000;

    /**
     * @param array $saisie corps JSON décodé ou formulaire
     * @param int[] $salles identifiants des salles existantes
     * @return array{donnees: array, erreurs: array<string, string>}
     */
    public static function valider(array $saisie, array $salles): array
    {
        $erreurs = [];
        $donnees = [];

        $idSalle = self::entier($saisie['id_salle'] ?? null);
        if ($idSalle === null || !in_array($idSalle, $salles, true)) {
            $erreurs['id_salle'] = 'Indiquez une salle existante (identifiant entier).';
        } else {
            $donnees['id_salle'] = $idSalle;
        }

        $titre = is_string($saisie['titre'] ?? null) ? trim($saisie['titre']) : null;
        if ($titre === null || mb_strlen($titre) < self::TITRE_MIN || mb_strlen($titre) > self::TITRE_MAX) {
            $erreurs['titre'] = sprintf('Le titre est un texte de %d à %d caractères.', self::TITRE_MIN, self::TITRE_MAX);
        } else {
            $donnees['titre'] = $titre;
        }

        $gravite = $saisie['gravite'] ?? null;
        if (!is_string($gravite) || !in_array($gravite, self::GRAVITES, true)) {
            $erreurs['gravite'] = 'La gravité vaut MINEUR ou BLOQUANT.';
        } else {
            $donnees['gravite'] = $gravite;
        }

        $description = $saisie['description'] ?? null;
        if ($description !== null && !is_string($description)) {
            $erreurs['description'] = 'La description est un texte.';
        } elseif (is_string($description) && mb_strlen(trim($description)) > self::DESCRIPTION_MAX) {
            $erreurs['description'] = sprintf('La description est limitée à %d caractères.', self::DESCRIPTION_MAX);
        } else {
            $description = is_string($description) ? trim($description) : '';
            $donnees['description'] = $description === '' ? null : $description;
        }

        $equipement = $saisie['id_equipement'] ?? null;
        if ($equipement === null || $equipement === '') {
            $donnees['id_equipement'] = null;
        } elseif (($idEquipement = self::entier($equipement)) === null) {
            $erreurs['id_equipement'] = 'L\'équipement est un identifiant entier, ou null.';
        } else {
            $donnees['id_equipement'] = $idEquipement;
        }

        return ['donnees' => $donnees, 'erreurs' => $erreurs];
    }

    /** Entier JSON, ou texte de chiffres (formulaire HTML). Ni booléen, ni décimal. */
    private static function entier(mixed $valeur): ?int
    {
        if (is_int($valeur)) {
            return $valeur;
        }
        if (is_string($valeur) && preg_match('/^[1-9][0-9]{0,8}$/', $valeur) === 1) {
            return (int) $valeur;
        }

        return null;
    }
}
