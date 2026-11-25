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

    /**
     * GET /salles/{id} : fiche publique d'une salle et de tous ses créneaux publiés.
     */
    public function salle(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $salle = $this->service->salle((int) $args['id']);

        if ($salle === null) {
            return $this->renderer->render($response, 'message', [
                'titre' => 'Salle introuvable',
                'texte' => 'Cette salle n\'existe pas ou n\'est plus proposée.',
                'code' => 404,
                'lien' => '/#salles',
                'lienTexte' => 'Voir toutes les salles',
            ], 404);
        }

        return $this->renderer->render($response, 'salle', ['salle' => $salle]);
    }
}
