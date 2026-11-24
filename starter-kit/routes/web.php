<?php

declare(strict_types=1);

use App\Controller\VitrineController;
use Slim\App;

return static function (App $app, VitrineController $controller): void {
    $app->get('/', [$controller, 'accueil']);   
};
