<?php

declare(strict_types=1);

/**
 * Tests de la règle E5 (chevauchement de créneaux), sans SGBD : composer test
 */
use App\Domain\Creneau;

require dirname(__DIR__) . '/vendor/autoload.php';

$total = 0;

function verifier(bool $condition, string $label): void
{
    global $total;
    $total++;
    if (!$condition) {
        throw new RuntimeException('Échec : ' . $label);
    }
}

$d = static fn (string $h): DateTimeImmutable => new DateTimeImmutable('2026-11-25 ' . $h);

verifier(!Creneau::chevauche($d('18:00'), $d('19:00'), $d('19:00'), $d('20:00')), 'créneaux jointifs acceptés');

verifier(Creneau::chevauche($d('18:00'), $d('19:00'), $d('18:00'), $d('19:00')), 'créneaux identiques refusés');
verifier(Creneau::chevauche($d('18:00'), $d('19:00'), $d('18:30'), $d('19:30')), 'créneaux partiellement chevauchants refusés');
verifier(!Creneau::chevauche($d('17:00'), $d('18:00'), $d('19:00'), $d('20:00')), 'créneaux séparés acceptés');

// L'ordre des deux créneaux ne change rien
verifier(Creneau::chevauche($d('18:30'), $d('19:30'), $d('18:00'), $d('19:00')), 'chevauchement détecté dans les deux sens');
verifier(!Creneau::chevauche($d('19:00'), $d('20:00'), $d('18:00'), $d('19:00')), 'jointif accepté dans les deux sens');
// Un créneau inclus dans un autre
verifier(Creneau::chevauche($d('17:00'), $d('20:00'), $d('18:00'), $d('19:00')), 'créneau inclus refusé');

echo "Règle E5 : {$total} vérifications vertes.\n";