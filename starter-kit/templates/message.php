<?php
/**
 * Page de message (403, 404, 409…), publique ou dans l'espace équipe.
 * @var string $titre
 * @var string $texte
 * @var int    $code
 * @var string $lien
 * @var string $lienTexte
 * @var ?array $utilisateur  présent dans l'espace équipe
 */

use App\View\Html;

$equipe = isset($utilisateur) && is_array($utilisateur);
$classeCorps = $equipe ? 'bg-equipe text-encre' : 'bg-fond text-encre';
$titrePage = $titre;
$titre = $titrePage . ' — Les Archives Obscures';
require __DIR__ . '/partials/tete.php';

if ($equipe) {
    $csrf ??= \App\Http\Csrf::jeton();
    require __DIR__ . '/partials/entete-equipe.php';
} else {
    require __DIR__ . '/partials/entete-public.php';
}
?>
<main class="max-w-3xl mx-auto px-4 md:px-12 py-24">
    <p class="font-mono text-sm tracking-[0.14em] text-texte-pale">ERREUR <?= (int) $code ?></p>
    <h1 class="mt-3 font-serif text-5xl font-semibold"><?= Html::e($titrePage) ?></h1>
    <p class="mt-5 text-lg text-texte"><?= Html::e($texte) ?></p>
    <p class="mt-8"><a href="<?= Html::e($lien) ?>" class="font-semibold"><?= Html::e($lienTexte) ?> →</a></p>
</main>
<?php require __DIR__ . '/partials/fin.php'; ?>
