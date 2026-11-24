<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Contrôle du formulaire de réservation d'un groupe (S5).
 * La capacité vient de la salle : capacite_min <= nb_joueurs <= capacite_max.
 */
final class SaisieReservation
{
    /**
     * @return array{donnees: array, erreurs: array<string, string>}
     */
    public static function valider(array $saisie, int $capaciteMin, int $capaciteMax): array
    {
        $erreurs = [];
        $donnees = [];

        $nomGroupe = self::texte($saisie['nom_groupe'] ?? null);
        if ($nomGroupe === null || $nomGroupe === '' || mb_strlen($nomGroupe) > 100) {
            $erreurs['nom_groupe'] = 'Donnez un nom à votre groupe (100 caractères au plus).';
        } else {
            $donnees['nom_groupe'] = $nomGroupe;
        }

        $nb = filter_var($saisie['nb_joueurs'] ?? null, FILTER_VALIDATE_INT);
        if ($nb === false || $nb < $capaciteMin || $nb > $capaciteMax) {
            $erreurs['nb_joueurs'] = "Cette salle accueille de $capaciteMin à $capaciteMax joueurs.";
        } else {
            $donnees['nb_joueurs'] = $nb;
        }

        $contact = self::texte($saisie['contact_nom'] ?? null);
        if ($contact === null || $contact === '' || mb_strlen($contact) > 100) {
            $erreurs['contact_nom'] = 'Indiquez le nom de la personne à contacter.';
        } else {
            $donnees['contact_nom'] = $contact;
        }

        $email = self::texte($saisie['contact_email'] ?? null);
        if ($email === null || mb_strlen($email) > 180 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $erreurs['contact_email'] = 'Indiquez une adresse e-mail valide.';
        } else {
            $donnees['contact_email'] = $email;
        }

        // Téléphone facultatif : chiffres, espaces, points, tirets, + en tête
        $tel = self::texte($saisie['contact_tel'] ?? '');
        if ($tel === null || ($tel !== '' && preg_match('/^\+?[0-9 .\-]{6,20}$/', $tel) !== 1)) {
            $erreurs['contact_tel'] = 'Le téléphone ne doit contenir que des chiffres.';
        } else {
            $donnees['contact_tel'] = $tel === '' ? null : $tel;
        }

        if (($saisie['conditions'] ?? null) !== '1') {
            $erreurs['conditions'] = 'Cochez la case pour confirmer que vous avez lu la règle d\'annulation.';
        }

        return ['donnees' => $donnees, 'erreurs' => $erreurs];
    }

    /** Une chaîne nettoyée, ou null si le champ n'est pas du texte (ex. envoyé en tableau). */
    private static function texte(mixed $valeur): ?string
    {
        return is_string($valeur) ? trim($valeur) : null;
    }
}
