<?php
/** @var array $salles Les salles et leurs créneaux, envoyées par VitrineController */

// Raccourci d'échappement : tout texte venant de la base passe par e() (protection XSS).
$e = static fn (mixed $valeur): string => htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');

// Dessins des salles, repris de la maquette (présentation, donc hors base de données).
$dessins = require __DIR__ . '/partials/dessins-salles.php';

// Chiffres de l'accroche, calculés depuis la base.
$nbSalles = count($salles);
$joueursMin = $salles ? min(array_column($salles, 'capacite_min')) : 0;
$joueursMax = $salles ? max(array_column($salles, 'capacite_max')) : 0;
?>
<?php
$titre = 'Les Archives Obscures — Escape games';
require __DIR__ . '/partials/tete.php';
require __DIR__ . '/partials/entete-public.php';
?>

<main>
    <!-- Accroche -->
    <section class="border-b border-trait">
        <div class="max-w-7xl mx-auto px-4 md:px-12 pt-16 pb-14 md:pt-22 md:pb-20 flex flex-wrap items-center gap-14">
            <div class="flex-[999_1_560px] min-w-0">
                <p class="mb-5 font-mono text-[13px] tracking-[0.14em] uppercase text-accent">
                    Escape games · <?= $nbSalles ?> salles · groupes de <?= (int) $joueursMin ?> à <?= (int) $joueursMax ?>
                </p>
                <h1 class="font-serif font-semibold text-5xl md:text-7xl lg:text-[84px] leading-[1.02] max-w-[14ch]">Chaque archive cache une issue.</h1>
                <p class="mt-7 text-[19px] leading-relaxed text-texte max-w-[54ch]">
                    Des salles privatisées pour votre groupe. Entre 60 et 75 minutes pour en sortir.
                    Quand vous réservez un créneau, la salle est à vous seuls.
                </p>
                <div class="flex flex-wrap gap-3.5 mt-9">
                    <a href="#salles" class="inline-flex items-center gap-2.5 min-h-[52px] px-6 rounded-md bg-accent hover:bg-accent-fonce text-white font-semibold">
                        Voir les prochains créneaux
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14 M13 6l6 6-6 6"></path></svg>
                    </a>
                    <a href="#fonctionnement" class="inline-flex items-center min-h-[52px] px-5 rounded-md border border-trait-fort text-encre hover:border-encre">Comment ça marche</a>
                </div>
            </div>

            <!-- Registre décoratif -->
            <div aria-hidden="true" class="flex-[1_1_340px] flex justify-center">
                <div class="w-[340px] max-w-full h-[380px] border border-trait-fort rounded bg-papier flex flex-col justify-between p-7">
                    <div class="flex justify-between font-mono text-xs tracking-[0.12em] text-texte-pale">
                        <span>REGISTRE</span><span>AO · 1923</span>
                    </div>
                    <svg viewBox="0 0 284 220" class="w-full h-auto" fill="none" stroke="#2F4BFF" stroke-width="1.2">
                        <rect x="12" y="10" width="260" height="200" rx="2"></rect>
                        <path d="M12 60h260 M12 110h260 M12 160h260 M142 10v200"></path>
                        <path d="M68 30h18 M198 30h18 M68 80h18 M198 80h18 M68 130h18 M198 130h18 M68 180h18 M198 180h18"></path>
                        <circle cx="207" cy="135" r="15"></circle>
                        <path d="M207 128a4 4 0 0 1 2 7.5l1.5 6h-7l1.5-6a4 4 0 0 1 2-7.5z"></path>
                    </svg>
                    <div class="flex justify-between items-baseline border-t border-trait pt-3.5">
                        <span class="font-serif italic text-xl text-texte">Tiroir n° 7 — scellé</span>
                        <span class="font-mono text-xs text-texte-pale"><?= $nbSalles ?> / <?= $nbSalles ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Les salles -->
    <section id="salles" class="border-b border-trait">
        <div class="max-w-7xl mx-auto px-4 md:px-12 py-20">
            <div class="flex flex-wrap justify-between items-end gap-6 mb-10">
                <div>
                    <p class="mb-2.5 font-mono text-[13px] tracking-[0.14em] uppercase text-accent">Les dossiers</p>
                    <h2 class="font-serif font-semibold text-5xl leading-tight">Choisissez votre salle</h2>
                </div>
                <div class="flex flex-wrap gap-5 text-sm text-texte-doux">
                    <span class="inline-flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2F4BFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4 10-10"></path></svg>Disponible
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#66696F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 11h14v10H5z M8 11V7a4 4 0 0 1 8 0v4"></path></svg>Déjà réservé par un groupe
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-[repeat(auto-fit,minmax(min(320px,100%),1fr))] gap-6">
                <?php foreach ($salles as $salle): ?>
                    <?php $id = (int) $salle['id_salle']; ?>
                    <article class="flex flex-col bg-papier border border-trait rounded-md overflow-hidden">
                        <!-- Visuel -->
                        <div class="relative h-[200px] bg-papier-gris border-b border-trait flex items-center justify-center">
                            <svg width="240" height="200" viewBox="0 0 240 200" fill="none" stroke="#2F4BFF" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= $dessins[$id] ?? $dessins[1] ?>"></path></svg>
                            <span class="absolute top-3.5 left-4 font-mono text-xs tracking-[0.12em] text-texte-pale">DOSSIER N° <?= sprintf('%02d', $id) ?></span>
                        </div>

                        <!-- Présentation -->
                        <div class="px-6 pt-6 pb-2 flex flex-col gap-3 grow">
                            <h3 class="font-serif font-semibold text-[30px] leading-tight">
                                <a href="/salles/<?= $id ?>" class="text-encre no-underline hover:underline"><?= $e($salle['nom_salle']) ?></a>
                            </h3>
                            <p class="text-[15px] text-texte"><?= $e($salle['description_salle'] ?: $salle['theme_salle']) ?></p>
                            <dl class="mt-1 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2F4BFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0-18 0 M12 7v5l3 2"></path></svg>
                                    <dt class="text-texte-doux">Durée</dt>
                                    <dd class="font-semibold"><?= (int) $salle['duree_jeu_min'] ?> min</dd>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2F4BFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 20v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 10a3 3 0 1 0 0-6a3 3 0 1 0 0 6 M22 20v-2a4 4 0 0 0-3-3.9 M16 4.1a3 3 0 0 1 0 5.8"></path></svg>
                                    <dt class="text-texte-doux">Groupe</dt>
                                    <dd class="font-semibold"><?= (int) $salle['capacite_min'] ?> à <?= (int) $salle['capacite_max'] ?> joueurs</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Créneaux -->
                        <div class="px-6 pt-4 pb-6">
                            <p class="mb-2.5 font-mono text-xs tracking-[0.12em] uppercase text-texte-pale">Prochains créneaux publiés</p>

                            <?php if ($salle['creneaux'] === []): ?>
                                <p class="text-sm text-texte-doux">Aucun créneau publié pour le moment.</p>
                            <?php else: ?>
                                <ul class="grid grid-cols-2 gap-2">
                                    <?php foreach ($salle['creneaux'] as $c): ?>
                                        <li>
                                            <?php if ($c['libre']): ?>
                                                <a href="/reserver/<?= (int) $c['id'] ?>" class="flex flex-col gap-0.5 min-h-11 px-3 py-2 border border-accent rounded text-encre no-underline hover:bg-[#F5F6FF]">
                                                    <span class="text-[13px] text-texte"><?= $e($c['jour']) ?></span>
                                                    <span class="flex items-center justify-between gap-1.5 font-mono font-semibold text-[15px]">
                                                        <?= $e($c['heure']) ?>
                                                        <span class="inline-flex items-center gap-1 font-sans font-medium text-xs text-accent">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4 10-10"></path></svg>Libre
                                                        </span>
                                                    </span>
                                                </a>
                                            <?php else: ?>
                                                <div class="flex flex-col gap-0.5 min-h-11 px-3 py-2 border border-dashed border-trait-pris rounded text-texte-pale">
                                                    <span class="text-[13px]"><?= $e($c['jour']) ?></span>
                                                    <span class="flex items-center justify-between gap-1.5 font-mono text-[15px]">
                                                        <?= $e($c['heure']) ?>
                                                        <span class="inline-flex items-center gap-1 font-sans text-xs">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 11h14v10H5z M8 11V7a4 4 0 0 1 8 0v4"></path></svg>Réservé
                                                        </span>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <p class="mt-4"><a href="/salles/<?= $id ?>" class="text-sm font-semibold">Voir la salle et tous ses créneaux →</a></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Fonctionnement -->
    <section id="fonctionnement" class="border-b border-trait">
        <div class="max-w-7xl mx-auto px-4 md:px-12 py-20">
            <h2 class="mb-10 font-serif font-semibold text-[44px] leading-tight">Comment ça marche</h2>
            <ol class="grid grid-cols-[repeat(auto-fit,minmax(min(260px,100%),1fr))] gap-8">
                <li class="border-t border-accent pt-5">
                    <span class="font-mono text-[13px] text-accent">Étape 1</span>
                    <h3 class="mt-2 mb-2 text-xl font-semibold">Choisissez une salle et un créneau</h3>
                    <p class="text-texte">Seuls les créneaux encore libres sont réservables. Un créneau = une salle privatisée pour un seul groupe.</p>
                </li>
                <li class="border-t border-accent pt-5">
                    <span class="font-mono text-[13px] text-accent">Étape 2</span>
                    <h3 class="mt-2 mb-2 text-xl font-semibold">Présentez votre groupe</h3>
                    <p class="text-texte">Un nom d'équipe, le nombre de joueurs (dans la capacité de la salle) et un contact. Aucun paiement n'est demandé.</p>
                </li>
                <li class="border-t border-accent pt-5">
                    <span class="font-mono text-[13px] text-accent">Étape 3</span>
                    <h3 class="mt-2 mb-2 text-xl font-semibold">Recevez votre confirmation</h3>
                    <p class="text-texte">Arrivez 15 minutes avant l'heure. Une annulation faite au moins 24 h avant le début est classée « sans frais ».</p>
                </li>
            </ol>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/pied-public.php'; ?>
