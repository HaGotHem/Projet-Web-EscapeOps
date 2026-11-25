<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

/**
 * Règle E9 — « Maintenance préventive après dix parties ».
 *
 * Le compteur d'une salle = ses sessions TERMINEE finies après son dernier contrôle.
 * Les sessions annulées (ou pas encore finies) ne comptent pas.
 * À 10, la salle affiche CONTROLE_A_PREVOIR ; enregistrer un contrôle remet à 0.
 */
final class Maintenance
{
    public const SEUIL = 10;
    public const CONTROLE_A_PREVOIR = 'CONTROLE_A_PREVOIR';

    /**
     * @param list<array{etat_session: string, fin_session: string}> $sessions sessions de la salle
     * @param ?DateTimeImmutable $dernierControle null si la salle n'a jamais été contrôlée
     */
    public static function compter(array $sessions, ?DateTimeImmutable $dernierControle): int
    {
        $compteur = 0;
        foreach ($sessions as $session) {
            if ($session['etat_session'] !== 'TERMINEE') {
                continue;
            }
            if ($dernierControle !== null && new DateTimeImmutable($session['fin_session']) <= $dernierControle) {
                continue; // déjà couverte par le contrôle
            }
            $compteur++;
        }

        return $compteur;
    }

    public static function controleAPrevoir(int $compteur): bool
    {
        return $compteur >= self::SEUIL;
    }
}
