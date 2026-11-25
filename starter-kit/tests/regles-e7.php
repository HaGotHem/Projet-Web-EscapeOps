<?php

declare(strict_types=1);

/**
 * Tests de la règle E7 (classement d'une annulation), sans SGBD : composer test
 *
 * Au moins 24 h avant le début : SANS_FRAIS. Sinon : TARDIVE.
 */
use App\Domain\Annulation;

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

$paris = new DateTimeZone('Europe/Paris');
$debut = new DateTimeImmutable('2026-11-27 18:00:00', $paris);
$avant = static fn (string $duree): DateTimeImmutable => $debut->modify('-' . $duree);

// Les trois cas de la démo et du sujet
verifier(Annulation::classer($avant('26 hours'), $debut) === 'SANS_FRAIS', '26 h avant : SANS_FRAIS');
verifier(Annulation::classer($avant('24 hours'), $debut) === 'SANS_FRAIS', '24 h pile avant : SANS_FRAIS (cas limite)');
verifier(Annulation::classer($avant('2 hours'), $debut) === 'TARDIVE', '2 h avant : TARDIVE');

// Autour de la frontière, à la seconde près
verifier(Annulation::classer($avant('86399 seconds'), $debut) === 'TARDIVE', '23 h 59 min 59 s avant : TARDIVE');
verifier(Annulation::classer($avant('86401 seconds'), $debut) === 'SANS_FRAIS', '24 h 00 min 01 s avant : SANS_FRAIS');

// Très tôt ou au dernier moment
verifier(Annulation::classer($avant('30 days'), $debut) === 'SANS_FRAIS', 'un mois avant : SANS_FRAIS');
verifier(Annulation::classer($debut, $debut) === 'TARDIVE', 'à l\'heure du début : TARDIVE');

// 24 h réelles, même quand l'heure change (passage à l'heure d'été le 29/03/2026 à 2 h)
$debutEte = new DateTimeImmutable('2026-03-29 18:00:00', $paris);
verifier(
    Annulation::classer(new DateTimeImmutable('2026-03-28 18:00:00', $paris), $debutEte) === 'TARDIVE',
    'veille à la même heure, jour de 23 h : seulement 23 h réelles, donc TARDIVE'
);
verifier(
    Annulation::classer(new DateTimeImmutable('2026-03-28 17:00:00', $paris), $debutEte) === 'SANS_FRAIS',
    'veille une heure plus tôt : 24 h réelles, donc SANS_FRAIS'
);

// Délai affiché sur la page de confirmation
verifier(Annulation::heuresAvant($avant('26 hours'), $debut) === 26, 'délai affiché : 26 h');
verifier(Annulation::heuresAvant($avant('90 minutes'), $debut) === 1, 'délai affiché : 1 h (heures entières)');

echo "Règle E7 : {$total} vérifications vertes.\n";
