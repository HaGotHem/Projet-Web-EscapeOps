<?php
/**
 * « Mes sessions » du game master connecté (lecture seule).
 * @var array  $sessions
 * @var int    $jours
 * @var array  $utilisateur
 * @var string $csrf
 */

use App\View\FormatFr;
use App\View\Html;

$titre = 'Mes sessions — EscapeOps';
$classeCorps = 'bg-equipe text-encre';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-equipe.php';
?>
<main class="max-w-4xl mx-auto px-4 md:px-12 py-10">
    <p class="font-mono text-xs tracking-[0.14em] text-texte-pale"><?= (int) $jours ?> PROCHAINS JOURS</p>
    <h1 class="font-serif text-5xl font-semibold mb-8">Bonjour <?= Html::e($utilisateur['prenom']) ?></h1>

    <?php if ($sessions === []): ?>
        <p class="text-texte-doux">Aucune session ne vous est affectée pour le moment.</p>
    <?php else: ?>
        <ul class="flex flex-col gap-3">
            <?php foreach ($sessions as $s): ?>
                <li class="bg-white border border-gris-trait rounded-md p-5 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="font-semibold"><?= Html::e($s['nom_salle']) ?></p>
                        <p class="text-sm text-texte">
                            <?= Html::e(FormatFr::jourCourt($s['debut_session'])) ?> ·
                            <span class="font-mono"><?= Html::e(FormatFr::horaire($s['debut_session'], $s['fin_session'])) ?></span>
                            <?php if ($s['nom_groupe'] !== null): ?>
                                · <?= Html::e($s['nom_groupe']) ?> (<?= (int) $s['nb_joueurs'] ?> joueurs)
                            <?php endif; ?>
                        </p>
                    </div>
                    <?= Html::badgeEtat($s['etat_session']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/partials/fin.php'; ?>
