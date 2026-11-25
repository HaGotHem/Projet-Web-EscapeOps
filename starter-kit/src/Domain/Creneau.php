<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

/**
 * Règle E5 : deux créneaux se chevauchent-ils ?
 *
 * A chevauche B si A commence avant la fin de B ET finit après le début de B.
 * Les comparaisons sont strictes : 18 h–19 h et 19 h–20 h sont jointifs,
 * ils ne se chevauchent pas (un game master peut enchaîner les deux).
 */
final class Creneau
{
    public static function chevauche(
        DateTimeImmutable $debutA,
        DateTimeImmutable $finA,
        DateTimeImmutable $debutB,
        DateTimeImmutable $finB
    ): bool {
        return $debutA < $finB && $finA > $debutB;
    }
}