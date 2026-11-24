<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UtilisateurRepository;

/**
 * Connexion et déconnexion des membres de l'équipe (session PHP).
 * Suppose que la session est déjà démarrée (voir public/index.php).
 */
final class AuthService
{
    public function __construct(private UtilisateurRepository $repository)
    {
    }

    /**
     * Renvoie l'utilisateur connecté, ou null si email, mot de passe ou compte invalide.
     * Les trois refus sont volontairement identiques : on ne révèle pas quels emails existent.
     */
    public function connecter(string $email, string $motDePasse): ?array
    {
        $u = $this->repository->findByEmail(trim($email));

        // 1. Email inconnu
        if ($u === null) {
            return null;
        }

        // 2. Mauvais mot de passe : on compare au hash bcrypt, jamais au mot de passe en clair
        if (!password_verify($motDePasse, $u['mdp_hash'])) {
            return null;
        }

        // 3. Compte désactivé (ex. Jules Petit dans le seed)
        if ((int) $u['actif_utilisateur'] !== 1) {
            return null;
        }

        // Nouvel identifiant de session à la connexion : protège contre la fixation de session
        session_regenerate_id(true);

        // On ne garde en session que le strict nécessaire, jamais le hash
        $_SESSION['utilisateur'] = [
            'id' => (int) $u['id_utilisateur'],
            'prenom' => (string) $u['prenom_utilisateur'],
            'nom' => (string) $u['nom_utilisateur'],
            'role' => (string) $u['role_utilisateur'],
        ];

        return $_SESSION['utilisateur'];
    }

    public function deconnecter(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * L'utilisateur de la session en cours, ou null si personne n'est connecté.
     */
    public function utilisateurConnecte(): ?array
    {
        return $_SESSION['utilisateur'] ?? null;
    }
}
    