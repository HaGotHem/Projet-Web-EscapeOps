<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class ViewRenderer
{
    /** Données disponibles dans toutes les vues (ex. comptes du menu debug). */
    private array $partage = [];

    public function __construct(private string $basePath)
    {
    }

    public function partager(string $cle, mixed $valeur): void
    {
        $this->partage[$cle] = $valeur;
    }

    public function render(
        ResponseInterface $response,
        string $template,
        array $data = [],
        int $status = 200
    ): ResponseInterface {
        $file = $this->basePath . '/' . $template . '.php';

        if (!is_file($file)) {
            throw new RuntimeException('Vue introuvable : ' . $template);
        }

        extract($data + $this->partage, EXTR_SKIP);

        ob_start();
        require $file;
        $html = (string) ob_get_clean();

        $response->getBody()->write($html);

        return $response
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withStatus($status);
    }
}
