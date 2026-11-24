<?php
/** @var ?array $message ['texte' => string, 'erreur' => bool] */

use App\View\Html;

if (!empty($message)): ?>
    <div role="<?= $message['erreur'] ? 'alert' : 'status' ?>"
         class="mb-6 px-4 py-3 rounded-md border <?= $message['erreur'] ? 'bg-ko-fond border-ko-trait text-ko' : 'bg-ok-fond border-ok-trait text-ok' ?>">
        <?= Html::e($message['texte']) ?>
    </div>
<?php endif; ?>
