<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;

/**
 * Formulaire refusé : une erreur par champ, à réafficher à côté du champ (HTTP 422).
 */
final class SaisieInvalide extends InvalidArgumentException
{
    /** @param array<string, string> $erreurs champ => message */
    public function __construct(public readonly array $erreurs)
    {
        parent::__construct(implode(' ', $erreurs));
    }
}
