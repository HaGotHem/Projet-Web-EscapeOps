<?php
/**
 * Planning du responsable (maquette Cockpit, liste des sessions).
 * @var array  $sessions
 * @var int    $jours
 * @var ?array $message
 * @var array  $utilisateur
 * @var string $csrf
 */

use App\View\FormatFr;
use App\View\Html;

$titre = 'Planning — EscapeOps';
$classeCorps = 'bg-equipe text-encre';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-equipe.php';

// Regroupement par jour pour l'affichage
$parJour = [];
foreach ($sessions as $s) {
    $parJour[substr($s['debut_session'], 0, 10)][] = $s;
}
?>
<main class="max-w-7xl mx-auto px-4 md:px-12 py-10">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="font-mono text-xs tracking-[0.14em] text-texte-pale">COCKPIT · <?= (int) $jours ?> PROCHAINS JOURS</p>
            <h1 class="font-serif text-5xl font-semibold">Planning des sessions</h1>
        </div>
        <a href="/responsable/sessions/nouvelle" class="inline-flex items-center min-h-11 px-5 rounded-md bg-marine hover:bg-marine-fonce text-white font-semibold no-underline">+ Nouvelle session</a>
    </div>

    <?php require dirname(__DIR__) . '/partials/message-flash.php'; ?>

    <?php if ($sessions === []): ?>
        <p class="text-texte-doux">Aucune session planifiée.</p>
    <?php endif; ?>

    <?php foreach ($parJour as $jour => $liste): ?>
        <section class="mb-10" aria-labelledby="jour-<?= Html::e($jour) ?>">
            <h2 id="jour-<?= Html::e($jour) ?>" class="font-semibold text-lg mb-3"><?= Html::e(FormatFr::jourLong($jour)) ?></h2>
            <div class="overflow-x-auto bg-white border border-gris-trait rounded-md">
                <table class="w-full text-sm">
                    <thead class="bg-gris-fond text-left text-texte-doux">
                        <tr>
                            <th scope="col" class="px-4 py-2.5 font-medium">Horaire</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Salle</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Groupe</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Game master</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">État</th>
                            <th scope="col" class="px-4 py-2.5"><span class="sr-only">Détail</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($liste as $s): ?>
                            <tr class="border-t border-gris-trait">
                                <td class="px-4 py-3 font-mono whitespace-nowrap"><?= Html::e(FormatFr::horaire($s['debut_session'], $s['fin_session'])) ?></td>
                                <td class="px-4 py-3"><?= Html::e($s['nom_salle']) ?></td>
                                <td class="px-4 py-3">
                                    <?php if ($s['nom_groupe'] !== null): ?>
                                        <?= Html::e($s['nom_groupe']) ?> <span class="text-texte-doux">· <?= (int) $s['nb_joueurs'] ?> j.</span>
                                    <?php else: ?>
                                        <span class="text-texte-doux">Libre</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($s['id_game_master'] !== null): ?>
                                        <?= Html::e($s['gm_prenom'] . ' ' . $s['gm_nom']) ?>
                                    <?php else: ?>
                                        <span class="text-ko font-semibold">Non affecté</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3"><?= Html::badgeEtat($s['etat_session']) ?></td>
                                <td class="px-4 py-3 text-right"><a href="/responsable/sessions/<?= (int) $s['id_session'] ?>" class="text-marine font-semibold">Ouvrir</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>
</main>
<?php require dirname(__DIR__) . '/partials/fin.php'; ?>
