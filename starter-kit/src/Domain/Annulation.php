<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

/**
 * Règle E7 — « Annuler au bon moment ».
 *
 * Une annulation faite au moins 24 heures avant le début est SANS_FRAIS, sinon TARDIVE.
 * Aucun paiement n'est traité : le statut informe le responsable et le client.
 *
 * On compare des timestamps : ce sont 24 heures réelles, même la nuit où l'heure change.
 * 24 h pile reste SANS_FRAIS (« au moins 24 heures »).
 */
final class Annulation
{
    public const SANS_FRAIS = 'SANS_FRAIS';
    public const TARDIVE = 'TARDIVE';
    public const DELAI_SANS_FRAIS_HEURES = 24;

    public static function classer(DateTimeImmutable $maintenant, DateTimeImmutable $debut): string
    {
        $secondesAvant = $debut->getTimestamp() - $maintenant->getTimestamp();

        return $secondesAvant >= self::DELAI_SANS_FRAIS_HEURES * 3600 ? self::SANS_FRAIS : self::TARDIVE;
    }

    /** Heures entières restant avant le début (pour l'affichage), jamais négatif. */
    public static function heuresAvant(DateTimeImmutable $maintenant, DateTimeImmutable $debut): int
    {
        return max(0, intdiv($debut->getTimestamp() - $maintenant->getTimestamp(), 3600));
    }

    /** Libellé du statut, tel qu'il est affiché (« Sans frais », « Tardive »). */
    public static function libelle(string $statut): string
    {
        return [self::SANS_FRAIS => 'Sans frais', self::TARDIVE => 'Tardive'][$statut] ?? $statut;
    }
}
