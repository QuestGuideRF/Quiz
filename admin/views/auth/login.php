<?php $title = $isSetup ? 'Первый администратор' : 'Вход в админку'; ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('favicons/favicon-16x16.png') ?>">
    <link rel="icon" href="<?= asset('favicons/favicon.ico') ?>">
    <link rel="manifest" href="<?= asset('favicons/site.webmanifest') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="auth-page">
<section class="auth-card">
    <a class="admin-brand center" href="/">Domnin Quiz</a>
    <h1><?= e($title) ?></h1>
    <p><?= $isSetup ? 'Создайте первого администратора. После этого страница setup станет недоступна.' : 'Введите логин и пароль для входа в панель управления.' ?></p>
    <?php if ($error = \App\Core\Session::getFlash('error')): ?>
        <div class="alert error"><?= e((string) $error) ?></div>
    <?php endif; ?>
    <?php if ($success = \App\Core\Session::getFlash('success')): ?>
        <div class="alert success"><?= e((string) $success) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= $isSetup ? '/admin/setup': '/admin/login' ?>" class="admin-form">
        <?= csrf_field() ?>
        <label class="field">
            <span>Логин</span>
            <input type="text" name="username" required value="<?= e((string) old('username')) ?>">
        </label>
        <label class="field">
            <span>Пароль</span>
            <input type="password" name="password" required>
        </label>
        <?php if ($isSetup): ?>
            <label class="field">
                <span>Повторите пароль</span>
                <input type="password" name="password_confirmation" required>
            </label>
        <?php endif; ?>
        <button class="btn primary full" type="submit"><?= $isSetup ? 'Создать администратора' : 'Войти' ?></button>
    </form>
</section>
</body>
</html>