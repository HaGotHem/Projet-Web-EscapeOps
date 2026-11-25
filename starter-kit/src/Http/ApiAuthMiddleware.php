<?php

declare(strict_types=1);

namespace App\Http;

use App\Service\AuthService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Contrôle d'accès des routes d'écriture de l'API JSON.
 *
 * L'API ne lit pas la session PHP : chaque requête s'authentifie avec
 * l'en-tête « Authorization: Basic » (email:mot de passe d'un compte actif).
 * Sans identifiants valides : 401 ; rôle non autorisé : 403. Même règles que le site HTML.
 * Les identifiants circulent en clair dans l'en-tête : en production, HTTPS obligatoire.
 */
final class ApiAuthMiddleware implements MiddlewareInterface
{
    /** @param string[] $roles rôles autorisés */
    public function __construct(
        private AuthService $auth,
        private ResponseFactoryInterface $responses,
        private array $roles
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $utilisateur = $this->identifier($request->getHeaderLine('Authorization'));

        if ($utilisateur === null) {
            return JsonResponder::respond($this->responses->createResponse(), [
                'erreur' => 'Authentification requise : en-tête Authorization Basic avec l\'email et le mot de passe d\'un compte actif.',
            ], 401)->withHeader('WWW-Authenticate', 'Basic realm="EscapeOps API", charset="UTF-8"');
        }

        if (!in_array($utilisateur['role'], $this->roles, true)) {
            return JsonResponder::respond($this->responses->createResponse(), [
                'erreur' => 'Votre rôle ne permet pas cette action.',
            ], 403);
        }

        return $handler->handle($request->withAttribute('utilisateur', $utilisateur));
    }

    private function identifier(string $entete): ?array
    {
        if (!preg_match('/^Basic\s+([A-Za-z0-9+\/=]+)$/', trim($entete), $m)) {
            return null;
        }

        $decode = base64_decode($m[1], true);
        if ($decode === false || !str_contains($decode, ':')) {
            return null;
        }

        [$email, $motDePasse] = explode(':', $decode, 2);

        return $this->auth->authentifierRequete($email, $motDePasse);
    }
}
