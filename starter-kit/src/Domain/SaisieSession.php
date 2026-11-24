<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

/**
 * Contrôle de la saisie « nouvelle session » du responsable (S5).
 * Ne touche pas à la base : les vérifications qui demandent la base
 * (début dans le futur, salle libre) sont faites par SessionService.
 */
final class SaisieSession
{
    /**
     * @param array $saisie  corps du formulaire ($_POST)
     * @param int[] $salles  identifiants des salles existantes
     * @return array{donnees: array, erreurs: array<string, string>}
     */
    public static function valider(array $saisie, array $salles): array
    {
        $erreurs = [];
        $donnees = [];

        $idSalle = filter_var($saisie['id_salle'] ?? null, FILTER_VALIDATE_INT);
        if ($idSalle === false || !in_array($idSalle, $salles, true)) {
            $erreurs['id_salle'] = 'Choisissez une salle de la liste.';
        } else {
            $donnees['id_salle'] = $idSalle;
        }

        // Format envoyé par <input type="datetime-local"> : 2026-11-28T18:00
        $brut = $saisie['debut'] ?? null;
        $debut = is_string($brut) ? DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $brut) : false;
        // createFromFormat accepte « 31 février » en le décalant : on vérifie qu'il relit la même chaîne
        if ($debut === false || $debut->format('Y-m-d\TH:i') !== $brut) {
            $erreurs['debut'] = 'Indiquez une date et une heure de début valides.';
        } else {
            $donnees['debut'] = $debut;
        }

        return ['donnees' => $donnees, 'erreurs' => $erreurs];
    }

    /** La fin d'une session se déduit de la durée de jeu de la salle. */
    public static function fin(DateTimeImmutable $debut, int $dureeMinutes): DateTimeImmutable
    {
        return $debut->modify('+' . $dureeMinutes . ' minutes');
    }
}
