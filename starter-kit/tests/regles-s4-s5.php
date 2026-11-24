<?php

declare(strict_types=1);

/**
 * Tests des règles S4 / S5, sans SGBD : composer test
 *
 * - états d'une session : quelles transitions sont permises ;
 * - saisie d'une session par le responsable ;
 * - saisie d'une réservation de groupe ;
 * - code de réservation ;
 * - jeton CSRF des formulaires.
 */
use App\Domain\CodeReservation;
use App\Domain\EtatSession;
use App\Domain\SaisieReservation;
use App\Domain\SaisieSession;
use App\Http\Csrf;

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

// --- États d'une session ----------------------------------------------------

verifier(EtatSession::peutPasser('PREVUE', 'PRETE'), 'PREVUE → PRETE permis');
verifier(EtatSession::peutPasser('PREVUE', 'ANNULEE'), 'PREVUE → ANNULEE permis');
verifier(EtatSession::peutPasser('PRETE', 'EN_COURS'), 'PRETE → EN_COURS permis');
verifier(EtatSession::peutPasser('PRETE', 'ANNULEE'), 'PRETE → ANNULEE permis');
verifier(EtatSession::peutPasser('EN_COURS', 'TERMINEE'), 'EN_COURS → TERMINEE permis');

verifier(!EtatSession::peutPasser('PREVUE', 'EN_COURS'), 'PREVUE → EN_COURS refusé (la salle doit être prête)');
verifier(!EtatSession::peutPasser('PREVUE', 'TERMINEE'), 'PREVUE → TERMINEE refusé');
verifier(!EtatSession::peutPasser('EN_COURS', 'ANNULEE'), 'une partie commencée ne s\'annule pas');
verifier(!EtatSession::peutPasser('TERMINEE', 'PREVUE'), 'TERMINEE est un état final');
verifier(!EtatSession::peutPasser('ANNULEE', 'PREVUE'), 'ANNULEE est un état final');
verifier(!EtatSession::peutPasser('PREVUE', 'PREVUE'), 'pas de transition vers le même état');
verifier(!EtatSession::peutPasser('INCONNU', 'PRETE'), 'état inconnu refusé');
verifier(EtatSession::transitionsDepuis('TERMINEE') === [], 'aucune transition depuis TERMINEE');
verifier(EtatSession::libelle('EN_COURS') === 'En cours', 'libellé lisible');

// --- Saisie d'une session (responsable) -------------------------------------

$salles = [1, 2, 3];

$ok = SaisieSession::valider(['id_salle' => '2', 'debut' => '2026-11-28T18:00'], $salles);
verifier($ok['erreurs'] === [], 'saisie de session valide acceptée');
verifier($ok['donnees']['id_salle'] === 2, 'id de salle converti en entier');
verifier($ok['donnees']['debut']->format('Y-m-d H:i') === '2026-11-28 18:00', 'début lu depuis datetime-local');

$ko = SaisieSession::valider(['id_salle' => '9', 'debut' => '2026-11-28T18:00'], $salles);
verifier(isset($ko['erreurs']['id_salle']), 'salle inexistante refusée');

$ko = SaisieSession::valider(['id_salle' => '1', 'debut' => '28/11/2026 18h'], $salles);
verifier(isset($ko['erreurs']['debut']), 'date mal formée refusée');

$ko = SaisieSession::valider(['id_salle' => '1', 'debut' => '2026-02-31T18:00'], $salles);
verifier(isset($ko['erreurs']['debut']), 'date impossible (31 février) refusée');

$ko = SaisieSession::valider([], $salles);
verifier(isset($ko['erreurs']['id_salle'], $ko['erreurs']['debut']), 'champs absents refusés');

$fin = SaisieSession::fin(new DateTimeImmutable('2026-11-28 18:00'), 75);
verifier($fin->format('H:i') === '19:15', 'fin = début + durée de jeu de la salle');

// --- Saisie d'une réservation (groupe) --------------------------------------

$saisie = [
    'nom_groupe' => '  Les Feux Follets ',
    'nb_joueurs' => '5',
    'contact_nom' => 'Inès Garnier',
    'contact_email' => 'ines.garnier@exemple.fr',
    'contact_tel' => '',
    'conditions' => '1',
];

$ok = SaisieReservation::valider($saisie, 2, 6);
verifier($ok['erreurs'] === [], 'réservation valide acceptée');
verifier($ok['donnees']['nom_groupe'] === 'Les Feux Follets', 'nom de groupe nettoyé (trim)');
verifier($ok['donnees']['nb_joueurs'] === 5, 'nombre de joueurs converti en entier');
verifier($ok['donnees']['contact_tel'] === null, 'téléphone facultatif : vide → null');

$cas = [
    'nom de groupe vide' => ['nom_groupe' => '   '],
    'nom de groupe trop long' => ['nom_groupe' => str_repeat('a', 101)],
    'moins de joueurs que le minimum' => ['nb_joueurs' => '1'],
    'plus de joueurs que la capacité' => ['nb_joueurs' => '7'],
    'nombre de joueurs non numérique' => ['nb_joueurs' => 'cinq'],
    'email invalide' => ['contact_email' => 'ines@'],
    'contact vide' => ['contact_nom' => ''],
    'téléphone fantaisiste' => ['contact_tel' => '<script>'],
    'conditions non acceptées' => ['conditions' => null],
    'champ envoyé en tableau' => ['nom_groupe' => ['x']],
];

foreach ($cas as $label => $modif) {
    $r = SaisieReservation::valider(array_merge($saisie, $modif), 2, 6);
    verifier($r['erreurs'] !== [], 'refusé : ' . $label);
}

$r = SaisieReservation::valider(array_merge($saisie, ['contact_tel' => '06 12 34 56 78']), 2, 6);
verifier($r['donnees']['contact_tel'] === '06 12 34 56 78', 'téléphone valide conservé');

// --- Code de réservation ------------------------------------------------------

$codes = [];
for ($i = 0; $i < 200; $i++) {
    $code = CodeReservation::generer();
    verifier(preg_match('/^AO-[A-HJ-NP-Z2-9]{8}$/', $code) === 1, 'format AO-XXXXXXXX sans caractère ambigu');
    $codes[$code] = true;
}
verifier(count($codes) === 200, 'codes tirés au hasard, sans doublon sur 200 tirages');
verifier(strlen(CodeReservation::generer()) <= 12, 'tient dans VARCHAR(12)');

// --- Jeton CSRF -------------------------------------------------------------

$_SESSION = [];
$jeton = Csrf::jeton();
verifier(strlen($jeton) === 64, 'jeton de 32 octets en hexadécimal');
verifier(Csrf::jeton() === $jeton, 'même jeton pendant toute la session');
verifier(Csrf::estValide($jeton), 'bon jeton accepté');
verifier(!Csrf::estValide('faux'), 'mauvais jeton refusé');
verifier(!Csrf::estValide(null), 'jeton absent refusé');
verifier(!Csrf::estValide(['tableau']), 'jeton envoyé en tableau refusé');
$_SESSION = [];
verifier(!Csrf::estValide($jeton), 'jeton d\'une autre session refusé');

echo "Règles S4/S5 : $total vérifications vertes.\n";
