<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Référence donnée au groupe après sa réservation : AO-XXXXXXXX.
 * Tirée au hasard (random_int, cryptographiquement sûr) pour qu'on ne puisse
 * pas deviner celle d'un autre groupe. Sans I, O, 0, 1 : on ne les confond pas au téléphone.
 */
final class CodeReservation
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function generer(): string
    {
        $code = 'AO-';
        for ($i = 0; $i < 8; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $code;
    }
}
