<?php
/**
 * Menu DEBUG de connexion rapide, en bas à droite de toutes les pages.
 * N'apparaît que si $debugComptes est partagé, c'est-à-dire en APP_ENV=dev + APP_DEBUG=true
 * (voir public/index.php). Chaque bouton envoie un POST /debug/connexion avec le jeton CSRF.
 * @var ?callable $debugComptes  renvoie les comptes actifs
 */

use App\Http\Csrf;
use App\View\Html;

if (!isset($debugComptes) || !is_callable($debugComptes)) {
    return;
}

$debugConnecte = $_SESSION['utilisateur'] ?? null;
$debugJeton = Csrf::jeton();
?>
<details class="fixed bottom-4 right-4 z-50 w-72 rounded-md border-2 border-dashed border-[#B45309] bg-[#FFFBEB] text-[#1E1F22] text-sm shadow-lg">
    <summary class="cursor-pointer select-none px-3 py-2 font-mono text-xs font-semibold tracking-[0.1em] uppercase text-[#B45309]">
        Debug · <?= $debugConnecte ? Html::e($debugConnecte['prenom'] . ' (' . $debugConnecte['role'] . ')') : 'non connecté' ?>
    </summary>
    <div class="px-3 pb-3 flex flex-col gap-1.5">
        <p class="text-xs text-[#78350F]">Connexion rapide, sans mot de passe. Désactivée hors développement.</p>
        <?php foreach ($debugComptes() as $compte): ?>
            <form method="post" action="/debug/connexion">
                <input type="hidden" name="csrf" value="<?= Html::e($debugJeton) ?>">
                <input type="hidden" name="id_utilisateur" value="<?= (int) $compte['id_utilisateur'] ?>">
                <button type="submit" class="w-full flex justify-between gap-2 px-2.5 py-1.5 rounded border border-[#FCD34D] bg-white text-left hover:border-[#B45309]">
                    <span><?= Html::e($compte['prenom_utilisateur'] . ' ' . $compte['nom_utilisateur']) ?></span>
                    <span class="font-mono text-[11px] text-[#78350F]"><?= Html::e($compte['role_utilisateur']) ?></span>
                </button>
            </form>
        <?php endforeach; ?>
        <?php if ($debugConnecte): ?>
            <form method="post" action="/deconnexion">
                <input type="hidden" name="csrf" value="<?= Html::e($debugJeton) ?>">
                <button type="submit" class="w-full px-2.5 py-1.5 rounded border border-[#FCD34D] text-[#78350F] hover:border-[#B45309]">Se déconnecter</button>
            </form>
        <?php endif; ?>
    </div>
</details>
