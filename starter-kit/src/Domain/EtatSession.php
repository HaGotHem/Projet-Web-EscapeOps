<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Cycle de vie d'une session (S5).
 *
 *   PREVUE ──► PRETE ──► EN_COURS ──► TERMINEE
 *     │          │
 *     └──────────┴──► ANNULEE
 *
 * TERMINEE et ANNULEE sont des états finaux. Une partie commencée ne s'annule pas.
 * Le passage à EN_COURS exige en plus un game master (règle E5, vérifiée par le service).
 */
final class EtatSession
{
    private const TRANSITIONS = [
        'PREVUE' => ['PRETE', 'ANNULEE'],
        'PRETE' => ['EN_COURS', 'ANNULEE'],
        'EN_COURS' => ['TERMINEE'],
        'TERMINEE' => [],
        'ANNULEE' => [],
    ];

    private const LIBELLES = [
        'PREVUE' => 'Prévue',
        'PRETE' => 'Prête',
        'EN_COURS' => 'En cours',
        'TERMINEE' => 'Terminée',
        'ANNULEE' => 'Annulée',
    ];

    /** Libellé du bouton qui mène à chaque état. */
    private const ACTIONS = [
        'PRETE' => 'Marquer prête',
        'EN_COURS' => 'Démarrer',
        'TERMINEE' => 'Terminer',
        'ANNULEE' => 'Annuler la session',
    ];

    public static function transitionsDepuis(string $etat): array
    {
        return self::TRANSITIONS[$etat] ?? [];
    }

    public static function peutPasser(string $de, string $vers): bool
    {
        return in_array($vers, self::transitionsDepuis($de), true);
    }

    public static function libelle(string $etat): string
    {
        return self::LIBELLES[$etat] ?? $etat;
    }

    public static function action(string $etat): string
    {
        return self::ACTIONS[$etat] ?? $etat;
    }
}
