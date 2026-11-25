<?php

declare(strict_types=1);

use App\Controller\ApiController;
use App\Controller\AuthController;
use App\Controller\DebugController;
use App\Controller\EquipeController;
use App\Controller\ReservationController;
use App\Controller\VitrineController;
use App\Database\DatabaseFactory;
use App\Http\ApiAuthMiddleware;
use App\Http\RoleMiddleware;
use App\Http\ViewRenderer;
use App\Repository\IncidentRepository;
use App\Repository\ReservationRepository;
use App\Repository\SalleRepository;
use App\Repository\SessionRepository;
use App\Repository\UtilisateurRepository;
use App\Service\AuthService;
use App\Service\IncidentService;
use App\Service\MaintenanceService;
use App\Service\ReservationService;
use App\Service\SessionService;
use App\Service\VitrineService;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);

// Heure de Paris, comme la base : règle E7 (24 h) et dates ISO de l'API
date_default_timezone_set('Europe/Paris');

Dotenv::createImmutable($root)->safeLoad();

$settings = require $root . '/config/settings.php';

// Session PHP pour les pages HTML (connexion, rôles, jeton CSRF).
// L'API JSON n'en a pas besoin : elle ne lit pas la session.
$chemin = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!str_starts_with($chemin, '/api/')) {
    session_set_cookie_params([
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // cookie envoyé seulement en HTTPS quand le site est en HTTPS
        'httponly' => true,  // JavaScript ne peut pas lire le cookie (limite le vol de session par XSS)
        'samesite' => 'Lax', // le cookie n'accompagne pas les POST venant d'un autre site (limite le CSRF)
    ]);
    session_start();
}

$app = AppFactory::create();

$database = DatabaseFactory::create($settings['database']);
$renderer = new ViewRenderer($root . '/templates');

// Repositories (accès aux données)
$salles = new SalleRepository($database);
$sessionsRepo = new SessionRepository($database);
$reservationsRepo = new ReservationRepository($database);
$incidentsRepo = new IncidentRepository($database);
$utilisateurs = new UtilisateurRepository($database);

// Services (règles métier)
$auth = new AuthService($utilisateurs);
$sessionService = new SessionService($sessionsRepo, $salles, $reservationsRepo);
$maintenanceService = new MaintenanceService($salles);
$incidentService = new IncidentService($incidentsRepo, $salles);

// Contrôleurs
$controleurs = [
    'vitrine' => new VitrineController(new VitrineService($salles), $renderer),
    'auth' => new AuthController($auth, $renderer),
    'equipe' => new EquipeController($sessionService, $maintenanceService, $utilisateurs, $renderer),
    'reservation' => new ReservationController(new ReservationService($reservationsRepo), $renderer),
];
$api = new ApiController($salles, $sessionService, $incidentService);

// Mode debug (développement seulement) : connexion rapide à un compte de démo.
// En production (APP_ENV=prod ou APP_DEBUG=false), ni la route ni le menu n'existent.
if ($settings['app']['env'] === 'dev' && $settings['app']['debug']) {
    $controleurs['debug'] = new DebugController($auth);
    // Closure : la requête SQL n'est faite que si une page affiche le menu
    $renderer->partager('debugComptes', static fn (): array => array_filter(
        $utilisateurs->all(),
        static fn (array $u): bool => (int) $u['actif_utilisateur'] === 1
    ));
}

// Fabrique de middlewares de contrôle d'accès : $role(['RESPONSABLE']) protège un groupe de routes
$role = static fn (array $roles): RoleMiddleware => new RoleMiddleware(
    $auth,
    $renderer,
    $app->getResponseFactory(),
    $roles
);

// Même chose pour l'API : authentification Basic à chaque requête, réponses 401 / 403 en JSON
$apiAuth = static fn (array $roles): ApiAuthMiddleware => new ApiAuthMiddleware(
    $auth,
    $app->getResponseFactory(),
    $roles
);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(
    $settings['app']['debug'],
    true,
    true
);

(require $root . '/routes/web.php')($app, $controleurs, $role);
(require $root . '/routes/api.php')($app, $api, $apiAuth);

$app->run();
