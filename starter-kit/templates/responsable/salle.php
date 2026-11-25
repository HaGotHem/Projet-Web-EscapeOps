<?php
/**
 * Fiche salle du responsable : maintenance préventive (règle E9, maquette 09 SalleResponsable).
 * @var array  $salle avec compteur, seuil, controle_a_prevoir, dernier_controle, controles, sessions_depuis_controle
 * @var array  $erreurs  champ => message (enregistrement d'un contrôle)
 * @var array  $valeurs  saisie à réafficher
 * @var ?array $message
 * @var array  $utilisateur
 * @var string $csrf
 */

use App\View\FormatFr;
use App\View\Html;

$titre = $salle['nom_salle'] . ' — maintenance — EscapeOps';
$classeCorps = 'bg-equipe text-encre';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-equipe.php';

$alerte = $salle['controle_a_prevoir'];
$compteur = (int) $salle['compteur'];
$seuil = (int) $salle['seuil'];
$numero = 0;
?>
<main class="max-w-5xl mx-auto px-4 md:px-12 py-10">
    <p class="text-sm mb-4"><a href="/responsable/salles" class="text-marine">← Salles et maintenance</a></p>

    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
            <p class="font-mono text-xs tracking-[0.14em] text-texte-pale">SALLE N° <?= (int) $salle['id_salle'] ?></p>
            <h1 class="font-serif text-5xl font-semibold"><?= Html::e($salle['nom_salle']) ?></h1>
            <p class="mt-2 text-lg text-texte">
                <?= (int) $salle['duree_jeu_min'] ?> min · <?= (int) $salle['capacite_min'] ?> à <?= (int) $salle['capacite_max'] ?> joueurs
            </p>
        </div>
        <?php if ($alerte): ?><?= Html::badgeControle() ?><?php endif; ?>
    </div>

    <?php require dirname(__DIR__) . '/partials/message-flash.php'; ?>

    <div class="grid md:grid-cols-[3fr_2fr] gap-6">
        <!-- Compteur E9 -->
        <section class="bg-white border <?= $alerte ? 'border-[#C9B3EA]' : 'border-gris-trait' ?> rounded-md p-6" aria-labelledby="t-maintenance">
            <h2 id="t-maintenance" class="font-semibold text-lg mb-4">Maintenance préventive</h2>
            <p class="font-mono text-5xl <?= $alerte ? 'text-[#5B2E9A]' : 'text-marine' ?>">
                <?= $compteur ?><span class="text-2xl text-texte-doux">/<?= $seuil ?></span>
            </p>
            <p class="text-texte-doux">sessions terminées depuis le dernier contrôle</p>

            <div class="mt-4 flex gap-1" aria-hidden="true">
                <?php for ($i = 0; $i < $seuil; $i++): ?>
                    <span class="h-3 flex-1 rounded-sm <?= $i < $compteur ? ($alerte ? 'bg-[#6D3FB0]' : 'bg-marine') : 'bg-[#E4DFD6]' ?>"></span>
                <?php endfor; ?>
            </div>

            <p class="mt-4 text-sm text-texte">
                <?php if ($salle['dernier_controle'] !== null): ?>
                    Dernier contrôle le <?= Html::e(FormatFr::jourCourt($salle['dernier_controle']['controle_le']) . ' à ' . FormatFr::heure($salle['dernier_controle']['controle_le'])) ?>
                    par <?= Html::e($salle['dernier_controle']['prenom_utilisateur'] . ' ' . $salle['dernier_controle']['nom_utilisateur']) ?>.
                <?php else: ?>
                    Cette salle n'a jamais été contrôlée.
                <?php endif; ?>
                Les sessions annulées ne sont pas comptées.
            </p>

            <details class="mt-4">
                <summary class="cursor-pointer text-sm text-marine">Voir les sessions depuis le dernier contrôle</summary>
                <?php if ($salle['sessions_depuis_controle'] === []): ?>
                    <p class="mt-2 text-sm text-texte-doux">Aucune session terminée ou annulée.</p>
                <?php else: ?>
                    <ul class="mt-2 flex flex-col gap-1 text-sm">
                        <?php foreach ($salle['sessions_depuis_controle'] as $ss): ?>
                            <?php $comptee = $ss['etat_session'] === 'TERMINEE'; ?>
                            <li class="flex justify-between gap-2 <?= $comptee ? '' : 'text-ko line-through' ?>">
                                <span>
                                    <?= $comptee ? ++$numero : '—' ?> ·
                                    <?= Html::e(FormatFr::jourCourt($ss['debut_session']) . ' ' . FormatFr::heure($ss['debut_session'])) ?>
                                    <?= $ss['nom_groupe'] !== null ? '· ' . Html::e($ss['nom_groupe']) : '' ?>
                                    <?= $comptee ? '' : '(non comptée)' ?>
                                </span>
                                <span class="font-mono text-xs"><?= $comptee ? 'TERMINÉE' : 'ANNULÉE' ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </details>
        </section>

        <!-- Enregistrer un contrôle -->
        <section class="bg-white border border-gris-trait rounded-md p-6" aria-labelledby="t-controle">
            <h2 id="t-controle" class="font-semibold text-lg mb-4">Enregistrer un contrôle</h2>
            <form method="post" action="/responsable/salles/<?= (int) $salle['id_salle'] ?>/controles" class="flex flex-col gap-3">
                <input type="hidden" name="csrf" value="<?= Html::e($csrf) ?>">
                <p class="text-sm text-texte">Contrôle fait maintenant par <strong><?= Html::e($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?></strong>.</p>
                <label for="commentaire" class="text-sm font-medium">Commentaire (facultatif)</label>
                <textarea id="commentaire" name="commentaire" rows="4" maxlength="1000"
                          <?= isset($erreurs['commentaire']) ? 'aria-invalid="true" aria-describedby="err-commentaire"' : '' ?>
                          class="rounded-md border border-gris-trait p-3 text-sm"
                          placeholder="Ex. coffre testé, lampe du projecteur remplacée."><?= Html::e($valeurs['commentaire'] ?? '') ?></textarea>
                <?php if (isset($erreurs['commentaire'])): ?>
                    <p id="err-commentaire" class="text-sm text-ko"><?= Html::e($erreurs['commentaire']) ?></p>
                <?php endif; ?>
                <p class="text-sm text-texte-doux">Le compteur repassera à 0/<?= $seuil ?><?= $alerte ? ' et le badge disparaîtra' : '' ?>.</p>
                <button type="submit" class="min-h-11 px-5 rounded-md font-semibold bg-marine text-white hover:bg-marine-fonce cursor-pointer">Enregistrer le contrôle</button>
            </form>
        </section>
    </div>

    <!-- Historique -->
    <section class="mt-6 bg-white border border-gris-trait rounded-md p-6" aria-labelledby="t-historique">
        <h2 id="t-historique" class="font-semibold text-lg mb-4">Historique des contrôles</h2>
        <?php if ($salle['controles'] === []): ?>
            <p class="text-texte-doux">Aucun contrôle enregistré.</p>
        <?php else: ?>
            <ul class="flex flex-col gap-3 text-sm">
                <?php foreach ($salle['controles'] as $c): ?>
                    <li class="border-t border-gris-trait pt-3">
                        <p class="font-medium"><?= Html::e(FormatFr::jourCourt($c['controle_le']) . ' à ' . FormatFr::heure($c['controle_le'])) ?> · <?= Html::e($c['prenom_utilisateur'] . ' ' . $c['nom_utilisateur']) ?></p>
                        <?php if ($c['commentaire_controle'] !== null): ?>
                            <p class="text-texte-doux"><?= Html::e($c['commentaire_controle']) ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>
<?php require dirname(__DIR__) . '/partials/fin.php'; ?>
