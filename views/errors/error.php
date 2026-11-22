<section class="section">
    <div class="container narrow">
        <div class="panel error-panel">
            <span class="error-code"><?= e((string) $code) ?></span>
            <h1><?= e($title) ?></h1>
            <p class="lead"><?= e($description) ?></p>
            <div class="actions">
                <a class="btn btn-primary" href="/">На главную</a>
                <a class="btn btn-secondary" href="javascript:history.back()">Назад</a>
            </div>
        </div>
    </div>
</section>