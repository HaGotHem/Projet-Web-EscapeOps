<?php
/**
 * Détail d'une session et changements d'état (maquette Session).
 * @var array  $session  avec 'actions' (vers, libelle, possible, raison)
 * @var ?array $message
 * @var array  $utilisateur
 * @var string $csrf
 */

use App\View\FormatFr;
use App\View\Html;

$titre = 'Session — EscapeOps';
$classeCorps = 'bg-equipe text-encre';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-equipe.php';

$s = $session;
?>
<main class="max-w-5xl mx-auto px-4 md:px-12 py-10">
    <p class="text-sm mb-4"><a href="/responsable/sessions" class="text-marine">← Planning</a></p>

    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
            <p class="font-mono text-xs tracking-[0.14em] text-texte-pale">SESSION N° <?= (int) $s['id_session'] ?></p>
            <h1 class="font-serif text-5xl font-semibold"><?= Html::e($s['nom_salle']) ?></h1>
            <p class="mt-2 text-lg text-texte">
                <?= Html::e(FormatFr::jourLong($s['debut_session'])) ?> ·
                <span class="font-mono"><?= Html::e(FormatFr::horaire($s['debut_session'], $s['fin_session'])) ?></span>
            </p>
        </div>
        <?= Html::badgeEtat($s['etat_session']) ?>
    </div>

    <?php require dirname(__DIR__) . '/partials/message-flash.php'; ?>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Réservation -->
        <section class="bg-white border border-gris-trait rounded-md p-6" aria-labelledby="t-resa">
            <h2 id="t-resa" class="font-semibold text-lg mb-4">Réservation</h2>
            <?php if ($s['code_reservation'] !== null): ?>
                <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm">
                    <dt class="text-texte-doux">Référence</dt><dd class="font-mono"><?= Html::e($s['code_reservation']) ?></dd>
                    <dt class="text-texte-doux">Groupe</dt><dd><?= Html::e($s['nom_groupe']) ?> · <?= (int) $s['nb_joueurs'] ?> joueurs</dd>
                    <dt class="text-texte-doux">Contact</dt><dd><?= Html::e($s['contact_nom']) ?></dd>
                    <dt class="text-texte-doux">E-mail</dt><dd><?= Html::e($s['contact_email']) ?></dd>
                    <?php if ($s['contact_tel'] !== null): ?>
                        <dt class="text-texte-doux">Téléphone</dt><dd><?= Html::e($s['contact_tel']) ?></dd>
                    <?php endif; ?>
                    <dt class="text-texte-doux">Réservée le</dt><dd><?= Html::e(FormatFr::jourCourt($s['reservee_le']) . ' · ' . FormatFr::heure($s['reservee_le'])) ?></dd>
                </dl>
            <?php else: ?>
                <p class="text-texte-doux">Aucun groupe n'a réservé ce créneau. Il est proposé sur la vitrine tant qu'il est « Prévu » ou « Prêt ».</p>
            <?php endif; ?>
        </section>

        <!-- Game master -->
        <section class="bg-white border border-gris-trait rounded-md p-6" aria-labelledby="t-gm">
            <h2 id="t-gm" class="font-semibold text-lg mb-4">Game master</h2>
            <?php if ($s['id_game_master'] !== null): ?>
                <p><?= Html::e($s['gm_prenom'] . ' ' . $s['gm_nom']) ?></p>
            <?php else: ?>
                <p class="text-ko font-semibold">Aucun game master affecté.</p>
                <p class="text-sm text-texte-doux mt-1">Une session sans game master ne peut pas démarrer (règle E5).</p>
            <?php endif; ?>

            <?php if ($s['gm_modifiable']): ?>
                <ul class="mt-5 flex flex-col gap-2">
                    <?php foreach ($s['game_masters'] as $gm): ?>
                        <li class="flex flex-wrap items-center justify-between gap-2 border-t border-gris-trait pt-2">
                            <div>
                                <p class="font-medium"><?= Html::e($gm['nom']) ?></p>
                                <?php if ($gm['conflit'] !== null): ?>
                                    <p class="text-sm text-ko">Déjà sur « <?= Html::e($gm['conflit']['nom_salle']) ?> » · <?= Html::e(FormatFr::horaire($gm['conflit']['debut_session'], $gm['conflit']['fin_session'])) ?></p>
                                <?php else: ?>
                                    <p class="text-sm text-texte-doux">Libre sur ce créneau</p>
                                <?php endif; ?>
                            </div>
                            <?php if ($gm['affecte']): ?>
                                <span class="text-sm font-semibold text-ok">Affecté</span>
                            <?php else: ?>
                                <!-- Le bouton reste actif même en conflit : c'est le serveur qui refuse (E5) -->
                                <form method="post" action="/responsable/sessions/<?= (int) $s['id_session'] ?>/game-master">
                                    <input type="hidden" name="csrf" value="<?= Html::e($csrf) ?>">
                                    <input type="hidden" name="id_game_master" value="<?= (int) $gm['id'] ?>">
                                    <button type="submit" class="min-h-10 px-4 rounded-md border border-marine text-marine font-semibold hover:bg-marine hover:text-white cursor-pointer">Affecter</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>

    <!-- Changements d'état -->
    <section class="mt-6 bg-white border border-gris-trait rounded-md p-6" aria-labelledby="t-etat">
        <h2 id="t-etat" class="font-semibold text-lg mb-4">Faire avancer la session</h2>
        <?php if ($s['actions'] === []): ?>
            <p class="text-texte-doux">Cette session est dans un état final : elle ne change plus.</p>
        <?php else: ?>
            <div class="flex flex-wrap gap-4">
                <?php foreach ($s['actions'] as $a): ?>
                    <form method="post" action="/responsable/sessions/<?= (int) $s['id_session'] ?>/etat" class="flex flex-col gap-1.5 max-w-xs">
                        <input type="hidden" name="csrf" value="<?= Html::e($csrf) ?>">
                        <input type="hidden" name="etat" value="<?= Html::e($a['vers']) ?>">
                        <button type="submit" <?= $a['possible'] ? '' : 'disabled' ?>
                                class="min-h-11 px-5 rounded-md font-semibold <?= !$a['possible']
                                    ? 'bg-gris-fond text-gris border border-gris-trait cursor-not-allowed'
                                    : ($a['vers'] === 'ANNULEE' ? 'bg-white text-ko border border-ko-trait hover:bg-ko-fond cursor-pointer' : 'bg-marine text-white hover:bg-marine-fonce cursor-pointer') ?>">
                            <?= Html::e($a['libelle']) ?>
                        </button>
                        <?php if (!$a['possible']): ?>
                            <p class="text-sm text-ko"><?= Html::e($a['raison']) ?></p>
                        <?php endif; ?>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require dirname(__DIR__) . '/partials/fin.php'; ?>
