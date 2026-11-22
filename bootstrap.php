<?php
declare(strict_types=1);
const BASE_PATH = __DIR__;
require_once BASE_PATH . '/app/Support/helpers.php';
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
$config = require BASE_PATH . '/config/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'Europe/Moscow');
\App\Core\Session::start($config['session'] ?? []);
$db = \App\Core\Database::connect($config['db']);
$siteRepository = new \App\Repositories\SiteRepository($db);
$adminRepository = new \App\Repositories\AdminRepository($db);
$uploader = new \App\Services\FileUploader(BASE_PATH, $config['uploads']);
$sitemapService = new \App\Services\SitemapService(BASE_PATH, $config['app']);
try {
    $sitemapService->sync($siteRepository->getPublishedTestsForSitemap());
} catch (\Throwable $e) {
}
return [
    'config' => $config,
    'db' => $db,
    'siteRepository' => $siteRepository,
    'adminRepository' => $adminRepository,
    'uploader' => $uploader,
    'sitemapService' => $sitemapService,
];