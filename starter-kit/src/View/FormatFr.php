<?php

declare(strict_types=1);

namespace App\View;

use DateTimeImmutable;

/**
 * Dates au format français pour les vues (l'extension intl n'est pas installée).
 * Accepte une date SQL (« 2026-11-24 18:00:00 ») ou un DateTimeImmutable.
 */
final class FormatFr
{
    private const JOURS = [1 => 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    private const MOIS = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    private const MOIS_COURTS = [1 => 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

    /** « Mar. 24 nov. » */
    public static function jourCourt(string|DateTimeImmutable $date): string
    {
        $d = self::date($date);

        return ucfirst(mb_substr(self::JOURS[(int) $d->format('N')], 0, 3)) . '. '
            . $d->format('j') . ' ' . self::MOIS_COURTS[(int) $d->format('n')];
    }

    /** « Mardi 24 novembre 2026 » */
    public static function jourLong(string|DateTimeImmutable $date): string
    {
        $d = self::date($date);

        return ucfirst(self::JOURS[(int) $d->format('N')]) . ' ' . $d->format('j') . ' '
            . self::MOIS[(int) $d->format('n')] . ' ' . $d->format('Y');
    }

    /** « 18 h 00 » */
    public static function heure(string|DateTimeImmutable $date): string
    {
        $d = self::date($date);

        return $d->format('G') . ' h ' . $d->format('i');
    }

    /** « 18 h 00 – 19 h 00 » */
    public static function horaire(string|DateTimeImmutable $debut, string|DateTimeImmutable $fin): string
    {
        return self::heure($debut) . ' – ' . self::heure($fin);
    }

    private static function date(string|DateTimeImmutable $date): DateTimeImmutable
    {
        return $date instanceof DateTimeImmutable ? $date : new DateTimeImmutable($date);
    }
}
