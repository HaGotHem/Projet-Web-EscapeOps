<?php
/**
 * Confirmation de l'annulation d'une réservation (règle E7, maquettes 08a / 08b).
 * Le classement est annoncé avant de confirmer ; il est recalculé au moment du POST.
 * @var array  $session avec maintenant, statut_annulation, heures_avant, seuil_tardive
 * @var array  $utilisateur
 * @var string $csrf
 */

use App\View\FormatFr;
use App\View\Html;

$titre = 'Annuler la réservation — EscapeOps';
$classeCorps = 'bg-equipe text-encre';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-equipe.php';

$s = $session;
$tardive = $s['statut_annulation'] === 'TARDIVE';
$quand = static fn ($d): string => FormatFr::jourCourt($d) . ' · ' . FormatFr::heure($d);
$delai = $s['heures_avant'] >= 1 ? 'dans ' . $s['heures_avant'] . ' h' : 'dans moins d\'une heure';

// Position de « maintenant » sur la frise : de 48 h avant le début (0 %) au début (100 %)
$position = max(2, min(98, 100 - $s['heures_avant'] / 48 * 100));
?>
<main class="max-w-3xl mx-auto px-4 md:px-12 py-10">
    <p class="text-sm mb-4"><a href="/responsable/sessions/<?= (int) $s['id_session'] ?>" class="text-marine">← Session n° <?= (int) $s['id_session'] ?></a></p>

    <h1 class="font-serif text-4xl font-semibold">Annuler la réservation ?</h1>
    <p class="mt-2 text-lg text-texte">
        <?= Html::e($s['nom_groupe']) ?> · <?= Html::e($s['nom_salle']) ?> · <?= Html::e($quand($s['debut_session'])) ?>
    </p>

    <section class="mt-8 rounded-md p-6 <?= $tardive ? 'bg-[#FFFAF3] border border-[#F0C38E]' : 'bg-[#F4FAFA] border border-[#9FCFD4]' ?>" aria-labelledby="t-classement">
        <div class="flex flex-wrap items-center gap-3">
            <?= Html::badgeAnnulation($s['statut_annulation']) ?>
            <h2 id="t-classement" class="font-semibold text-xl <?= $tardive ? 'text-[#8A4500]' : 'text-[#135E66]' ?>">
                <?= $tardive ? 'Annulation tardive' : 'Annulation sans frais' ?>
            </h2>
        </div>
        <p class="mt-3 text-texte">
            La session commence <?= Html::e($delai) ?>.
            <?php if ($tardive): ?>
                L'annulation est faite moins de 24 h avant le début : elle sera classée <strong>TARDIVE</strong>.
            <?php else: ?>
                L'annulation est faite au moins 24 h avant le début : elle sera classée <strong>SANS FRAIS</strong>.
            <?php endif; ?>
        </p>

        <!-- Frise : sans frais jusqu'au seuil des 24 h, tardive ensuite -->
        <div class="mt-6" aria-hidden="true">
            <div class="relative h-3 rounded-full overflow-hidden flex">
                <div class="h-full w-1/2 bg-[#CFE7E9]"></div>
                <div class="h-full w-1/2 bg-[#F6D9B3]"></div>
                <div class="absolute top-[-4px] h-5 w-1 rounded bg-encre" style="left: <?= number_format($position, 1, '.', '') ?>%"></div>
            </div>
        </div>
        <dl class="mt-3 grid grid-cols-3 gap-2 text-sm">
            <div><dt class="text-texte-doux">Maintenant</dt><dd class="font-mono"><?= Html::e($quand($s['maintenant'])) ?></dd></div>
            <div class="text-center"><dt class="text-texte-doux">Tardive dès</dt><dd class="font-mono"><?= Html::e($quand($s['seuil_tardive'])) ?></dd></div>
            <div class="text-right"><dt class="text-texte-doux">Début</dt><dd class="font-mono"><?= Html::e($quand($s['debut_session'])) ?></dd></div>
        </dl>
    </section>

    <p class="mt-6 text-sm text-texte-doux">
        Aucun paiement n'est traité. Le statut restera affiché sur la réservation, pour vous comme pour le client.
        Le créneau sera de nouveau proposé sur la vitrine.
    </p>

    <form method="post" action="/responsable/sessions/<?= (int) $s['id_session'] ?>/annulation" class="mt-6 flex flex-wrap gap-4">
        <input type="hidden" name="csrf" value="<?= Html::e($csrf) ?>">
        <a href="/responsable/sessions/<?= (int) $s['id_session'] ?>"
           class="inline-flex items-center min-h-11 px-5 rounded-md border border-gris-trait bg-white text-encre font-semibold no-underline hover:bg-gris-fond">Garder la réservation</a>
        <button type="submit"
                class="min-h-11 px-5 rounded-md font-semibold text-white cursor-pointer <?= $tardive ? 'bg-[#8A4500] hover:bg-[#6E3700]' : 'bg-[#135E66] hover:bg-[#0E4A50]' ?>">
            <?= $tardive ? 'Confirmer l\'annulation tardive' : 'Confirmer l\'annulation sans frais' ?>
        </button>
    </form>
</main>
<?php require dirname(__DIR__) . '/partials/fin.php'; ?>
