<?php
/**
 * Fiche salle publique (maquette 02 · FicheSalle).
 * @var array $salle  la salle + 'jours' : [['jour' => 'Mercredi 25 novembre', 'creneaux' => [...]], ...]
 *                    envoyée par VitrineController::salle()
 */

use App\View\Html;

$dessins = require __DIR__ . '/partials/dessins-salles.php';
$id = (int) $salle['id_salle'];

$titre = $salle['nom_salle'] . ' — Les Archives Obscures';
require __DIR__ . '/partials/tete.php';
require __DIR__ . '/partials/entete-public.php';
?>

<main>
    <!-- Présentation de la salle -->
    <section class="border-b border-trait">
        <div class="max-w-7xl mx-auto px-4 md:px-12 pt-10 pb-16">
            <nav aria-label="Fil d'Ariane" class="mb-8 text-sm text-texte-doux">
                <a href="/#salles" class="text-texte-doux hover:text-encre">Les salles</a>
                <span aria-hidden="true"> / </span>
                <span class="text-encre"><?= Html::e($salle['nom_salle']) ?></span>
            </nav>

            <div class="flex flex-wrap items-start gap-12">
                <div class="flex-[999_1_520px] min-w-0">
                    <p class="mb-3 font-mono text-[13px] tracking-[0.14em] uppercase text-accent">Dossier n° <?= sprintf('%02d', $id) ?> · <?= Html::e($salle['theme_salle']) ?></p>
                    <h1 class="font-serif font-semibold text-5xl md:text-6xl leading-[1.05]"><?= Html::e($salle['nom_salle']) ?></h1>
                    <p class="mt-6 text-[19px] leading-relaxed text-texte max-w-[56ch]"><?= Html::e($salle['description_salle'] ?: $salle['theme_salle']) ?></p>

                    <dl class="mt-7 flex flex-wrap gap-x-8 gap-y-3 text-[15px]">
                        <div class="flex items-center gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2F4BFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0-18 0 M12 7v5l3 2"></path></svg>
                            <dt class="text-texte-doux">Durée</dt>
                            <dd class="font-semibold"><?= (int) $salle['duree_jeu_min'] ?> min</dd>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2F4BFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 20v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 10a3 3 0 1 0 0-6a3 3 0 1 0 0 6 M22 20v-2a4 4 0 0 0-3-3.9 M16 4.1a3 3 0 0 1 0 5.8"></path></svg>
                            <dt class="text-texte-doux">Groupe</dt>
                            <dd class="font-semibold"><?= (int) $salle['capacite_min'] ?> à <?= (int) $salle['capacite_max'] ?> joueurs</dd>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2F4BFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 11h14v10H5z M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
                            <dd class="font-semibold">Privatisée pour votre groupe</dd>
                        </div>
                    </dl>

                    <h2 class="mt-10 mb-3 text-xl font-semibold">À savoir avant de réserver</h2>
                    <ul class="flex flex-col gap-2 text-texte list-disc pl-5">
                        <li>Un créneau = un seul groupe. Pas de joueurs inconnus avec vous.</li>
                        <li>Présentez-vous 15 minutes avant l'heure du créneau.</li>
                        <li>Annulation au moins 24 h avant : classée « sans frais ». Après : « tardive ».</li>
                    </ul>
                </div>

                <!-- Visuel -->
                <div aria-hidden="true" class="flex-[1_1_320px] flex justify-center">
                    <div class="relative w-[340px] max-w-full h-[300px] border border-trait-fort rounded bg-papier-gris flex items-center justify-center">
                        <svg width="240" height="200" viewBox="0 0 240 200" fill="none" stroke="#2F4BFF" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $dessins[$id] ?? $dessins[1] ?>"></path></svg>
                        <span class="absolute top-3.5 left-4 font-mono text-xs tracking-[0.12em] text-texte-pale">DOSSIER N° <?= sprintf('%02d', $id) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Créneaux, rangés par jour -->
    <section id="creneaux" class="border-b border-trait">
        <div class="max-w-7xl mx-auto px-4 md:px-12 py-16">
            <div class="flex flex-wrap justify-between items-end gap-6 mb-8">
                <div>
                    <p class="mb-2.5 font-mono text-[13px] tracking-[0.14em] uppercase text-accent">Créneaux publiés</p>
                    <h2 class="font-serif font-semibold text-4xl leading-tight">Choisissez votre créneau</h2>
                </div>
                <p class="text-sm text-texte-doux">Aucun paiement en ligne. Confirmation immédiate.</p>
            </div>

            <?php if ($salle['jours'] === []): ?>
                <p class="text-texte-doux">Aucun créneau publié pour le moment. Revenez bientôt.</p>
            <?php else: ?>
                <div class="flex flex-col gap-8">
                    <?php foreach ($salle['jours'] as $jour): ?>
                        <?php $nbLibres = count(array_filter($jour['creneaux'], static fn (array $c): bool => $c['libre'])); ?>
                        <div>
                            <h3 class="mb-3 flex items-baseline gap-3 text-lg font-semibold">
                                <?= Html::e($jour['jour']) ?>
                                <span class="font-mono text-xs font-normal text-texte-pale">
                                    <?= $nbLibres === 0 ? 'complet' : $nbLibres . ' libre' . ($nbLibres > 1 ? 's' : '') ?>
                                </span>
                            </h3>
                            <ul class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-2.5">
                                <?php foreach ($jour['creneaux'] as $c): ?>
                                    <li>
                                        <?php if ($c['libre']): ?>
                                            <a href="/reserver/<?= (int) $c['id'] ?>" class="flex items-center justify-between gap-2 min-h-12 px-4 py-2.5 border border-accent rounded text-encre no-underline hover:bg-[#F5F6FF]">
                                                <span class="font-mono font-semibold text-[15px]"><?= Html::e($c['plage']) ?></span>
                                                <span class="text-xs font-medium text-accent">Réserver →</span>
                                            </a>
                                        <?php else: ?>
                                            <div class="flex items-center justify-between gap-2 min-h-12 px-4 py-2.5 border border-dashed border-trait-pris rounded text-texte-pale">
                                                <span class="font-mono text-[15px]"><?= Html::e($c['plage']) ?></span>
                                                <span class="inline-flex items-center gap-1 text-xs">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 11h14v10H5z M8 11V7a4 4 0 0 1 8 0v4"></path></svg>Réservé
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/pied-public.php'; ?>
