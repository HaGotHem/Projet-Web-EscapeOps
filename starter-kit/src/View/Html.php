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

    /** Badge du classement d'une annulation (E7), couleurs des maquettes AnnulationSansFrais / Tardive. */
    public static function badgeAnnulation(string $statut): string
    {
        [$classes, $texte] = [
            'SANS_FRAIS' => ['bg-[#E6F3F4] text-[#135E66] border-[#9FCFD4]', 'Sans frais'],
            'TARDIVE' => ['bg-[#FDF0E0] text-[#8A4500] border-[#F0C38E]', 'Tardive'],
        ][$statut] ?? ['bg-gris-fond text-gris border-gris-trait', $statut];

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-semibold font-mono uppercase tracking-wide ' . $classes . '">'
            . self::e($texte) . '</span>';
    }

    /** Badge CONTROLE_A_PREVOIR (E9), violet de la maquette SalleResponsable. */
    public static function badgeControle(): string
    {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-xs font-semibold font-mono uppercase tracking-wide bg-[#F1EBFA] text-[#5B2E9A] border-[#C9B3EA]">Contrôle à prévoir</span>';
    }
}
