<?php
$flashSuccess = \App\Core\Session::getFlash('success');
$flashError = \App\Core\Session::getFlash('error');
?>
<!doctype html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? config($config, 'app.name')) ?></title>
    <meta name="description" content="<?= e($metaDescription ?? config($config, 'app.default_meta_description')) ?>">
    <meta name="theme-color" content="#f6f2ea">
    <link rel="canonical" href="<?= e($canonical ?? current_full_url()) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('favicons/favicon-16x16.png') ?>">
    <link rel="icon" href="<?= asset('favicons/favicon.ico') ?>">
    <link rel="manifest" href="<?= asset('favicons/site.webmanifest') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/site.css') ?>">
</head>
<body>
<div class="page-shell">
    <header class="site-header">
        <div class="container header-inner header-inner-compact">
            <button class="theme-toggle theme-toggle-icon" type="button" data-theme-toggle aria-label="Переключить тему" title="Переключить тему">
                <span data-theme-label aria-hidden="true">&
            </button>
        </div>
    </header>
    <main class="site-main">
        <div class="container">
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="alert alert-error"><?= e((string) $flashError) ?></div>
            <?php endif; ?>
        </div>
        <?php require $viewPath; ?>
    </main>
    <footer class="site-footer">
        <div class="container footer-inner footer-inner-compact"></div>
    </footer>
</div>
<script src="<?= asset('assets/js/site.js') ?>" defer></script>
</body>
</html>