<?php

declare(strict_types=1);

/**
 * Tests de la règle E9 (contrôle de maintenance après 10 parties), sans SGBD : composer test
 *
 * On compte les sessions TERMINEE d'une salle finies après son dernier contrôle.
 * À 10 : CONTROLE_A_PREVOIR. Les sessions annulées ne comptent pas.
 */
use App\Domain\Maintenance;

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

/** $n sessions dans l'état donné, une par jour à partir du 1er novembre. */
$sessions = static function (int $n, string $etat, int $decalage = 0): array {
    $liste = [];
    for ($i = 0; $i < $n; $i++) {
        $jour = sprintf('2026-11-%02d', 1 + $decalage + $i);
        $liste[] = ['etat_session' => $etat, 'fin_session' => $jour . ' 19:00:00'];
    }

    return $liste;
};

// 9 / 10
$neuf = $sessions(9, 'TERMINEE');
verifier(Maintenance::compter($neuf, null) === 9, '9 sessions terminées : compteur 9');
verifier(!Maintenance::controleAPrevoir(9), '9/10 : pas de badge');

$dix = $sessions(10, 'TERMINEE');
verifier(Maintenance::compter($dix, null) === 10, '10 sessions terminées : compteur 10');
verifier(Maintenance::controleAPrevoir(10), '10/10 : CONTROLE_A_PREVOIR');
verifier(Maintenance::controleAPrevoir(11), '11/10 : toujours CONTROLE_A_PREVOIR tant qu\'on n\'a pas contrôlé');

// Les sessions annulées (et celles pas encore terminées) ne comptent pas
$avecAnnulee = array_merge($neuf, $sessions(1, 'ANNULEE', 20));
verifier(Maintenance::compter($avecAnnulee, null) === 9, '9 terminées + 1 annulée : compteur 9, pas de badge');
$avecEnCours = array_merge($neuf, $sessions(1, 'EN_COURS', 20), $sessions(1, 'PREVUE', 21));
verifier(Maintenance::compter($avecEnCours, null) === 9, 'sessions en cours ou prévues non comptées');

// Un contrôle remet le compteur à zéro : seules les sessions finies après lui comptent
$controle = new DateTimeImmutable('2026-11-10 23:00:00');
verifier(Maintenance::compter($dix, $controle) === 0, 'contrôle après la 10e session : compteur 0');
verifier(!Maintenance::controleAPrevoir(Maintenance::compter($dix, $controle)), 'après le contrôle : badge retiré');
$apres = array_merge($dix, $sessions(3, 'TERMINEE', 15));
verifier(Maintenance::compter($apres, $controle) === 3, '3 sessions après le contrôle : compteur 3');

// Session finie à la minute exacte du contrôle : déjà vérifiée par le contrôle, non comptée
$pile = [['etat_session' => 'TERMINEE', 'fin_session' => '2026-11-10 23:00:00']];
verifier(Maintenance::compter($pile, $controle) === 0, 'session finie à l\'heure du contrôle : non comptée');

echo "Règle E9 : {$total} vérifications vertes.\n";
