<section class="section">
    <div class="container narrow">
        <article class="result-card">
            <p class="eyebrow">Ваш результат</p>
            <h1><?= e($attempt['nickname']) ?>, у вас результат:</h1>
            <h2><?= e($attempt['result_title'] ?? 'Без результата') ?></h2>
            <?php if (!empty($attempt['result_image'])): ?>
                <div class="result-image">
                    <img src="<?= e($attempt['result_image']) ?>" alt="<?= e($attempt['result_title']) ?>">
                </div>
            <?php endif; ?>
            <div class="result-text">
                <p><?= nl2br(e($attempt['result_description'] ?? 'Описание результата недоступно.')) ?></p>
            </div>
            <div class="actions">
                <a class="btn btn-primary" href="/test/<?= e($attempt['slug']) ?>/start">Пройти заново</a>
                <a class="btn btn-secondary" href="/">На главную</a>
            </div>
        </article>
    </div>
</section>