<?php
/**
 * En-tête de l'espace équipe : liens selon le rôle, nom de l'utilisateur, déconnexion.
 * @var array  $utilisateur id, prenom, nom, role
 * @var string $csrf
 */

use App\View\Html;

$liens = [
    'RESPONSABLE' => ['/responsable/sessions' => 'Planning', '/responsable/sessions/nouvelle' => 'Nouvelle session', '/responsable/salles' => 'Salles & maintenance'],
    'GAME_MASTER' => ['/gm/sessions' => 'Mes sessions'],
    'ADMIN' => ['/admin/comptes' => 'Comptes'],
][$utilisateur['role']] ?? [];

$roles = ['RESPONSABLE' => 'Responsable', 'GAME_MASTER' => 'Game master', 'ADMIN' => 'Administrateur'];
?>
<header class="bg-marine text-white">
    <div class="max-w-7xl mx-auto px-4 md:px-12 py-3 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-8">
            <a href="/espace" class="font-serif text-xl font-semibold text-white no-underline">Les Archives Obscures <span class="font-mono text-xs tracking-[0.14em] text-white/60 ml-2">ESCAPEOPS</span></a>
            <nav aria-label="Espace équipe" class="flex flex-wrap gap-5 text-sm">
                <?php foreach ($liens as $url => $libelle): ?>
                    <a href="<?= $url ?>" class="text-white/90 hover:text-white hover:underline"><?= Html::e($libelle) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span><?= Html::e($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?> · <span class="text-white/70"><?= Html::e($roles[$utilisateur['role']] ?? '') ?></span></span>
            <form method="post" action="/deconnexion">
                <input type="hidden" name="csrf" value="<?= Html::e($csrf) ?>">
                <button type="submit" class="min-h-9 px-3 rounded border border-white/40 hover:bg-white/10 cursor-pointer">Se déconnecter</button>
            </form>
        </div>
    </div>
</header>
