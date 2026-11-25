<?php
/**
 * Salles et maintenance préventive (règle E9) : un compteur par salle.
 * @var array  $salles avec compteur, seuil, controle_a_prevoir, dernier_controle
 * @var ?array $message
 * @var array  $utilisateur
 * @var string $csrf
 */

use App\View\FormatFr;
use App\View\Html;

$titre = 'Salles et maintenance — EscapeOps';
$classeCorps = 'bg-equipe text-encre';
require dirname(__DIR__) . '/partials/tete.php';
require dirname(__DIR__) . '/partials/entete-equipe.php';
?>
<main class="max-w-5xl mx-auto px-4 md:px-12 py-10">
    <h1 class="font-serif text-5xl font-semibold">Salles et maintenance</h1>
    <p class="mt-2 text-texte">Une salle doit être contrôlée après <?= (int) ($salles[0]['seuil'] ?? 10) ?> sessions terminées depuis son dernier contrôle. Les sessions annulées ne comptent pas.</p>

    <div class="mt-6"><?php require dirname(__DIR__) . '/partials/message-flash.php'; ?></div>

    <ul class="mt-2 grid md:grid-cols-3 gap-6">
        <?php foreach ($salles as $salle): ?>
            <li class="bg-white border <?= $salle['controle_a_prevoir'] ? 'border-[#C9B3EA]' : 'border-gris-trait' ?> rounded-md p-6 flex flex-col gap-3">
                <h2 class="font-semibold text-lg">
                    <a href="/responsable/salles/<?= (int) $salle['id_salle'] ?>" class="text-encre"><?= Html::e($salle['nom_salle']) ?></a>
                </h2>
                <p class="font-mono text-3xl <?= $salle['controle_a_prevoir'] ? 'text-[#5B2E9A]' : 'text-marine' ?>">
                    <?= (int) $salle['compteur'] ?><span class="text-lg text-texte-doux">/<?= (int) $salle['seuil'] ?></span>
                </p>
                <p class="text-sm text-texte-doux -mt-2">sessions terminées depuis le dernier contrôle</p>
                <?php if ($salle['controle_a_prevoir']): ?>
                    <p><?= Html::badgeControle() ?></p>
                <?php endif; ?>
                <p class="text-sm text-texte-doux">
                    <?php if ($salle['dernier_controle'] !== null): ?>
                        Dernier contrôle : <?= Html::e(FormatFr::jourCourt($salle['dernier_controle']['controle_le'])) ?>
                    <?php else: ?>
                        Jamais contrôlée
                    <?php endif; ?>
                </p>
                <a href="/responsable/salles/<?= (int) $salle['id_salle'] ?>" class="mt-auto text-sm text-marine">Voir la salle →</a>
            </li>
        <?php endforeach; ?>
    </ul>
</main>
<?php require dirname(__DIR__) . '/partials/fin.php'; ?>
