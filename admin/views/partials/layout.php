<?php
$flashSuccess = \App\Core\Session::getFlash('success');
$flashError = \App\Core\Session::getFlash('error');
?>
<!doctype html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Админ-панель') ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('favicons/favicon-16x16.png') ?>">
    <link rel="icon" href="<?= asset('favicons/favicon.ico') ?>">
    <link rel="manifest" href="<?= asset('favicons/site.webmanifest') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="/admin">Domnin Quiz</a>
        <nav class="admin-nav">
            <a href="/admin">Дашборд</a>
            <a href="/admin/tests">Тесты</a>
            <a href="/">Открыть сайт</a>
            <a href="/admin/logout">Выйти</a>
        </nav>
    </aside>
    <main class="admin-content">
        <?php if ($flashSuccess): ?>
            <div class="alert success"><?= e((string) $flashSuccess) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert error"><?= e((string) $flashError) ?></div>
        <?php endif; ?>
        <?php require $viewPath; ?>
    </main>
</div>
<script src="<?= asset('assets/js/admin.js') ?>" defer></script>
</body>
</html>