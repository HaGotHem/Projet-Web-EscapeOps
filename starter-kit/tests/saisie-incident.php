<?php

declare(strict_types=1);

/**
 * Tests de la saisie d'un incident (POST /api/incidents), sans SGBD : composer test
 *
 * Une entrée compréhensible mais invalide donne une erreur par champ (HTTP 422).
 */
use App\Domain\SaisieIncident;

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

$salles = [1, 2, 3];
$valide = ['id_salle' => 2, 'titre' => 'La lampe UV clignote', 'gravite' => 'MINEUR'];

// Cas nominal
$r = SaisieIncident::valider($valide, $salles);
verifier($r['erreurs'] === [], 'incident minimal valide');
verifier($r['donnees']['id_salle'] === 2 && $r['donnees']['gravite'] === 'MINEUR', 'données normalisées');
verifier($r['donnees']['description'] === null && $r['donnees']['id_equipement'] === null, 'champs facultatifs à null');

$complet = $valide + ['description' => '  Elle s\'éteint toutes les 30 s.  ', 'id_equipement' => 4];
$r = SaisieIncident::valider($complet, $salles);
verifier($r['erreurs'] === [] && $r['donnees']['description'] === 'Elle s\'éteint toutes les 30 s.', 'description nettoyée');
verifier($r['donnees']['id_equipement'] === 4, 'équipement accepté');
verifier(SaisieIncident::valider(['id_salle' => '3'] + $valide, $salles)['erreurs'] === [], 'identifiant en texte numérique accepté (formulaire HTML)');

// Une erreur par champ
$erreurs = static fn (array $saisie): array => SaisieIncident::valider($saisie, $salles)['erreurs'];
verifier(isset($erreurs(['id_salle' => 9] + $valide)['id_salle']), 'salle inconnue refusée');
verifier(isset($erreurs(['id_salle' => true] + $valide)['id_salle']), 'booléen refusé comme identifiant');
verifier(isset($erreurs(['id_salle' => 2.5] + $valide)['id_salle']), 'décimal refusé comme identifiant');
verifier(isset($erreurs(array_diff_key($valide, ['id_salle' => 0]))['id_salle']), 'salle absente refusée');
verifier(isset($erreurs(['titre' => '  '] + $valide)['titre']), 'titre vide refusé');
verifier(isset($erreurs(['titre' => 'ab'] + $valide)['titre']), 'titre de 2 caractères refusé');
verifier(isset($erreurs(['titre' => str_repeat('é', 151)] + $valide)['titre']), 'titre de 151 caractères refusé');
verifier($erreurs(['titre' => str_repeat('é', 150)] + $valide) === [], 'titre de 150 caractères accepté');
verifier(isset($erreurs(['titre' => ['x']] + $valide)['titre']), 'titre non texte refusé');
verifier(isset($erreurs(['gravite' => 'CRITIQUE'] + $valide)['gravite']), 'gravité hors liste refusée');
verifier(isset($erreurs(['gravite' => 'mineur'] + $valide)['gravite']), 'gravité en minuscules refusée');
verifier(isset($erreurs(['description' => 42] + $valide)['description']), 'description non texte refusée');
verifier(isset($erreurs(['description' => str_repeat('a', 2001)] + $valide)['description']), 'description trop longue refusée');
verifier(isset($erreurs(['id_equipement' => 'lampe'] + $valide)['id_equipement']), 'équipement non numérique refusé');
verifier($erreurs(['id_equipement' => null] + $valide) === [], 'équipement null accepté');

// Plusieurs erreurs à la fois
verifier(count($erreurs([])) === 3, 'corps vide : 3 erreurs (salle, titre, gravité)');

echo "Saisie d'incident : {$total} vérifications vertes.\n";
