<?php

declare(strict_types=1);

namespace App\Domain;

use DomainException;

/**
 * Action refusée par une règle métier (ex. démarrer une session sans game master,
 * réserver un créneau déjà pris). Le message est montré tel quel à l'utilisateur.
 */
final class RegleMetierException extends DomainException
{
}
