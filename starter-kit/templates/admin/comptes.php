<?php
/**
 * Comptes de l'équipe, pour l'administrateur (lecture seule pour l'instant).
 * @var array  $comptes
 * @var array  $utilisateur
 * @var string $csrf
 */

use App\View\Html;

$titre = 'Comptes — EscapeOps';
$classeCorps = 'bg-equipe text-encre';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-equipe.php';

$roles = ['RESPONSABLE' => 'Responsable', 'GAME_MASTER' => 'Game master', 'ADMIN' => 'Administrateur'];
?>
<main class="max-w-5xl mx-auto px-4 md:px-12 py-10">
    <h1 class="font-serif text-5xl font-semibold mb-8">Comptes</h1>
    <div class="overflow-x-auto bg-white border border-gris-trait rounded-md">
        <table class="w-full text-sm">
            <thead class="bg-gris-fond text-left text-texte-doux">
                <tr>
                    <th scope="col" class="px-4 py-2.5 font-medium">Nom</th>
                    <th scope="col" class="px-4 py-2.5 font-medium">E-mail</th>
                    <th scope="col" class="px-4 py-2.5 font-medium">Rôle</th>
                    <th scope="col" class="px-4 py-2.5 font-medium">Compte</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comptes as $c): ?>
                    <tr class="border-t border-gris-trait">
                        <td class="px-4 py-3"><?= Html::e($c['prenom_utilisateur'] . ' ' . $c['nom_utilisateur']) ?></td>
                        <td class="px-4 py-3"><?= Html::e($c['email_utilisateur']) ?></td>
                        <td class="px-4 py-3"><?= Html::e($roles[$c['role_utilisateur']] ?? $c['role_utilisateur']) ?></td>
                        <td class="px-4 py-3">
                            <?= (int) $c['actif_utilisateur'] === 1
                                ? '<span class="text-ok font-semibold">Actif</span>'
                                : '<span class="text-ko font-semibold">Désactivé</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php require dirname(__DIR__) . '/partials/fin.php'; ?>
