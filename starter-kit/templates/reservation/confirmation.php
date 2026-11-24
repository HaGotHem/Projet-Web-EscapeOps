<?php
/**
 * Confirmation de réservation (maquette Reservation, étape 3).
 * @var array $recap code, nom_salle, debut_session, fin_session, nom_groupe, nb_joueurs, contact_email
 */

use App\View\FormatFr;
use App\View\Html;

$titre = 'Réservation confirmée — Les Archives Obscures';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-public.php';
?>
<main class="max-w-2xl mx-auto px-4 md:px-12 py-16">
    <section aria-labelledby="titre-ok" class="bg-papier border border-trait rounded-lg p-8">
        <span class="inline-flex items-center px-3 py-1 rounded-full bg-ok-fond text-ok border border-ok-trait font-mono text-xs font-semibold tracking-wide">CONFIRMÉE</span>
        <h1 id="titre-ok" class="mt-4 font-serif text-5xl font-semibold">La salle vous attend.</h1>
        <p class="mt-3 text-texte">Conservez votre référence : elle vous sera demandée pour toute modification.</p>

        <dl class="mt-8 grid grid-cols-[auto_1fr] gap-x-8 gap-y-3">
            <dt class="text-texte-doux">Référence</dt><dd class="font-mono text-lg font-semibold"><?= Html::e($recap['code']) ?></dd>
            <dt class="text-texte-doux">Salle</dt><dd><?= Html::e($recap['nom_salle']) ?></dd>
            <dt class="text-texte-doux">Créneau</dt>
            <dd><?= Html::e(FormatFr::jourCourt($recap['debut_session'])) ?> · <span class="font-mono"><?= Html::e(FormatFr::horaire($recap['debut_session'], $recap['fin_session'])) ?></span></dd>
            <dt class="text-texte-doux">Groupe</dt><dd><?= Html::e($recap['nom_groupe']) ?> · <?= (int) $recap['nb_joueurs'] ?> joueurs</dd>
        </dl>

        <p class="mt-8 text-sm text-texte-doux">Présentez-vous 15 minutes avant l'heure. Une annulation faite au moins 24 h avant le début est classée « sans frais ».</p>
        <p class="mt-6"><a href="/" class="font-semibold">Retour à l'accueil →</a></p>
    </section>
</main>
<?php require dirname(__DIR__) . '/partials/pied-public.php'; ?>
