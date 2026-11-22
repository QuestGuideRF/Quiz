<?php
declare(strict_types=1);
$app = require dirname(__DIR__) . '/bootstrap.php';
use App\Controllers\AdminController;
$controller = new AdminController(
    $app['adminRepository'],
    $app['siteRepository'],
    $app['uploader'],
    $app['sitemapService'],
    $app['config']
);
$route = trim((string) ($_GET['route'] ?? ''), '/');
try {
    $controller->dispatch($route);
} catch (Throwable $e) {
    if (config($app['config'], 'app.debug', false)) {
        throw $e;
    }
    http_response_code(500);
    echo 'Внутренняя ошибка админ-панели.';
}