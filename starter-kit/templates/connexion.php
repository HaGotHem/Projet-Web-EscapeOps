<?php
/**
 * Page de connexion de l'équipe (maquette Connexion).
 * @var ?string $erreur
 * @var string  $email  dernier email saisi (jamais le mot de passe)
 * @var string  $csrf
 */

use App\View\Html;

$titre = 'Connexion — Les Archives Obscures';
$classeCorps = 'bg-equipe text-encre';
require __DIR__ . '/partials/tete.php';
?>
<div class="min-h-screen grid md:grid-cols-2">
    <!-- Panneau d'ambiance -->
    <section aria-hidden="true" class="hidden md:flex flex-col justify-between bg-marine text-white p-12">
        <span class="font-serif text-2xl font-semibold">Les Archives Obscures</span>
        <div>
            <p class="font-mono text-xs tracking-[0.14em] text-white/70 mb-4">ESCAPEOPS · ESPACE ÉQUIPE</p>
            <p class="font-serif text-5xl leading-tight font-semibold max-w-[16ch]">Personne ne réserve une salle indisponible.</p>
            <p class="mt-6 text-white/80 max-w-[40ch]">Et toute l'équipe sait pourquoi une session peut — ou ne peut pas — démarrer.</p>
        </div>
        <div class="flex gap-3 font-mono text-xs tracking-[0.12em] text-white/60">
            <span>3 SALLES</span><span>·</span><span>SESSIONS</span><span>·</span><span>MAINTENANCE</span>
        </div>
    </section>

    <!-- Formulaire -->
    <main class="flex items-center justify-center p-6 md:p-12">
        <div class="w-full max-w-md">
            <h1 class="font-serif text-5xl font-semibold mb-2">Connexion</h1>
            <p class="text-texte-doux mb-8">Accès réservé au personnel des Archives Obscures.</p>

            <?php if ($erreur !== null): ?>
                <div role="alert" class="mb-6 px-4 py-3 rounded-md border border-ko-trait bg-ko-fond text-ko">
                    <strong><?= Html::e($erreur) ?></strong>
                    Vérifiez votre saisie. Si vous avez oublié votre mot de passe, l'administrateur peut le réinitialiser.
                </div>
            <?php endif; ?>

            <form method="post" action="/connexion" class="flex flex-col gap-5">
                <input type="hidden" name="csrf" value="<?= Html::e($csrf) ?>">

                <div class="flex flex-col gap-1.5">
                    <label for="email" class="font-medium">E-mail professionnel</label>
                    <input id="email" name="email" type="email" required autocomplete="username"
                           value="<?= Html::e($email) ?>"
                           class="min-h-12 px-3 rounded-md border border-gris-trait bg-white focus:outline-2 focus:outline-marine">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="mot_de_passe" class="font-medium">Mot de passe</label>
                    <input id="mot_de_passe" name="mot_de_passe" type="password" required autocomplete="current-password"
                           class="min-h-12 px-3 rounded-md border border-gris-trait bg-white focus:outline-2 focus:outline-marine">
                </div>

                <button type="submit" class="min-h-12 rounded-md bg-marine hover:bg-marine-fonce text-white font-semibold cursor-pointer">Se connecter</button>
            </form>

            <p class="mt-6 px-4 py-3 rounded-md bg-info-fond text-info text-sm">
                Pas besoin de choisir un rôle : il est lié à votre compte. Après connexion, vous arrivez sur votre espace —
                <strong>planning</strong> (responsable), <strong>mes sessions</strong> (game master) ou <strong>comptes</strong> (administrateur).
            </p>
            <p class="mt-6 text-sm"><a href="/" class="text-marine">← Retour au site public</a></p>
        </div>
    </main>
</div>
<?php require __DIR__ . '/partials/fin.php'; ?>
