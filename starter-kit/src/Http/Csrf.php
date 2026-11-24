<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Jeton anti-CSRF des formulaires HTML.
 * Un jeton aléatoire est rangé en session ; chaque formulaire le renvoie dans
 * un champ caché « csrf ». Un site tiers ne connaît pas ce jeton : il ne peut
 * donc pas faire soumettre un formulaire à la place de l'utilisateur.
 * La session doit être démarrée (public/index.php).
 */
final class Csrf
{
    public static function jeton(): string
    {
        if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    public static function estValide(mixed $recu): bool
    {
        return is_string($recu)
            && isset($_SESSION['csrf'])
            && is_string($_SESSION['csrf'])
            // hash_equals compare en temps constant : pas d'indice sur les caractères justes
            && hash_equals($_SESSION['csrf'], $recu);
    }
}
