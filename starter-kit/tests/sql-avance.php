<?php

declare(strict_types=1);

/**
 * Tests du SQL avancé (Q1) sur la vraie base : composer test-bdd
 *
 * Il faut une base construite avec 001 à 004 puis le seed (database/README.md).
 * Tout se passe dans une transaction annulée à la fin : la base n'est pas modifiée.
 *
 * - vues v_planning et v_tableau_bord : présentes et lues par les repositories ;
 * - procédure sp_bilan_salle : même compteur E9 que Maintenance::compter() ;
 * - trigger trg_session_historique : une ligne par changement d'état, rien sinon.
 */
use App\Database\DatabaseFactory;
use App\Domain\Maintenance;
use App\Repository\SalleRepository;
use App\Repository\SessionRepository;
use Dotenv\Dotenv;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

Dotenv::createImmutable($root)->safeLoad();
$settings = require $root . '/config/settings.php';
$database = DatabaseFactory::create($settings['database']);
$pdo = $database->pdo;

$salles = new SalleRepository($database);
$sessions = new SessionRepository($database);

$total = 0;

function verifier(bool $condition, string $label): void
{
    global $total;
    $total++;
    if (!$condition) {
        throw new RuntimeException('Échec : ' . $label);
    }
}

$valeur = static fn (string $sql, array $p = []): mixed => $database->query($sql, $p)->fetchColumn();

/** Nombre de lignes d'historique d'une session. */
$historique = static fn (int $id): int => (int) $valeur(
    'SELECT COUNT(*) FROM historique_session WHERE id_session = :id',
    [':id' => $id]
);

/** Compteur E9 recalculé en PHP, comme avant Q1, pour comparer avec la procédure. */
$compteurPhp = static function (int $idSalle) use ($salles): int {
    $depuis = $salles->controles($idSalle)[0]['controle_le'] ?? null;

    return Maintenance::compter(
        $salles->sessionsFinies($idSalle, $depuis),
        $depuis === null ? null : new DateTimeImmutable($depuis)
    );
};

// ---------------------------------------------------------------------
// Les objets existent dans la base (002, 003, 004 appliqués)
// ---------------------------------------------------------------------
$objets = static fn (string $sql): array => $database->query($sql)->fetchAll(PDO::FETCH_COLUMN);

$vues = $objets("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE()");
verifier(in_array('v_planning', $vues, true), 'vue v_planning présente');
verifier(in_array('v_tableau_bord', $vues, true), 'vue v_tableau_bord présente');
verifier(
    in_array('sp_bilan_salle', $objets("SELECT ROUTINE_NAME FROM information_schema.ROUTINES
        WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'PROCEDURE'"), true),
    'procédure sp_bilan_salle présente'
);
verifier(
    in_array('trg_session_historique', $objets("SELECT TRIGGER_NAME FROM information_schema.TRIGGERS
        WHERE TRIGGER_SCHEMA = DATABASE()"), true),
    'trigger trg_session_historique présent'
);

// ---------------------------------------------------------------------
// Vue v_planning
// ---------------------------------------------------------------------
verifier(
    (int) $valeur('SELECT COUNT(DISTINCT id_session) FROM v_planning') === (int) $valeur('SELECT COUNT(*) FROM session'),
    'v_planning : chaque session apparaît'
);
$fiche = $sessions->trouver(1);
verifier($fiche !== null && $fiche['nom_salle'] !== '' && array_key_exists('gm_prenom', $fiche), 'trouver() lit la session dans la vue, avec salle et game master');
foreach ($sessions->planning(7) as $ligne) {
    verifier(isset($ligne['nom_salle'], $ligne['etat_session']), 'planning() : session ' . $ligne['id_session'] . ' avec sa salle');
}

// ---------------------------------------------------------------------
// Vue v_tableau_bord
// ---------------------------------------------------------------------
$tableau = $salles->tableauDeBord();
verifier(count($tableau) === (int) $valeur('SELECT COUNT(*) FROM salle'), 'v_tableau_bord : une ligne par salle');
verifier(
    array_sum(array_column($tableau, 'incidents_ouverts')) === (int) $valeur("SELECT COUNT(*) FROM incident WHERE statut_incident = 'OUVERT'"),
    'v_tableau_bord : total des incidents ouverts'
);
verifier(
    array_sum(array_column($tableau, 'sessions_jour')) === (int) $valeur("SELECT COUNT(*) FROM session
        WHERE etat_session <> 'ANNULEE' AND DATE(debut_session) = CURDATE()"),
    'v_tableau_bord : total des sessions du jour hors annulées'
);

// ---------------------------------------------------------------------
// Procédure sp_bilan_salle : même résultat que le calcul PHP sur le seed
// ---------------------------------------------------------------------
foreach ($salles->all() as $salle) {
    $id = (int) $salle['id_salle'];
    $bilan = $salles->bilan($id);
    verifier($bilan !== null && $bilan['compteur'] === $compteurPhp($id), "sp_bilan_salle($id) : même compteur que Maintenance::compter()");
    verifier($bilan['dernier_controle'] === ($salles->controles($id)[0]['controle_le'] ?? null), "sp_bilan_salle($id) : date du dernier contrôle");
}
verifier($salles->bilan(999999) === null, 'sp_bilan_salle : salle inconnue, aucune ligne');

// ---------------------------------------------------------------------
// Scénario dans une transaction annulée : trigger et compteur
// ---------------------------------------------------------------------
$pdo->beginTransaction();
try {
    $idSalle = 1;
    $gm = (int) $sessions->gameMastersActifs()[0]['id_utilisateur'];
    $responsable = (int) $valeur("SELECT id_utilisateur FROM utilisateur WHERE role_utilisateur = 'RESPONSABLE' LIMIT 1");
    $avant = $salles->bilan($idSalle)['compteur'];

    // Une partie jouée il y a 2 h, après le dernier contrôle de la salle
    $debut = (string) $valeur('SELECT NOW() - INTERVAL 2 HOUR');
    $fin = (string) $valeur('SELECT NOW() - INTERVAL 1 HOUR');
    $id = $sessions->creer($idSalle, $debut, $fin);
    verifier($historique($id) === 0, 'trigger : création, aucune ligne (pas de changement d\'état)');

    verifier($sessions->changerEtat($id, 'PREVUE', 'PRETE'), 'PREVUE -> PRETE accepté');
    verifier($historique($id) === 1, 'trigger : PREVUE -> PRETE, 1 ligne');
    $ligne = $database->query(
        'SELECT ancien_etat, nouvel_etat FROM historique_session WHERE id_session = :id',
        [':id' => $id]
    )->fetch(PDO::FETCH_ASSOC);
    verifier($ligne === ['ancien_etat' => 'PREVUE', 'nouvel_etat' => 'PRETE'], 'trigger : ancien et nouvel état enregistrés');

    verifier($sessions->affecterGameMaster($id, $gm, 'PRETE'), 'affectation du game master');
    verifier($historique($id) === 1, 'trigger : affectation sans changement d\'état, aucune ligne de plus');

    verifier(!$sessions->changerEtat($id, 'PREVUE', 'EN_COURS'), 'transition depuis un état périmé refusée');
    verifier($historique($id) === 1, 'trigger : UPDATE sans effet, aucune ligne de plus');

    verifier($sessions->changerEtat($id, 'PRETE', 'EN_COURS'), 'PRETE -> EN_COURS accepté');
    verifier($salles->bilan($idSalle)['compteur'] === $avant, 'E9 : session en cours pas encore comptée');
    verifier($sessions->changerEtat($id, 'EN_COURS', 'TERMINEE'), 'EN_COURS -> TERMINEE accepté');
    verifier($historique($id) === 3, 'trigger : 3 transitions, 3 lignes');
    verifier($salles->bilan($idSalle)['compteur'] === $avant + 1, 'E9 : session terminée comptée (+1)');
    verifier($salles->bilan($idSalle)['compteur'] === $compteurPhp($idSalle), 'E9 : procédure et PHP toujours d\'accord');

    // Une session annulée est historisée mais ne compte pas
    $annulee = $sessions->creer($idSalle, $debut, $fin);
    verifier($sessions->changerEtat($annulee, 'PREVUE', 'ANNULEE'), 'PREVUE -> ANNULEE accepté');
    verifier($historique($annulee) === 1, 'trigger : annulation historisée');
    verifier($salles->bilan($idSalle)['compteur'] === $avant + 1, 'E9 : session annulée ignorée');

    // Un contrôle maintenant remet le compteur à zéro
    $salles->enregistrerControle($idSalle, $responsable, 'Contrôle du test Q1');
    $apres = $salles->bilan($idSalle);
    verifier($apres['compteur'] === 0, 'E9 : contrôle enregistré, compteur à 0');
    verifier($apres['dernier_controle'] === (string) $valeur('SELECT MAX(controle_le) FROM controle_maintenance WHERE id_salle = 1'), 'E9 : dernier contrôle mis à jour');
} finally {
    $pdo->rollBack();
}

verifier($salles->bilan(1)['compteur'] === $compteurPhp(1), 'transaction annulée : base inchangée');

echo "SQL avancé (Q1) : {$total} vérifications vertes.\n";
