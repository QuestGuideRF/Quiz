<?php
declare(strict_types=1);
$app = require __DIR__ . '/bootstrap.php';
use App\Controllers\SiteController;
$controller = new SiteController($app['siteRepository'], $app['config']);
$route = trim((string) ($_GET['route'] ?? ''), '/');
try {
    if ($route === '') {
        $controller->home();
        return;
    }
    if (preg_match('#^test/([a-z0-9-]+)$#', $route, $matches) === 1) {
        $controller->showTest($matches[1]);
        return;
    }
    if (preg_match('#^test/([a-z0-9-]+)/start$#', $route, $matches) === 1) {
        method_is('POST') ? $controller->startAttempt($matches[1]) : $controller->showStart($matches[1]);
        return;
    }
    if (preg_match('#^quiz/([a-z0-9-]+)/(?:question|вопрос)/(\d+)$#', $route, $matches) === 1) {
        $step = (int) $matches[2];
        method_is('POST') ? $controller->submitQuestion($matches[1], $step) : $controller->showQuestion($matches[1], $step);
        return;
    }
    if (preg_match('#^quiz/([a-z0-9-]+)/result$#', $route, $matches) === 1) {
        $controller->showResult($matches[1]);
        return;
    }
    if (in_array($route, ['403', '404', '500'], true)) {
        $controller->error((int) $route);
        return;
    }
    $controller->error(404);
} catch (Throwable $e) {
    if (config($app['config'], 'app.debug', false)) {
        throw $e;
    }
    $controller->error(500);
}