<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\ViewRenderer;
use App\Service\VitrineService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Vitrine publique : lecture seule, aucune connexion requise.
 */
final class VitrineController
{
    public function __construct(
        private VitrineService $service,
        private ViewRenderer $renderer
    ) {
    }

    /**
     * GET / : les salles et leurs prochains créneaux publiés.
     */
    public function accueil(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $this->renderer->render($response, 'accueil', [
            'salles' => $this->service->salles(),
        ]);
    }
}
