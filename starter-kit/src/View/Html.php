<?php

declare(strict_types=1);

namespace App\View;

use App\Domain\EtatSession;

/**
 * Petits outils partagés par les vues.
 */
final class Html
{
    /** Échappement HTML : tout texte venant de la base ou de l'utilisateur passe par ici (XSS). */
    public static function e(mixed $valeur): string
    {
        return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Badge d'état d'une session, couleurs de la maquette Cockpit / Etats. */
    public static function badgeEtat(string $etat): string
    {
        $classes = [
            'PREVUE' => 'bg-info-fond text-info border-info-trait',
            'PRETE' => 'bg-ok-fond text-ok border-ok-trait',
            'EN_COURS' => 'bg-marine text-white border-marine',
            'TERMINEE' => 'bg-gris-fond text-gris border-gris-trait',
            'ANNULEE' => 'bg-ko-fond text-ko border-ko-trait',
        ][$etat] ?? 'bg-gris-fond text-gris border-gris-trait';

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-semibold font-mono uppercase tracking-wide ' . $classes . '">'
            . self::e(EtatSession::libelle($etat)) . '</span>';
    }
}
