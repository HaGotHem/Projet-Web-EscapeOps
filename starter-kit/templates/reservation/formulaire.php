<?php
/**
 * Réservation d'un créneau par un groupe (maquette Reservation, étape 2).
 * @var array  $creneau  session, salle, capacités
 * @var array  $valeurs  saisie précédente (si erreur)
 * @var array  $erreurs  champ => message
 * @var string $csrf
 */

use App\View\FormatFr;
use App\View\Html;

$titre = 'Réserver — Les Archives Obscures';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-public.php';

$v = static fn (string $champ): string => is_string($valeurs[$champ] ?? null) ? $valeurs[$champ] : '';
$min = (int) $creneau['capacite_min'];
$max = (int) $creneau['capacite_max'];
$joueurs = $v('nb_joueurs') !== '' ? $v('nb_joueurs') : (string) $min;

// Classes d'un champ, rouges s'il est en erreur
$champ = static fn (string $nom): string => 'min-h-12 px-3 rounded-md border bg-white w-full '
    . (isset($erreurs[$nom]) ? 'border-ko-vif' : 'border-trait-fort');
$aria = static fn (string $nom): string => isset($erreurs[$nom]) ? 'aria-invalid="true" aria-describedby="err-' . $nom . '"' : '';
$erreur = static function (string $nom) use ($erreurs): void {
    if (isset($erreurs[$nom])) {
        echo '<p id="err-' . $nom . '" class="text-sm text-ko">' . Html::e($erreurs[$nom]) . '</p>';
    }
};
?>
<main class="max-w-6xl mx-auto px-4 md:px-12 py-10">
    <p class="text-sm mb-4"><a href="/#salles">← Changer de créneau</a></p>
    <h1 class="font-serif text-5xl font-semibold mb-2">Présentez votre groupe</h1>
    <p class="text-texte mb-8">La salle sera privatisée pour vous seuls. Aucun paiement n'est demandé.</p>

    <?php if ($erreurs !== []): ?>
        <div role="alert" class="mb-6 px-4 py-3 rounded-md border border-ko-trait bg-ko-fond text-ko">
            <?= isset($erreurs['csrf']) ? Html::e($erreurs['csrf']) : 'Vérifiez les champs signalés ci-dessous.' ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/reserver/<?= (int) $creneau['id_session'] ?>" class="grid lg:grid-cols-[1fr_380px] gap-8 items-start" novalidate>
        <input type="hidden" name="csrf" value="<?= Html::e($csrf) ?>">

        <div class="flex flex-col gap-8">
            <fieldset class="bg-papier border border-trait rounded-md p-6 flex flex-col gap-5">
                <legend class="px-2 font-mono text-xs tracking-[0.14em] text-texte-pale">LE GROUPE</legend>
                <div class="flex flex-col gap-1.5">
                    <label for="nom_groupe" class="font-medium">Nom du groupe</label>
                    <input id="nom_groupe" name="nom_groupe" type="text" maxlength="100" required autocomplete="off"
                           value="<?= Html::e($v('nom_groupe')) ?>" <?= $aria('nom_groupe') ?> class="<?= $champ('nom_groupe') ?>">
                    <?php $erreur('nom_groupe'); ?>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="nb_joueurs" class="font-medium">Nombre de joueurs</label>
                    <input id="nb_joueurs" name="nb_joueurs" type="number" min="<?= $min ?>" max="<?= $max ?>" required
                           value="<?= Html::e($joueurs) ?>" <?= $aria('nb_joueurs') ?> class="<?= $champ('nb_joueurs') ?> max-w-32">
                    <span class="text-sm text-texte-doux">Cette salle accueille de <?= $min ?> à <?= $max ?> joueurs.</span>
                    <?php $erreur('nb_joueurs'); ?>
                </div>
            </fieldset>

            <fieldset class="bg-papier border border-trait rounded-md p-6 flex flex-col gap-5">
                <legend class="px-2 font-mono text-xs tracking-[0.14em] text-texte-pale">LA PERSONNE À CONTACTER</legend>
                <div class="grid md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label for="contact_nom" class="font-medium">Nom et prénom</label>
                        <input id="contact_nom" name="contact_nom" type="text" maxlength="100" required autocomplete="name"
                               value="<?= Html::e($v('contact_nom')) ?>" <?= $aria('contact_nom') ?> class="<?= $champ('contact_nom') ?>">
                        <?php $erreur('contact_nom'); ?>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="contact_email" class="font-medium">E-mail</label>
                        <input id="contact_email" name="contact_email" type="email" maxlength="180" required autocomplete="email"
                               value="<?= Html::e($v('contact_email')) ?>" <?= $aria('contact_email') ?> class="<?= $champ('contact_email') ?>">
                        <?php $erreur('contact_email'); ?>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="contact_tel" class="font-medium">Téléphone <span class="font-normal text-texte-doux">(facultatif)</span></label>
                        <input id="contact_tel" name="contact_tel" type="tel" maxlength="20" autocomplete="tel"
                               value="<?= Html::e($v('contact_tel')) ?>" <?= $aria('contact_tel') ?> class="<?= $champ('contact_tel') ?>">
                        <?php $erreur('contact_tel'); ?>
                    </div>
                </div>
                <span class="text-sm text-texte-doux">Nous l'utilisons uniquement pour la confirmation et un éventuel changement d'horaire.</span>
            </fieldset>

            <div class="flex flex-col gap-1.5">
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="conditions" value="1" <?= ($valeurs['conditions'] ?? null) === '1' ? 'checked' : '' ?> <?= $aria('conditions') ?> class="mt-1 size-4">
                    <span>J'ai compris qu'une annulation faite moins de 24 h avant le début sera classée « tardive ».</span>
                </label>
                <?php $erreur('conditions'); ?>
            </div>
        </div>

        <!-- Récapitulatif -->
        <aside aria-labelledby="titre-recap" class="bg-papier border border-trait-fort rounded-lg p-6 lg:sticky lg:top-6">
            <p class="font-mono text-xs tracking-[0.14em] text-accent">DOSSIER N° <?= sprintf('%02d', (int) $creneau['id_salle']) ?></p>
            <h2 id="titre-recap" class="font-serif text-3xl font-semibold mb-4"><?= Html::e($creneau['nom_salle']) ?></h2>
            <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm mb-6">
                <dt class="text-texte-doux">Date</dt><dd><?= Html::e(FormatFr::jourLong($creneau['debut_session'])) ?></dd>
                <dt class="text-texte-doux">Horaire</dt><dd class="font-mono"><?= Html::e(FormatFr::horaire($creneau['debut_session'], $creneau['fin_session'])) ?></dd>
                <dt class="text-texte-doux">Durée</dt><dd><?= (int) $creneau['duree_jeu_min'] ?> min</dd>
                <dt class="text-texte-doux">Joueurs</dt><dd><?= $min ?> à <?= $max ?></dd>
            </dl>
            <button type="submit" class="w-full min-h-[52px] rounded-md bg-accent hover:bg-accent-fonce text-white font-semibold cursor-pointer">Confirmer la réservation</button>
            <p class="mt-3 text-sm text-texte-doux text-center">Aucun paiement. Le créneau est bloqué dès la confirmation.</p>
        </aside>
    </form>
</main>
<?php require dirname(__DIR__) . '/partials/pied-public.php'; ?>
