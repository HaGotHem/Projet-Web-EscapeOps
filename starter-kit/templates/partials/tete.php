<?php
/**
 * Début de toutes les pages : <head>, Tailwind et le thème des maquettes.
 * @var string $titre       titre de l'onglet
 * @var string $classeCorps classes Tailwind du <body>
 */

use App\View\Html;

$classeCorps ??= 'bg-fond text-encre';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::e($titre) ?></title>

    <!-- Polices des maquettes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap">

    <!-- Tailwind v4, version navigateur (version figée) -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.1.11"></script>

    <!-- Couleurs des maquettes, déclarées comme thème Tailwind (bg-xxx, text-xxx, border-xxx) -->
    <style type="text/tailwindcss">
        @theme {
            /* Site public (maquettes Main, FicheSalle, Reservation) */
            --color-fond: #F2F0EB;
            --color-papier: #FFFFFF;
            --color-papier-gris: #E9E6DF;
            --color-encre: #1E1F22;
            --color-texte: #3F4148;
            --color-texte-doux: #5C5F66;
            --color-texte-pale: #66696F;
            --color-trait: #D9D6CF;
            --color-trait-fort: #B9B5AC;
            --color-trait-pris: #BDBAB2;
            --color-accent: #2F4BFF;
            --color-accent-fonce: #1F33C7;

            /* Espace équipe (maquettes Connexion, Cockpit, Session) */
            --color-equipe: #F5F3EF;
            --color-marine: #24324D;
            --color-marine-fonce: #111A2C;
            --color-ok: #1B5E3F;
            --color-ok-fond: #E3F2EA;
            --color-ok-trait: #A9D4BC;
            --color-ko: #8E1F1A;
            --color-ko-vif: #B42318;
            --color-ko-fond: #FBEDEC;
            --color-ko-trait: #E9B4AF;
            --color-info: #2E3B55;
            --color-info-fond: #EEF1F6;
            --color-info-trait: #C3CCDB;
            --color-gris: #4A443C;
            --color-gris-fond: #EFEDE9;
            --color-gris-trait: #D4CEC4;

            --font-serif: 'Cormorant Garamond', Georgia, serif;
            --font-sans: 'IBM Plex Sans', system-ui, sans-serif;
            --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
        }
    </style>
</head>
<body class="<?= Html::e($classeCorps) ?> font-sans text-base leading-relaxed">
