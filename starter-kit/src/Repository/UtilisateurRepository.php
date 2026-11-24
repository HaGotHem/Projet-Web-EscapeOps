<?php

declare(strict_types=1);

namespace App\Repository;

use Medoo\Medoo;
use PDO;

final class UtilisateurRepository
{
    public function __construct(private Medoo $database)
    {
    }

    public function findByEmail(string $email): ?array
    {
        return $this->database->get(
            'utilisateur',
            [
                'id_utilisateur',
                'nom_utilisateur',
                'prenom_utilisateur',
                'email_utilisateur',
                'mdp_hash',
                'role_utilisateur',
                'actif_utilisateur',
            ],
            ['email_utilisateur' => $email]
        ) ?: null;
    }

    /** Tous les comptes, pour la page de l'administrateur (sans le hash du mot de passe). */
    public function all(): array
    {
        return $this->database->select(
            'utilisateur',
            [
                'id_utilisateur',
                'nom_utilisateur',
                'prenom_utilisateur',
                'email_utilisateur',
                'role_utilisateur',
                'actif_utilisateur',
            ],
            ['ORDER' => ['role_utilisateur' => 'ASC', 'nom_utilisateur' => 'ASC']]
        );
    }
}