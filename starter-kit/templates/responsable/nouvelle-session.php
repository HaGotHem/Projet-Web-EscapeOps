<?php
/**
 * Création d'une session par le responsable.
 * @var array  $salles
 * @var array  $valeurs  saisie précédente (si erreur)
 * @var array  $erreurs  champ => message
 * @var array  $utilisateur
 * @var string $csrf
 */

use App\View\Html;

$titre = 'Nouvelle session — EscapeOps';
$classeCorps = 'bg-equipe text-encre';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-equipe.php';

$salleChoisie = (string) ($valeurs['id_salle'] ?? '');
$debut = is_string($valeurs['debut'] ?? null) ? $valeurs['debut'] : '';
?>
<main class="max-w-2xl mx-auto px-4 md:px-12 py-10">
    <p class="text-sm mb-4"><a href="/responsable/sessions" class="text-marine">← Planning</a></p>
    <h1 class="font-serif text-5xl font-semibold mb-2">Nouvelle session</h1>
    <p class="text-texte-doux mb-8">La fin est calculée avec la durée de jeu de la salle. La session est créée « Prévue », sans game master.</p>

    <?php if (isset($erreurs['csrf'])): ?>
        <div role="alert" class="mb-6 px-4 py-3 rounded-md border border-ko-trait bg-ko-fond text-ko"><?= Html::e($erreurs['csrf']) ?></div>
    <?php endif; ?>

    <form method="post" action="/responsable/sessions" class="flex flex-col gap-6 bg-white border border-gris-trait rounded-md p-6" novalidate>
        <input type="hidden" name="csrf" value="<?= Html::e($csrf) ?>">

        <div class="flex flex-col gap-1.5">
            <label for="id_salle" class="font-medium">Salle</label>
            <select id="id_salle" name="id_salle" required
                    <?= isset($erreurs['id_salle']) ? 'aria-invalid="true" aria-describedby="err-salle"' : '' ?>
                    class="min-h-12 px-3 rounded-md border <?= isset($erreurs['id_salle']) ? 'border-ko-vif' : 'border-gris-trait' ?> bg-white">
                <option value="">— Choisir —</option>
                <?php foreach ($salles as $salle): ?>
                    <option value="<?= (int) $salle['id_salle'] ?>" <?= $salleChoisie === (string) $salle['id_salle'] ? 'selected' : '' ?>>
                        <?= Html::e($salle['nom_salle']) ?> (<?= (int) $salle['duree_jeu_min'] ?> min)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erreurs['id_salle'])): ?>
                <p id="err-salle" class="text-sm text-ko"><?= Html::e($erreurs['id_salle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="debut" class="font-medium">Début</label>
            <input id="debut" name="debut" type="datetime-local" required value="<?= Html::e($debut) ?>"
                   <?= isset($erreurs['debut']) ? 'aria-invalid="true" aria-describedby="err-debut"' : '' ?>
                   class="min-h-12 px-3 rounded-md border <?= isset($erreurs['debut']) ? 'border-ko-vif' : 'border-gris-trait' ?> bg-white">
            <?php if (isset($erreurs['debut'])): ?>
                <p id="err-debut" class="text-sm text-ko"><?= Html::e($erreurs['debut']) ?></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="min-h-12 rounded-md bg-marine hover:bg-marine-fonce text-white font-semibold cursor-pointer">Créer la session</button>
    </form>
</main>
<?php require dirname(__DIR__) . '/partials/fin.php'; ?>
